<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Cashier\Billable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, HasRoles, Impersonate, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'points_balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class);
    }

    public function awardPoints(int $points, string $description, ?Order $order = null): void
    {
        $this->increment('points_balance', $points);
        $this->pointsTransactions()->create([
            'order_id' => $order?->id,
            'points' => $points,
            'type' => 'earned',
            'description' => $description,
        ]);
    }

    public function redeemPoints(int $points, string $description, ?Order $order = null): void
    {
        $this->decrement('points_balance', $points);
        $this->pointsTransactions()->create([
            'order_id' => $order?->id,
            'points' => -$points,
            'type' => 'redeemed',
            'description' => $description,
        ]);
    }

    public function canImpersonate(): bool
    {
        return $this->hasRole('admin');
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->hasRole('admin');
    }
}
