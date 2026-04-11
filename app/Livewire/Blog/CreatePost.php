<?php

namespace App\Livewire\Blog;

use App\Models\BlogPost;
use App\Services\BlogContentSanitizer;
use App\Services\BlogImageUploader;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreatePost extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public ?BlogPost $post = null;

    public string $title            = '';
    public string $slug             = '';
    public string $content          = '';
    public string $excerpt          = '';
    public ?string $metaTitle       = null;
    public ?string $metaDescription = null;
    public ?int $category_id        = null;
    public string $status           = 'draft';
    public $featuredImage           = null;
    public ?string $autoSavedAt     = null;

    protected function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'slug'            => ['required', 'string', 'max:255'],
            'content'         => ['required', 'string'],
            'excerpt'         => ['nullable', 'string', 'max:500'],
            'metaTitle'       => ['nullable', 'string', 'max:70'],
            'metaDescription' => ['nullable', 'string', 'max:160'],
            'category_id'     => ['nullable', 'exists:blog_categories,id'],
            'status'          => ['required', 'in:draft,published'],
            'featuredImage'   => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function updatedTitle(string $value): void
    {
        if (! $this->slug || $this->slug === Str::slug($this->title)) {
            $this->slug = Str::slug($value);
        }
        $this->autoSave();
    }

    public function updatedContent(): void
    {
        $this->autoSave();
    }

    public function autoSave(): void
    {
        if (trim($this->title) === '') {
            return;
        }

        $this->authorize('create', BlogPost::class);

        $sanitizer = app(BlogContentSanitizer::class);
        $cleanContent = $sanitizer->clean($this->content);

        if ($this->post === null) {
            $this->post = BlogPost::create([
                'user_id' => Auth::id(),
                'title'   => $this->title,
                'slug'    => $this->slug ?: $this->generateUniqueSlug(Str::slug($this->title)),
                'content' => $cleanContent,
                'status'  => 'draft',
            ]);
            $this->slug = $this->post->slug;
        } else {
            $this->post->update([
                'title'   => $this->title,
                'content' => $cleanContent,
            ]);
        }

        $this->autoSavedAt = now()->toIso8601String();
    }

    public function save(): void
    {
        $this->authorize('create', BlogPost::class);

        $validated = $this->validate();

        // Force draft when moderation is required, unless user can bypass
        $authUser = Auth::user();
        $canBypassModeration = $authUser->can('posts.publish.own')
            || $authUser->can('posts.edit.any');

        if ($validated['status'] === 'published'
            && setting('blog.require_moderation', false)
            && ! $canBypassModeration) {
            $validated['status'] = 'draft';
            $this->status = 'draft';
            session()->flash('info', 'Votre article sera visible après validation par un administrateur.');
        }

        $sanitizer = app(BlogContentSanitizer::class);
        $cleanContent = $sanitizer->clean($this->content);

        $imageUrl = $this->post?->featured_image_url;
        if ($this->featuredImage) {
            $imageUrl = app(BlogImageUploader::class)
                ->uploadCover($this->featuredImage, $this->slug ?: Str::slug($this->title));
        }

        $payload = [
            'user_id'            => Auth::id(),
            'title'              => $this->title,
            'slug'               => $this->slug ?: $this->generateUniqueSlug(Str::slug($this->title)),
            'content'            => $cleanContent,
            'excerpt'            => $this->excerpt ?: null,
            'meta_title'         => $this->metaTitle ?: null,
            'meta_description'  => $this->metaDescription ?: null,
            'category_id'        => $this->category_id,
            'featured_image_url' => $imageUrl,
            'status'             => $validated['status'],
            'published_at'       => $validated['status'] === 'published' ? now() : null,
        ];

        if ($this->post === null) {
            $this->post = BlogPost::create($payload);
        } else {
            $this->post->update($payload);
        }

        $this->redirect(route('blog.mine'), navigate: true);
    }

    private function generateUniqueSlug(string $base): string
    {
        $slug = $base ?: 'article';
        $n = 1;
        while (BlogPost::where('slug', $slug)
            ->when($this->post, fn ($q) => $q->where('id', '!=', $this->post->id))
            ->exists()) {
            $slug = ($base ?: 'article') . '-' . (++$n);
        }
        return $slug;
    }

    public function render()
    {
        return view('livewire.blog.create-post', [
            'categories' => \App\Models\BlogCategory::orderBy('name')->get(),
        ]);
    }
}
