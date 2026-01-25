<?php

namespace App\Livewire\Comments;

use App\Models\Post;
use Livewire\Attributes\On;
use Livewire\Component;

class CommentSection extends Component
{
    public Post $post;
    public ?int $replyingTo = null;
    public bool $showPendingMessage = false;

    public function mount(Post $post): void
    {
        $this->post = $post;
    }

    public function startReply(int $commentId): void
    {
        $this->replyingTo = $commentId;
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
    }

    #[On('comment-submitted')]
    public function handleCommentSubmitted(bool $isPending = false): void
    {
        $this->replyingTo = null;
        $this->showPendingMessage = $isPending;
    }

    public function render()
    {
        // Load root comments with nested replies (3 levels deep)
        // Each level filters for approved comments only
        $comments = $this->post->comments()
            ->rootComments()
            ->approved()
            ->with([
                'replies' => fn($q) => $q->approved()->with([
                    'replies' => fn($q2) => $q2->approved()->with([
                        'replies' => fn($q3) => $q3->approved()
                    ])
                ])
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        $commentCount = $this->post->comments()->approved()->count();

        return view('livewire.comments.comment-section', [
            'comments' => $comments,
            'commentCount' => $commentCount,
        ]);
    }
}
