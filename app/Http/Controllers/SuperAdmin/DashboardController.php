<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Companie;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        Companie::suspendExpiredTrials();

        $stats = [
            'total' => Companie::query()->count(),
            'active' => Companie::query()->where('status', 'active')->count(),
            'trial' => Companie::query()->where('status', 'trial')->count(),
            'suspended' => Companie::query()->where('status', 'suspended')->count(),
        ];

        $recentCompanies = Companie::query()
            ->latest()
            ->limit(8)
            ->get(['id', 'name', 'email', 'status', 'trial_ends_at', 'created_at']);

        return view('admin.dashboard', compact('stats', 'recentCompanies'));
    }
}
