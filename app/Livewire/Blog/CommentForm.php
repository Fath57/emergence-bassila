<?php

namespace App\Livewire\Blog;

use App\Models\BlogComment;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CommentForm extends Component
{
    public BlogPost $post;
    public string $content = '';
    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function submit(): void
    {
        if (! setting('comments.enabled', true)) {
            $this->addError('content', 'Les commentaires sont actuellement désactivés.');
            return;
        }

        $this->validate();

        BlogComment::create([
            'blog_post_id' => $this->post->id,
            'user_id'      => Auth::id(),
            'content'      => $this->content,
            'moderated_at' => setting('comments.require_moderation', true) ? null : now(),
        ]);

        $this->content    = '';
        $this->submitted  = true;
    }

    public function render()
    {
        return view('livewire.blog.comment-form');
    }
}
