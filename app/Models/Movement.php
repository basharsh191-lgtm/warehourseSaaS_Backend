<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    protected $guarded = [];

    public function getCurrencyAttribute(?string $value): string
    {
        return $value ?: ($this->company?->currency ?? 'USD');
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'discount' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Companie::class);
    }

    public function party()
    {
        return $this->belongsTo(Partie::class);
    }

    public function relatedMovement()
    {
        return $this->belongsTo(Movement::class, 'related_movement_id');
    }

    public function returns()
    {
        return $this->hasMany(Movement::class, 'related_movement_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items()
    {
        return $this->hasMany(MovementItems::class);
    }

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }
}
