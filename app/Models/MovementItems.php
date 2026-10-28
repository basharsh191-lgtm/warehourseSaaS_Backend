<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovementItems extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'total_net' => 'decimal:2',
        ];
    }

    public function movement()
    {
        return $this->belongsTo(Movement::class);
    }

    public function relatedMovementItem()
    {
        return $this->belongsTo(MovementItems::class, 'related_movement_item_id');
    }

    public function returns()
    {
        return $this->hasMany(MovementItems::class, 'related_movement_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function operations()
    {
        return $this->hasMany(Operation::class, 'movement_item_id');
    }
}
