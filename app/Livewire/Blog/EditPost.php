<?php

namespace App\Livewire\Blog;

use App\Models\BlogPost;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditPost extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public BlogPost $post;

    public string $title = '';
    public string $slug = '';
    public string $content = '';
    public string $excerpt = '';
    public ?int $category_id = null;
    public string $status = 'draft';
    public $featuredImage = null;

    protected function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'slug'          => ['required', 'string', 'max:255', 'unique:blog_posts,slug,' . $this->post->id],
            'content'       => ['required', 'string'],
            'excerpt'       => ['nullable', 'string', 'max:500'],
            'category_id'   => ['nullable', 'exists:blog_categories,id'],
            'status'        => ['required', 'in:draft,published'],
            'featuredImage' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function mount(string $slug): void
    {
        $this->post = BlogPost::where('slug', $slug)->firstOrFail();

        $this->authorize('update', $this->post);

        $this->title       = $this->post->title;
        $this->slug        = $this->post->slug;
        $this->content     = $this->post->content;
        $this->excerpt     = $this->post->excerpt ?? '';
        $this->category_id = $this->post->category_id;
        $this->status      = $this->post->status;
    }

    public function updatedTitle(string $value): void
    {
        if ($this->slug === Str::slug($this->post->title)) {
            $this->slug = Str::slug($value);
        }
    }

    public function save(): void
    {
        $this->authorize('update', $this->post);

        $validated = $this->validate();

        // Force draft for non-admin publications if moderation is required
        if ($validated['status'] === 'published'
            && setting('blog.require_moderation', false)
            && ! Auth::user()->hasRole('admin')) {
            $validated['status'] = 'draft';
            $this->status = 'draft';
            session()->flash('info', 'Votre article sera visible après validation par un administrateur.');
        }

        $imageUrl = $this->post->featured_image_url;
        if ($this->featuredImage) {
            $path     = $this->featuredImage->store('blog/covers', 's3');
            $imageUrl = Storage::disk('s3')->url($path);
        }

        $wasPublished = $this->post->status === 'published';

        $this->post->update([
            'title'              => $this->title,
            'slug'               => $this->slug,
            'content'            => $this->content,
            'excerpt'            => $this->excerpt ?: null,
            'category_id'        => $this->category_id,
            'featured_image_url' => $imageUrl,
            'status'             => $validated['status'],
            'published_at'       => $validated['status'] === 'published' && ! $wasPublished ? now() : $this->post->published_at,
        ]);

        $this->redirect(route('blog.show', $this->post->slug), navigate: true);
    }

    public function render()
    {
        return view('livewire.blog.edit-post', [
            'categories' => \App\Models\BlogCategory::orderBy('name')->get(),
        ]);
    }
}
