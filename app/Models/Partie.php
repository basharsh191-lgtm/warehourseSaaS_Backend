<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partie extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Companie::class);
    }

    public function movements()
    {
        return $this->hasMany(Movement::class);
    }

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }
}
