<?php

namespace App\Models;

use App\Enums\SneakerCondition;
use App\Enums\SneakerDecision;
use App\Enums\SneakerStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Sneaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_number',
        'model',
        'colorway',
        'style_code',
        'size',
        'condition',
        'has_box',
        'store',
        'cost_paid',
        'stockx_price_usd',
        'stockx_checked_at',
        'usd_multiplier',
        'sale_price_gt',
        'decision',
        'stockx_url',
        'notes',
        'is_public',
        'status',
        'slug',
        'photos',
    ];

    protected function casts(): array
    {
        return [
            'condition' => SneakerCondition::class,
            'decision' => SneakerDecision::class,
            'status' => SneakerStatus::class,
            'has_box' => 'boolean',
            'is_public' => 'boolean',
            'stockx_checked_at' => 'date',
            'cost_paid' => 'integer',
            'stockx_price_usd' => 'integer',
            'sale_price_gt' => 'integer',
            'usd_multiplier' => 'integer',
            'photos' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Sneaker $sneaker) {
            if (empty($sneaker->slug)) {
                $sneaker->slug = Str::slug($sneaker->model . ' ' . $sneaker->colorway . ' ' . $sneaker->size);
            }

            if (empty($sneaker->usd_multiplier)) {
                $sneaker->usd_multiplier = 11;
            }

            if (empty($sneaker->status)) {
                $sneaker->status = SneakerStatus::Available;
            }
        });
    }

    // Calculated attributes

    protected function stockxGt(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->stockx_price_usd || ! $this->usd_multiplier) {
                return null;
            }

            return $this->stockx_price_usd * $this->usd_multiplier;
        });
    }

    protected function profit(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->sale_price_gt || ! $this->cost_paid) {
                return null;
            }

            return $this->sale_price_gt - $this->cost_paid;
        });
    }

    protected function margin(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->profit || ! $this->cost_paid) {
                return null;
            }

            return round(($this->profit / $this->cost_paid) * 100, 1);
        });
    }

    // Scopes

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', SneakerStatus::Available);
    }

    public function scopeForSale(Builder $query): Builder
    {
        return $query->whereIn('decision', [
            SneakerDecision::VENTA,
            SneakerDecision::POSIBLE_VENTA,
            SneakerDecision::VENTA_CONDICIONAL,
            SneakerDecision::VENTA_GANCHO,
        ]);
    }
}
