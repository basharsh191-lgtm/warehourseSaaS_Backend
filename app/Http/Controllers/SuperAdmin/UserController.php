<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Companie;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],
            'role' => ['nullable', Rule::in(['super_admin', 'company_admin'])],
        ]);

        $users = User::query()
            ->with('company:id,name')
            ->when($filters['company_id'] ?? null, fn ($query, $companyId) => $query->where('company_id', $companyId))
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $companies = Companie::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.users.index', compact('users', 'companies', 'filters'));
    }
}
