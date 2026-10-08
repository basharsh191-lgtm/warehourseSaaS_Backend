<?php

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }

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

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
