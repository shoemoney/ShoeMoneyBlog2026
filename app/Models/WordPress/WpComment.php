<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class WpComment extends Model
{
    protected $connection = 'wordpress';

    protected $table = 'comments';

    protected $primaryKey = 'comment_ID';

    public $timestamps = false;

    /**
     * Get the post this comment belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(WpPost::class, 'comment_post_ID', 'ID');
    }

    /**
     * Get the parent comment.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(WpComment::class, 'comment_parent', 'comment_ID');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(WpComment::class, 'comment_parent', 'comment_ID');
    }

    /**
     * Scope to only approved comments.
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('comment_approved', '1');
    }
}
