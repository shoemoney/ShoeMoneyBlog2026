<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'wordpress_id',
        'post_id',
        'user_id',
        'parent_id',
        'author_name',
        'author_email',
        'author_url',
        'author_ip',
        'content',
        'status',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRootComments($query)
    {
        return $query->whereNull('parent_id');
    }

    // Gravatar URL helper
    public function getGravatarUrlAttribute(): string
    {
        $hash = md5(strtolower(trim($this->author_email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=80";
    }

    // Check if this comment is a reply
    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }
}
