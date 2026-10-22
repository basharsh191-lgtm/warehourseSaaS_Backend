<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Companie extends Model
{
    protected $guarded = [];

    public static function suspendExpiredTrials(): int
    {
        return static::query()
            ->where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereDate('trial_ends_at', '<', today())
            ->update([
                'status' => 'suspended',
                'updated_at' => now(),
            ]);
    }

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'date',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function categories()
    {
        return $this->hasMany(Categorie::class, 'company_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'company_id');
    }

    public function parties()
    {
        return $this->hasMany(Partie::class, 'company_id');
    }

    public function movements()
    {
        return $this->hasMany(Movement::class, 'company_id');
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'company_id');
    }
}
