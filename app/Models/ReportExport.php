<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportExport extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'status',
        'filters',
        'file_path',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
        ];
    }
}
