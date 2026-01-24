<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WpUser extends Model
{
    protected $connection = 'wordpress';

    protected $table = 'users';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    /**
     * Get the posts for this user.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(WpPost::class, 'post_author', 'ID');
    }
}
