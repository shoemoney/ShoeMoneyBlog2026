<?php

namespace App\Services;

use App\Models\Comment;

class CommentModerationService
{
    /**
     * Determine the status for a new comment.
     *
     * Returns 'approved' if the email has any previously approved comment.
     * Returns 'pending' for first-time commenters.
     *
     * @param string $email The commenter's email address
     * @return string 'approved' or 'pending'
     */
    public function determineStatus(string $email): string
    {
        $normalizedEmail = strtolower(trim($email));

        $hasApprovedComment = Comment::where('author_email', $normalizedEmail)
            ->where('status', 'approved')
            ->exists();

        return $hasApprovedComment ? 'approved' : 'pending';
    }
}
