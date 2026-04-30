<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Waitlist extends Model
{
    protected $fillable = ['user_id', 'email', 'waitlistable_type', 'waitlistable_id', 'notified_at'];

    public function casts(): array
    {
        return [
            'notified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function waitlistable(): MorphTo
    {
        return $this->morphTo();
    }
}
