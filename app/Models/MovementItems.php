<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovementItems extends Model
{
    protected $guarded = [];
    public function movement()
    {
        return $this->belongsTo(Movement::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function operations()
    {
        return $this->hasMany(Operation::class);
    }
}
