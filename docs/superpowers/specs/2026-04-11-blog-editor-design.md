# Rich Blog Editor — Design Spec

**Project:** Bassila Émergence — Admin autonomy roadmap, sub-project ③
**Date:** 2026-04-11
**Status:** Draft — awaiting user review

---

## 1. Context

Bassila Émergence currently exposes a blog with a `<textarea font-mono>` as
its only editing interface. Content is stored as plain text and rendered via
`{!! nl2br(e($post->content)) !!}` — a choice that rules out any formatting,
images inline, code blocks, or embeds. There is no autosave, no preview, no
drafts dashboard, and no SEO controls.

This sub-project replaces the textarea with **TipTap 3** (a ProseMirror-based
headless editor), stores content as **HTML sanitized through HTMLPurifier**,
adds a `/mes-articles` dashboard for authors to manage their own content,
introduces autosave, preview in a new tab, SEO meta fields, and migrates the
existing 25 plain-text articles to paragraph-wrapped HTML.

It is the **third** sub-project of the admin autonomy roadmap. It depends on
② RBAC for the permissions it consumes (`posts.create`, `posts.publish.own`,
`posts.edit.own`, `posts.edit.any`) and on ① Settings for the two toggles that
gate member publishing (`blog.public_creation`, `blog.require_moderation`).

## 2. Goals

- Replace the plain-text editor with a rich WYSIWYG editor supporting:
  headings (H2-H4), bold/italic/underline/strikethrough, links, bullet and
  numbered lists, blockquotes, code blocks with syntax highlighting, tables,
  horizontal rules, inline images, and YouTube/Vimeo embeds.
- Store content as sanitized HTML in the existing `blog_posts.content` column
  (no schema migration on that column).
- Add two SEO meta fields (`meta_title`, `meta_description`) with sensible
  fallbacks to the visible title and auto-extracted excerpt.
- Upload images locally via the `public` disk with automatic compression and
  dimension caps (`intervention/image`).
- Force alt text on every inserted image (accessibility).
- Autosave drafts every 3 seconds of inactivity, starting only after the
  title is non-empty.
- Preview draft content in a new tab via a dedicated route, accessible only
  to the author or admins with `posts.edit.any`.
- Provide a `/mes-articles` page for any logged-in user to list, filter, and
  manage their own drafts/published/archived posts.
- Migrate the 25 existing plain-text posts to HTML without data loss, with a
  JSON backup in `storage/app/backups/` for rollback.

## 3. Non-Goals

Explicitly out of scope:

- **Versioning / revision history** — each save overwrites the previous.
- **Per-paragraph comments** (Medium-style margin annotations).
- **Real-time collaboration** (multiple concurrent authors on one post).
- **Advanced tables** (cell merging, formulas, column resize beyond TipTap's
  default).
- **Twitter, Instagram, Spotify, or TikTok embeds** — only YouTube and Vimeo
  at MVP.
- **Scheduled publishing** — no `scheduled` status, no `scheduled_for` column.
  Deferred.
- **Markdown import/export**.
- **Slash-menu `/` block picker** (Notion-like).
- **Drag-and-drop block reorder**.
- **Per-user storage quota** — trusts all authors.
- **Autosave with diffing / conflict resolution** — last write wins.

## 4. Dependency requirements

### NPM (new)

```json
{
  "@tiptap/core": "^3.0",
  "@tiptap/starter-kit": "^3.0",
  "@tiptap/extension-link": "^3.0",
  "@tiptap/extension-image": "^3.0",
  "@tiptap/extension-table": "^3.0",
  "@tiptap/extension-table-row": "^3.0",
  "@tiptap/extension-table-cell": "^3.0",
  "@tiptap/extension-table-header": "^3.0",
  "@tiptap/extension-code-block-lowlight": "^3.0",
  "@tiptap/extension-youtube": "^3.0",
  "lowlight": "^3.0",
  "highlight.js": "^11.0"
}
```

Bundle size with tree-shaking: ~120 KB gzipped. Loaded **only** on the edit
pages via a dedicated Vite entry.

### Composer (new)

```json
{
  "mews/purifier": "^3.4",
  "intervention/image": "^3.6"
}
```

- **`mews/purifier`** wraps `ezyang/htmlpurifier` with Laravel facade and config
  file support.
- **`intervention/image v3`** uses the GD driver by default (already present in
  PHP), no need for Imagick.

## 5. Architecture overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                    Browser (TipTap 3 + Alpine)                     │
│                                                                     │
│   <x-tiptap-editor wire:model.debounce.3000ms="content" />          │
│                                                                     │
│   • TipTap instance (StarterKit + 8 extensions)                    │
│   • Toolbar above the editor (bold/italic/link/heading/...)        │
│   • Image upload handler → fetch('/blog/upload-image')             │
│   • Alt-text modal forced before image insertion                   │
│   • Syncs editor.getHTML() to the Livewire `content` property      │
└──────────────────┬──────────────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    Livewire components (server)                    │
│                                                                     │
│   App\Livewire\Blog\CreatePost      — create-or-update draft        │
│   App\Livewire\Blog\EditPost        — load existing + same UX       │
│   App\Livewire\Blog\MyPosts         — /mes-articles list            │
│   App\Livewire\Blog\PreviewPost     — render draft like public      │
│                                                                     │
│   • autoSave() triggered by updatedTitle / updatedContent hooks     │
│   • Before persist: pipe $content through BlogContentSanitizer     │
└──────────────────┬──────────────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    Services                                        │
│                                                                     │
│   App\Services\BlogContentSanitizer                                 │
│     • HTMLPurifier config "blog" with curated whitelist             │
│     • Logs warning if > 30% content was stripped                    │
│                                                                     │
│   App\Services\BlogImageUploader                                    │
│     • accepts jpg/png/webp/gif ≤ 10 MB                              │
│     • resizes + compresses via intervention/image                   │
│     • writes to Storage::disk('public')                             │
└─────────────────────────────────────────────────────────────────────┘
```

Single data flow: browser TipTap → Livewire debounced update →
`BlogContentSanitizer` → DB. Zero client-side trust: every save
re-sanitizes on the server, even for autosave.

## 6. Data model changes

### 6.1 Migration `add_rich_editor_fields_to_blog_posts_table`

```php
Schema::table('blog_posts', function (Blueprint $table) {
    $table->string('meta_title', 70)->nullable()->after('excerpt');
    $table->string('meta_description', 160)->nullable()->after('meta_title');
});
```

Lengths chosen to match Google SERP display limits (~70 chars for title,
~160 chars for description — anything longer is truncated).

### 6.2 No schema change on `content`

The existing `longText` column holds the HTML. Only its contents change from
plain text to HTML — handled by the one-shot migration script (§12).

### 6.3 `BlogPost` model additions

```php
protected $fillable = [
    'user_id', 'title', 'slug', 'content', 'excerpt',
    'meta_title', 'meta_description',
    'featured_image_url', 'status', 'category_id', 'published_at',
];

// Existing accessor already handles HTML through strip_tags
public function getReadingTimeAttribute(): int
{
    $wordCount = str_word_count(strip_tags((string) $this->content));
    return max(1, (int) ceil($wordCount / 200));
}

// New: resolved meta title/description with fallbacks
public function getResolvedMetaTitleAttribute(): string
{
    return $this->meta_title ?: $this->title;
}

public function getResolvedMetaDescriptionAttribute(): string
{
    return $this->meta_description
        ?: Str::limit(strip_tags($this->content), 155);
}
```

## 7. HTMLPurifier configuration

Publish the Purifier config:
```bash
php artisan vendor:publish --tag=purifier.config
```

Then customize `config/purifier.php`:

```php
return [
    'encoding'          => 'UTF-8',
    'finalize'          => true,
    'ignoreNonStrings'  => false,
    'cachePath'         => storage_path('app/purifier'),
    'cacheFileMode'     => 0755,
    'settings' => [
        'default' => [
            'HTML.Doctype'                  => 'HTML 4.01 Transitional',
            'HTML.Allowed'                  => 'p,br,hr,h2,h3,h4,strong,em,s,code,ul,ol,li,blockquote,pre,a[href|rel|target],img[src|alt|title|width|height]',
            'AutoFormat.RemoveEmpty'        => true,
            'URI.AllowedSchemes'            => ['http' => true, 'https' => true, 'mailto' => true],
        ],
        'blog' => [
            'HTML.Doctype'                  => 'HTML 4.01 Transitional',
            'HTML.Allowed'                  => 'p,br,hr,h2,h3,h4,strong,em,s,u,code,mark,ul,ol,li,blockquote,pre,a[href|rel|target],img[src|alt|title|width|height],figure,figcaption,table,thead,tbody,tr,th[colspan|rowspan],td[colspan|rowspan]',
            'HTML.SafeIframe'               => true,
            'URI.SafeIframeRegexp'          => '%^(https://www\.youtube\.com/embed/|https://player\.vimeo\.com/video/)%',
            'AutoFormat.AutoParagraph'      => false,
            'AutoFormat.RemoveEmpty'        => false,
            'Attr.AllowedRel'               => ['nofollow', 'noopener', 'noreferrer'],
            'URI.AllowedSchemes'            => ['http' => true, 'https' => true, 'mailto' => true],
            'CSS.AllowedProperties'         => [],           // no inline styles
            'Core.EscapeNonASCIICharacters' => false,        // keep French accents
        ],
    ],
];
```

Key protections:
- `URI.SafeIframeRegexp` restricts iframes to YouTube and Vimeo only — any
  other `<iframe>` is dropped.
- `CSS.AllowedProperties = []` disallows all inline CSS, preventing
  `style="background:url(javascript:…)"` exploits.
- `Attr.AllowedRel` forces external links to use `rel="noopener noreferrer"`.
- `URI.AllowedSchemes` rejects `javascript:`, `data:`, `file:`, `vbscript:`.

`HTML.Allowed` explicitly lists every authorized tag and its authorized
attributes. Anything not on this list is silently stripped.

## 8. `BlogContentSanitizer` service

```php
namespace App\Services;

use Illuminate\Support\Facades\Log;
use Mews\Purifier\Facades\Purifier;

class BlogContentSanitizer
{
    /** Drops > 30% of bytes triggers a warning log. */
    private const SIGNIFICANT_STRIP_RATIO = 0.7;

    public function clean(string $rawHtml): string
    {
        $before = strlen($rawHtml);
        $clean  = Purifier::clean($rawHtml, 'blog');
        $after  = strlen($clean);

        if ($before > 0 && $after < $before * self::SIGNIFICANT_STRIP_RATIO) {
            Log::warning('BlogContentSanitizer stripped significant content', [
                'before_bytes' => $before,
                'after_bytes'  => $after,
                'ratio'        => $after / $before,
            ]);
        }

        return $clean;
    }
}
```

The significant-strip warning helps diagnose reports like "I typed a lot and
most of it disappeared after save" — checking the log immediately shows
which purifier rule stripped the content.

## 9. `BlogImageUploader` service

```php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class BlogImageUploader
{
    private const MAX_COVER_WIDTH  = 1600;
    private const MAX_COVER_HEIGHT = 900;
    private const MAX_INLINE_WIDTH = 1400;
    private const JPEG_QUALITY     = 85;

    public function uploadCover(UploadedFile $file, string $slug): string
    {
        $manager = ImageManager::gd();
        $image   = $manager->read($file->getRealPath());

        // Crop-to-fit 16:9, resizing as needed
        $image->cover(self::MAX_COVER_WIDTH, self::MAX_COVER_HEIGHT);

        $filename = sprintf(
            'blog/covers/%s/%s-%s.jpg',
            now()->format('Y/m'),
            Str::slug($slug),
            Str::random(8),
        );

        Storage::disk('public')->put($filename, $image->toJpeg(self::JPEG_QUALITY)->toString());

        return Storage::disk('public')->url($filename);
    }

    public function uploadInline(UploadedFile $file): string
    {
        $manager = ImageManager::gd();
        $image   = $manager->read($file->getRealPath());

        if ($image->width() > self::MAX_INLINE_WIDTH) {
            $image->scale(width: self::MAX_INLINE_WIDTH);
        }

        $ext     = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $binary  = match ($ext) {
            'png'  => $image->toPng()->toString(),
            'webp' => $image->toWebp(self::JPEG_QUALITY)->toString(),
            'gif'  => $image->toGif()->toString(),
            default => $image->toJpeg(self::JPEG_QUALITY)->toString(),
        };

        $safeExt = in_array($ext, ['png', 'webp', 'gif'], true) ? $ext : 'jpg';
        $filename = sprintf(
            'blog/inline/%s/%s.%s',
            now()->format('Y/m'),
            Str::random(40),
            $safeExt,
        );

        Storage::disk('public')->put($filename, $binary);

        return Storage::disk('public')->url($filename);
    }
}
```

## 10. Upload endpoint for TipTap inline images

New controller:

```php
// app/Http/Controllers/BlogImageUploadController.php

class BlogImageUploadController extends Controller
{
    public function __construct(private BlogImageUploader $uploader) {}

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif'],
        ]);

        $url = $this->uploader->uploadInline($request->file('image'));

        return response()->json(['url' => $url]);
    }
}
```

Route (auth-gated on `posts.create`):

```php
Route::post('/blog/upload-image', [BlogImageUploadController::class, 'upload'])
    ->middleware(['auth', 'can:create,App\Models\BlogPost'])
    ->name('blog.upload-image');
```

The TipTap JS calls this endpoint via `fetch()` with the CSRF token:

```javascript
const response = await fetch('/blog/upload-image', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
    },
    body: formData,
});
const { url } = await response.json();
editor.chain().focus().setImage({ src: url, alt: altText }).run();
```

`altText` comes from a modal that opens **before** the upload fires, forcing
the author to provide it. An empty alt is accepted (decorative image) but the
modal prompt explicitly says "Laissez vide uniquement si l'image est purement
décorative".

## 11. Autosave flow

Debounced via Livewire `wire:model.live.debounce.3000ms`. Title and content
each trigger `autoSave()` through the `updatedTitle` / `updatedContent` hooks:

```php
class CreatePost extends Component
{
    public ?BlogPost $post = null;
    public string $title = '';
    public string $slug = '';
    public string $content = '';
    public string $excerpt = '';
    public ?string $metaTitle = null;
    public ?string $metaDescription = null;
    public ?int $categoryId = null;
    public string $status = 'draft';
    public $featuredImage = null;
    public ?string $autoSavedAt = null;

    public function updatedTitle(): void { $this->autoSave(); }
    public function updatedContent(): void { $this->autoSave(); }

    public function autoSave(): void
    {
        if (trim($this->title) === '') {
            return; // don't create an empty row
        }

        $this->authorize('create', BlogPost::class);

        $cleanContent = app(BlogContentSanitizer::class)->clean($this->content);

        if ($this->post === null) {
            $this->post = BlogPost::create([
                'user_id' => Auth::id(),
                'title'   => $this->title,
                'slug'    => $this->slug ?: $this->generateUniqueSlug(Str::slug($this->title)),
                'content' => $cleanContent,
                'status'  => 'draft',
            ]);
        } else {
            $this->post->update([
                'title'   => $this->title,
                'content' => $cleanContent,
            ]);
        }

        $this->autoSavedAt = now()->toIso8601String();
    }

    private function generateUniqueSlug(string $base): string
    {
        $slug = $base;
        $n = 1;
        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$n);
        }
        return $slug;
    }

    public function save(): void
    {
        // The explicit Save button. Same path as autoSave but also persists
        // featuredImage, excerpt, metaTitle, metaDescription, categoryId, status.
        // Redirects to /mes-articles after success.
    }
}
```

The user-facing indicator (top of the form):

```blade
@if ($autoSavedAt)
    <span class="text-xs text-gray-400"
          x-data
          x-text="'Enregistré il y a ' + humanDiff('{{ $autoSavedAt }}')">
        Enregistré à l'instant
    </span>
@endif
```

A tiny Alpine helper `humanDiff()` computes the relative duration on the
client side so the text stays fresh without full page refresh.

## 12. Migration script — plain text → HTML

Implemented as a regular migration file that runs automatically with
`php artisan migrate`, but only after the `add_rich_editor_fields` migration.
The file timestamp guarantees the ordering.

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Backup existing rows as JSON (rollback safety)
        $backup = DB::table('blog_posts')->get()->toArray();
        $backupPath = 'backups/blog_posts_' . now()->format('Y-m-d_His') . '.json';
        Storage::disk('local')->put(
            $backupPath,
            json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        );

        // 2. Convert each post
        DB::table('blog_posts')->orderBy('id')->each(function ($post) {
            $plain = (string) ($post->content ?? '');
            if ($plain === '') {
                return;
            }

            $paragraphs = preg_split('/\n\s*\n/', trim($plain)) ?: [];
            $html = collect($paragraphs)
                ->filter(fn ($p) => trim($p) !== '')
                ->map(fn ($p) => '<p>' . nl2br(e(trim($p)), false) . '</p>')
                ->implode("\n");

            $clean = Purifier::clean($html, 'blog');

            DB::table('blog_posts')
                ->where('id', $post->id)
                ->update(['content' => $clean]);
        });
    }

    public function down(): void
    {
        $files = Storage::disk('local')->files('backups');
        $latest = collect($files)
            ->filter(fn ($f) => str_starts_with(basename($f), 'blog_posts_'))
            ->sortDesc()
            ->first();

        if (! $latest) {
            throw new \RuntimeException('No blog_posts backup available for rollback.');
        }

        $data = json_decode(Storage::disk('local')->get($latest), true);

        foreach ($data as $row) {
            DB::table('blog_posts')
                ->where('id', $row['id'])
                ->update(['content' => $row['content']]);
        }
    }
};
```

The backup file stays in `storage/app/backups/` indefinitely. A follow-up
cleanup (`php artisan blog:cleanup-migration-backups --keep=3`) can be added
later if the backup folder grows too large; out of scope for this spec.

## 13. New routes

In `routes/web.php`, inside the existing `auth + verified` group:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/blog/rediger',                CreatePost::class)
        ->middleware('can:create,App\Models\BlogPost')
        ->name('blog.create');

    Route::get('/blog/{slug}/modifier',        EditPost::class)
        ->name('blog.edit');

    Route::get('/mes-articles',                MyPosts::class)
        ->name('blog.mine');

    Route::get('/blog/preview/{post}',         PreviewPost::class)
        ->name('blog.preview');

    Route::post('/blog/upload-image',
        [BlogImageUploadController::class, 'upload']
    )->middleware('can:create,App\Models\BlogPost')
     ->name('blog.upload-image');
});
```

`{post}` uses model binding on the primary key (not slug) to simplify access
to drafts that don't yet have a unique slug finalized.

## 14. Page `/mes-articles` — `App\Livewire\Blog\MyPosts`

Permission: just `auth`. Scoping to own posts is done in the query.

### Layout

```
Mes articles
Gérez vos articles, brouillons et archives.
                                 [+ Rédiger un nouvel article]

[ Tous (8) ]  [ Brouillons (3) ]  [ Publiés (5) ]  [ Archivés (0) ]

┌──────────────────────────────────────────────────────────────────┐
│                                                                  │
│  [cover]  Titre de l'article                                     │
│           Extrait sur 1-2 lignes…                                │
│           [brouillon]  Modifié il y a 2h · 4 min de lecture      │
│                                                     [Modifier]  │
│                                                                  │
│  [cover]  Un autre titre                                         │
│           …                                                      │
│           [publié]   Publié le 10/03/2026 · 6 min               │
│                                       [Voir] [Modifier] [⋯]     │
└──────────────────────────────────────────────────────────────────┘
```

### Component

```php
class MyPosts extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'all'; // 'all' | 'draft' | 'published' | 'archived'

    public function updatingFilter(): void { $this->resetPage(); }

    public function delete(int $postId): void
    {
        $post = BlogPost::findOrFail($postId);
        $this->authorize('delete', $post);

        // Best-effort: delete inline images referenced only by this post
        app(BlogImageCleanupService::class)->cleanupOrphanedImages($post);

        $title = $post->title;
        $post->delete();
        session()->flash('success', "Article « {$title} » supprimé.");
    }

    public function render()
    {
        $posts = BlogPost::query()
            ->where('user_id', Auth::id())
            ->when($this->filter !== 'all',
                fn ($q) => $q->where('status', $this->filter))
            ->orderByRaw("
                CASE status
                    WHEN 'draft' THEN 1
                    WHEN 'published' THEN 2
                    ELSE 3
                END
            ")
            ->latest('updated_at')
            ->paginate(15);

        return view('livewire.blog.my-posts', compact('posts'));
    }
}
```

The `BlogImageCleanupService` (new, small, ~40 LoC) parses the post's HTML
with `DOMDocument`, extracts `<img src>` URLs that start with the app's
`public` disk URL, and for each one checks whether any *other* published post
references it. Those referenced elsewhere are kept; the rest are deleted from
disk. Best-effort, non-blocking: if it fails, the post deletion still proceeds.

### Layout reuse

Uses `layouts.app` (the public site layout), **not** `layouts.admin`. This
keeps the page accessible to a plain `member` without needing
`admin.access` permission. The admin sidebar from ② RBAC does not apply.

## 15. Preview page `/blog/preview/{post}` — `App\Livewire\Blog\PreviewPost`

### Access gate

```php
public function mount(BlogPost $post): void
{
    if ($post->user_id !== Auth::id()
        && ! Auth::user()->can('posts.edit.any')) {
        abort(403);
    }

    $this->post = $post;
}
```

Only the author or users with `posts.edit.any` (= admins) can preview a
non-public post.

### Rendering

Renders the same template as `blog/show.blade.php`, with a non-dismissible
alert banner at the top of the page:

```blade
<div class="bg-amber-50 border-b-2 border-amber-200 px-4 py-3">
    <div class="max-w-3xl mx-auto flex items-center justify-between">
        <p class="text-sm font-semibold text-amber-800 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Aperçu — Cet article est {{ $post->status }} et n'est pas visible publiquement.
        </p>
        <a href="{{ route('blog.edit', $post->slug) }}"
           class="text-sm font-semibold text-[#0066CC] hover:underline">
            Retour à l'édition
        </a>
    </div>
</div>
```

An SVG heroicon (`exclamation-triangle`) is used rather than an emoji, to
stay consistent with the site's "no emoji" design convention.

Comments are shown for context but the comment submission form is replaced
by a neutral message "Aperçu — les commentaires sont désactivés sur les
aperçus".

### Opening from the editor

In `CreatePost` / `EditPost`, the "Aperçu" button is a plain anchor with
`target="_blank"`:

```blade
@if ($post)
    <a href="{{ route('blog.preview', $post) }}"
       target="_blank"
       class="text-sm font-semibold text-[#0066CC] border border-gray-200 px-4 py-2 hover:border-[#0066CC] transition">
        Aperçu ↗
    </a>
@endif
```

The author can keep the editor and the preview tab side-by-side and iterate
visually.

## 16. SEO meta rendering in `blog/show.blade.php`

The `<head>` section of the layout includes OpenGraph and Twitter Card tags
pulled from the resolved accessors:

```blade
@section('title', $post->resolved_meta_title)
@section('description', $post->resolved_meta_description)

@section('meta')
    <meta property="og:title" content="{{ $post->resolved_meta_title }}">
    <meta property="og:description" content="{{ $post->resolved_meta_description }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('blog.show', $post->slug) }}">
    @if ($post->featured_image_url)
        <meta property="og:image" content="{{ $post->featured_image_url }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $post->resolved_meta_title }}">
    <meta name="twitter:description" content="{{ $post->resolved_meta_description }}">
@endsection
```

The `layouts/app.blade.php` layout must `@yield('meta')` inside `<head>` if
it doesn't already.

## 17. Nav link "Mes articles" — `partials/nav.blade.php`

For logged-in users, add a link between "Mon profil" and the logout form:

```blade
@auth
    <a href="{{ route('profile.edit') }}"
       class="text-sm font-medium text-gray-600 hover:text-[#111827] transition hidden sm:block">
        Mon profil
    </a>
    <a href="{{ route('blog.mine') }}"
       class="text-sm font-medium text-gray-600 hover:text-[#111827] transition hidden sm:block">
        Mes articles
    </a>
    <form method="POST" action="{{ route('logout') }}" class="inline">
        @csrf
        <button type="submit" class="text-sm font-medium text-gray-500 hover:text-[#DC143C] transition">
            Déconnexion
        </button>
    </form>
@else
    …
@endauth
```

## 18. Testing strategy

**20 tests** across unit and feature layers.

### Unit (4)

1. `BlogContentSanitizerTest::strips_script_tags`
2. `BlogContentSanitizerTest::allows_whitelisted_youtube_iframe`
3. `BlogContentSanitizerTest::strips_non_whitelist_iframe`
4. `BlogImageUploaderTest::cover_resizes_to_1600_900_jpeg`

### Feature — editor flow (7)

5. `CreatePostTest::autosave_does_nothing_when_title_is_empty`
6. `CreatePostTest::autosave_creates_draft_when_title_is_non_empty`
7. `CreatePostTest::autosave_updates_existing_draft_on_subsequent_calls`
8. `CreatePostTest::save_persists_full_content_and_meta_fields`
9. `CreatePostTest::save_rejects_malicious_script_in_content`
10. `EditPostTest::member_can_edit_own_post_but_not_others`
11. `EditPostTest::admin_can_edit_any_post`

### Feature — image upload (3)

12. `BlogImageUploadTest::authenticated_member_can_upload_jpeg_under_10mb`
13. `BlogImageUploadTest::rejects_svg_upload`
14. `BlogImageUploadTest::rejects_files_over_10mb`

### Feature — preview + my-articles (4)

15. `PreviewPostTest::author_can_preview_own_draft`
16. `PreviewPostTest::non_author_cannot_preview_draft`
17. `MyPostsTest::lists_only_own_posts`
18. `MyPostsTest::filters_by_status`

### Feature — migration (2)

19. `BlogMigrationTest::wraps_plain_text_paragraphs_in_p_tags`
20. `BlogMigrationTest::preserves_accents_during_migration`

## 19. Acceptance criteria

- [ ] TipTap editor loads on `/blog/rediger` and `/blog/{slug}/modifier` with
      the full toolbar (headings, bold, italic, underline, strikethrough,
      link, lists, blockquote, code block, table, image, YouTube, Vimeo).
- [ ] Uploading an inline image persists the file to `storage/app/public/blog/inline/{Y/m}/` with dimensions ≤ 1400 px wide, returns a public URL, and
      the image is inserted at the cursor position with its alt text.
- [ ] The alt-text modal is forced open before image insertion. An empty
      alt is accepted but the modal must be confirmed.
- [ ] Autosave triggers 3 s after the last keystroke, creates the row on
      first save, updates it on subsequent calls, and updates the
      "Enregistré il y a Xs" indicator.
- [ ] Autosave is suppressed as long as the title is empty.
- [ ] A content containing `<script>alert('xss')</script>` has the script
      removed by HTMLPurifier before persistence.
- [ ] An iframe pointing to `https://evil.com/` is removed; an iframe
      pointing to `https://www.youtube.com/embed/XYZ` is preserved.
- [ ] `/mes-articles` shows only the posts owned by the logged-in user,
      with the status tab filter working.
- [ ] `/blog/preview/{post}` renders the draft like the public template
      with the amber alert banner, and returns 403 for non-owners without
      `posts.edit.any`.
- [ ] The SEO panel lets the author set `meta_title` and `meta_description`
      separately from the visible title and excerpt.
- [ ] OpenGraph and Twitter Card meta tags on `/blog/{slug}` use the
      resolved values (meta_* if present, fallback otherwise).
- [ ] The migration script converts the 25 plain-text posts to HTML
      paragraph-wrapped without losing content. A JSON backup of the
      original rows exists in `storage/app/backups/` and `migrate:rollback`
      restores them correctly.
- [ ] All 20 tests pass.
- [ ] The existing blog test suite still passes (no regression).

## 20. Cross-project dependencies

This spec consumes contracts from:

- **② RBAC** — `posts.create`, `posts.publish.own`, `posts.edit.own`,
  `posts.edit.any`, `posts.delete.any`, `admin.access`. All six are defined
  in the ② RBAC spec's permission inventory (§5).
- **① Settings** — `blog.public_creation` and `blog.require_moderation`.
  The `BlogPostPolicy::create` and the draft-forcing logic from the
  ① Settings plan still apply; this spec does not change them.

This spec is consumed by:

- **④ Newsletter** — the newsletter composer reuses the same `TipTapEditor`
  component (same Alpine module, same HTMLPurifier config) for campaign
  drafting. Zero new editor code in ④.

## 21. Open questions / follow-ups deferred

- **Image cleanup on update** — when an editor removes an image from an
  article, the orphaned file stays on disk. The `BlogImageCleanupService`
  in §14 only runs on delete. A scheduled `blog:cleanup-orphan-images`
  command could be added later that scans all files in `blog/inline/`
  against all posts' content and deletes unreferenced files. Low priority.
- **Reading time accessor** uses `strip_tags` which is HTML-safe but counts
  `<pre><code>` blocks as normal words. A more sophisticated calculation
  (code blocks count as 1 minute per 25 lines) is deferred.
- **Markdown shortcut input** — TipTap StarterKit supports some markdown
  shortcuts by default (`# ` for H1 if enabled, `**bold**`, etc). Leaving
  these enabled is a free UX win; non-goal to extend them.
- **Editor theme** — currently uses the default Tailwind `prose` class from
  `@tailwindcss/typography`. Not currently installed. The spec assumes it
  will be added as a Vite dep alongside TipTap, otherwise the rendered HTML
  in the editor won't look typographically refined. **Action item for the
  implementation plan**: `npm install @tailwindcss/typography` and add
  `@plugin "@tailwindcss/typography"` in `resources/css/app.css`.
