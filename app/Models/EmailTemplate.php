<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;
    
    // Template types
    public const string TYPE_PRE_EXPIRATION = 'pre_expiration';
    public const string TYPE_POST_EXPIRATION = 'post_expiration';
    
    protected $fillable = [
        'name',
        'type',
        'subject',
        'body',
        'language_code',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
}