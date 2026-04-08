<?php

namespace App\Models;

use App\Enums\OfferStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    protected $fillable = [
        'user_id',
        'sneaker_id',
        'amount',
        'status',
        'admin_notes',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OfferStatus::class,
            'amount' => 'integer',
            'responded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sneaker(): BelongsTo
    {
        return $this->belongsTo(Sneaker::class);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', OfferStatus::Pending);
    }

    public function scopeForBuyer(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    public function isPending(): bool
    {
        return $this->status === OfferStatus::Pending;
    }
}
