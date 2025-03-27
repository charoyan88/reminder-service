<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'contact_email',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the order types associated with the business.
     */
    public function orderTypes(): HasMany
    {
        return $this->hasMany(OrderType::class);
    }

    /**
     * Get the orders associated with the business.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
} 