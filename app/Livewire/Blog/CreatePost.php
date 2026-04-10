<?php

namespace App\Livewire\Blog;

use App\Models\BlogPost;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreatePost extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

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
            'slug'          => ['required', 'string', 'max:255', 'unique:blog_posts,slug'],
            'content'       => ['required', 'string'],
            'excerpt'       => ['nullable', 'string', 'max:500'],
            'category_id'   => ['nullable', 'exists:blog_categories,id'],
            'status'        => ['required', 'in:draft,published'],
            'featuredImage' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function updatedTitle(string $value): void
    {
        if (! $this->slug || $this->slug === Str::slug($this->title)) {
            $this->slug = Str::slug($value);
        }
    }

    public function save(): void
    {
        $this->authorize('create', BlogPost::class);

        $validated = $this->validate();

        $imageUrl = null;
        if ($this->featuredImage) {
            $path     = $this->featuredImage->store('blog/covers', 's3');
            $imageUrl = Storage::disk('s3')->url($path);
        }

        $post = BlogPost::create([
            'user_id'            => Auth::id(),
            'title'              => $this->title,
            'slug'               => $this->slug,
            'content'            => $this->content,
            'excerpt'            => $this->excerpt ?: null,
            'category_id'        => $this->category_id,
            'featured_image_url' => $imageUrl,
            'status'             => $this->status,
            'published_at'       => $this->status === 'published' ? now() : null,
        ]);

        $this->redirect(route('blog.show', $post->slug), navigate: true);
    }

    public function render()
    {
        return view('livewire.blog.create-post', [
            'categories' => \App\Models\BlogCategory::orderBy('name')->get(),
        ]);
    }
}
