<?php

namespace App\Livewire\Admin\Comments;

use App\Models\Comment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.admin.layouts.app')]
#[Title('Comment Moderation')]
class CommentModeration extends Component
{
    use WithPagination;

    public string $status = 'pending';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function approve(Comment $comment): void
    {
        $comment->update(['status' => 'approved']);
        session()->flash('success', 'Comment approved.');
    }

    public function reject(Comment $comment): void
    {
        $comment->update(['status' => 'spam']);
        session()->flash('success', 'Comment marked as spam.');
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
        session()->flash('success', 'Comment deleted permanently.');
    }

    public function render()
    {
        return view('livewire.admin.comments.comment-moderation', [
            'comments' => Comment::query()
                ->with(['post:id,title,slug,published_at'])
                ->where('status', $this->status)
                ->orderBy('created_at', 'desc')
                ->paginate(20),
            'counts' => [
                'pending' => Comment::pending()->count(),
                'approved' => Comment::approved()->count(),
                'spam' => Comment::where('status', 'spam')->count(),
            ],
        ]);
    }
}
