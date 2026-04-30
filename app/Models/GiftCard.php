<?php

namespace App\Models;

use Database\Factories\GiftCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GiftCard extends Model
{
    /** @use HasFactory<GiftCardFactory> */
    use HasFactory;

    protected $fillable = [
        'code', 'original_amount', 'remaining_balance',
        'issued_by_user_id', 'redeemed_by_user_id',
        'is_active', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public function redeemedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by_user_id');
    }

    public function isUsable(): bool
    {
        return $this->is_active
            && $this->remaining_balance > 0
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper('GC-'.Str::random(4).'-'.Str::random(4));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
