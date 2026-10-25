<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'slug', 'is_active'])]

class Category extends Model
{
    protected function casts(): array
    {
        return[
            'is_active' => 'boolean',
        ];
    }
}
