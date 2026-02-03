<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_featured' => 'boolean',
        'has_ai_features' => 'boolean',
    ];

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }
}
