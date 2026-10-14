<?php

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'company_id',
        'category_id',
        'barcode',
        'name',
        'unit',
        'location',
        'item_shape',
        'price_sale_custom',
        'stock_min',
        'is_active',
    ];

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

    public function category()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function movementItems()
    {
        return $this->hasMany(MovementItems::class);
    }

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }
}
