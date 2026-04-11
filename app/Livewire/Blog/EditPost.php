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

class EditPost extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public BlogPost $post;

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

    public function mount(string $slug): void
    {
        $this->post = BlogPost::where('slug', $slug)->firstOrFail();
        $this->authorize('update', $this->post);

        $this->title           = $this->post->title;
        $this->slug            = $this->post->slug;
        $this->content         = (string) $this->post->content;
        $this->excerpt         = (string) ($this->post->excerpt ?? '');
        $this->metaTitle       = $this->post->meta_title;
        $this->metaDescription = $this->post->meta_description;
        $this->category_id     = $this->post->category_id;
        $this->status          = $this->post->status;
    }

    public function updatedTitle(string $value): void
    {
        if ($this->slug === Str::slug($this->post->title)) {
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

        $this->authorize('update', $this->post);

        $sanitizer = app(BlogContentSanitizer::class);

        $this->post->update([
            'title'   => $this->title,
            'content' => $sanitizer->clean($this->content),
        ]);

        $this->autoSavedAt = now()->toIso8601String();
    }

    public function save(): void
    {
        $this->authorize('update', $this->post);

        $validated = $this->validate();

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

        $imageUrl = $this->post->featured_image_url;
        if ($this->featuredImage) {
            $imageUrl = app(BlogImageUploader::class)->uploadCover($this->featuredImage, $this->slug);
        }

        $wasPublished = $this->post->status === 'published';

        $this->post->update([
            'title'              => $this->title,
            'slug'               => $this->slug,
            'content'            => $cleanContent,
            'excerpt'            => $this->excerpt ?: null,
            'meta_title'         => $this->metaTitle ?: null,
            'meta_description'  => $this->metaDescription ?: null,
            'category_id'        => $this->category_id,
            'featured_image_url' => $imageUrl,
            'status'             => $validated['status'],
            'published_at'       => $validated['status'] === 'published' && ! $wasPublished
                ? now()
                : $this->post->published_at,
        ]);

        $this->redirect(route('blog.mine'), navigate: true);
    }

    public function render()
    {
        return view('livewire.blog.edit-post', [
            'categories' => \App\Models\BlogCategory::orderBy('name')->get(),
        ]);
    }
}
