<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VirtualNumber extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'country_id',
        'phone_number',
        'type',
        'status',
        'price',
        'currency',
        'expires_at',
        'cancelled_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SmsMessage::class)->latest('received_at');
    }
}
