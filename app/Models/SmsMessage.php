<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsMessage extends Model
{
    protected $fillable = [
        'virtual_number_id',
        'from_address',
        'body',
        'received_at',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function virtualNumber(): BelongsTo
    {
        return $this->belongsTo(VirtualNumber::class);
    }
}
