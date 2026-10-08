<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    protected $guarded = [];
public function company()
    {
        return $this->belongsTo(Companie::class);
    }

    public function movement()
    {
        return $this->belongsTo(Movement::class);
    }

    public function movementItem()
    {
        return $this->belongsTo(MovementItems::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function party()
    {
        return $this->belongsTo(Partie::class);
    }
}
