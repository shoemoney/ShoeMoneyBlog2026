<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    protected $fillable = [
        'title',
        'type',
        'content',
        'settings',
        'position',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];
}
