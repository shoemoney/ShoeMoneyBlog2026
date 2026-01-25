<?php

namespace App\Livewire\Comments;

use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentModerationService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class CommentForm extends Component
{
    use UsesSpamProtection;
    use WithRateLimiting;

    public Post $post;
    public ?int $parentId = null;

    #[Validate('required|string|min:2|max:100')]
    public string $authorName = '';

    #[Validate('required|email|max:255')]
    public string $authorEmail = '';

    #[Validate('nullable|url|max:255')]
    public ?string $authorUrl = null;

    #[Validate('required|string|min:3|max:10000')]
    public string $content = '';

    public HoneypotData $extraFields;

    public function mount(Post $post, ?int $parentId = null): void
    {
        $this->post = $post;
        $this->parentId = $parentId;
        $this->extraFields = new HoneypotData();
    }

    public function submit(CommentModerationService $moderation): void
    {
        // Rate limiting: 5 comments per minute
        try {
            $this->rateLimit(5, 60);
        } catch (TooManyRequestsException $e) {
            $this->addError('content', 'Too many comments. Please wait a minute before posting again.');
            return;
        }

        // Spam protection (throws exception if honeypot triggered)
        $this->protectAgainstSpam();

        // Validation
        $this->validate();

        // Determine moderation status based on email history
        $normalizedEmail = strtolower(trim($this->authorEmail));
        $status = $moderation->determineStatus($normalizedEmail);

        // Create the comment
        Comment::create([
            'post_id' => $this->post->id,
            'parent_id' => $this->parentId,
            'author_name' => trim($this->authorName),
            'author_email' => $normalizedEmail,
            'author_url' => $this->authorUrl ? trim($this->authorUrl) : null,
            'author_ip' => request()->ip(),
            'content' => trim($this->content),
            'status' => $status,
        ]);

        // Reset content field (keep name/email for convenience)
        $this->reset(['content']);

        // Notify parent component
        $this->dispatch('comment-submitted', isPending: $status === 'pending');
    }

    public function render()
    {
        return view('livewire.comments.comment-form');
    }
}
