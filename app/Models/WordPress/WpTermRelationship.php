<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;

class WpTermRelationship extends Model
{
    protected $connection = 'wordpress';

    protected $table = 'term_relationships';

    public $incrementing = false;

    public $timestamps = false;
}
