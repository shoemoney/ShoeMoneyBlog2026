<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WpPostMeta extends Model
{
    protected $connection = 'wordpress';

    protected $table = 'postmeta';

    protected $primaryKey = 'meta_id';

    public $timestamps = false;

    /**
     * Get the post this meta belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(WpPost::class, 'post_id', 'ID');
    }
}
