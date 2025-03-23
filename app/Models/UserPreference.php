<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'preferred_categories'
    ];

    protected $casts = [
        'preferred_categories' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 