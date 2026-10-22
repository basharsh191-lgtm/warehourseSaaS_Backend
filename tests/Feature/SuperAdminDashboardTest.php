<?php

use App\Models\Companie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createSuperAdmin(array $attributes = []): User
{
    return User::query()->create([
        'name' => 'مدير المنصة',
        'email' => 'platform-admin@example.test',
        'password' => 'password',
        'role' => 'super_admin',
        'is_active' => true,
        ...$attributes,
    ]);
}

function createDashboardCompany(array $attributes = []): Companie
{
    return Companie::query()->create([
        'name' => 'شركة تجريبية',
        'status' => 'trial',
        'percent_markup' => 200,
        ...$attributes,
    ]);
}

test('super admin dashboard shows company subscription statistics', function () {
    createSuperAdmin();
    createDashboardCompany(['name' => 'شركة تجريبية']);
    createDashboardCompany(['name' => 'شركة نشطة', 'status' => 'active']);
    createDashboardCompany(['name' => 'شركة موقوفة', 'status' => 'suspended']);

    $this->actingAs(User::query()->where('role', 'super_admin')->first())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('إجمالي الشركات')
        ->assertSee('الشركات النشطة')
        ->assertSee('الفترة التجريبية')
        ->assertSee('الشركات الموقوفة')
        ->assertSee('شركة نشطة')
        ->assertSee('شركة موقوفة');
});

test('company administrators cannot access super admin pages', function () {
    $company = createDashboardCompany();
    $user = User::query()->create([
        'company_id' => $company->id,
        'name' => 'مدير شركة',
        'email' => 'company-admin@example.test',
        'password' => 'password',
        'role' => 'company_admin',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('super admin can update company details and subscription status', function () {
    $company = createDashboardCompany();

    $this->actingAs(createSuperAdmin())
        ->put(route('admin.companies.update', $company), [
            'name' => 'شركة محدثة',
            'email' => 'contact@example.test',
            'phone' => '+1-555-0100',
            'address' => 'عنوان الشركة',
            'status' => 'suspended',
            'trial_ends_at' => '2026-12-31',
            'percent_markup' => '35.50',
        ])
        ->assertRedirect(route('admin.companies.edit', $company))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'name' => 'شركة محدثة',
        'email' => 'contact@example.test',
        'phone' => '+1-555-0100',
        'address' => 'عنوان الشركة',
        'status' => 'suspended',
        'percent_markup' => 35.50,
    ]);

    expect($company->fresh()->trial_ends_at->toDateString())->toBe('2026-12-31');
});

test('setting an expired trial date suspends the company automatically', function () {
    $company = createDashboardCompany();

    $this->actingAs(createSuperAdmin())
        ->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'email' => null,
            'phone' => null,
            'address' => null,
            'status' => 'trial',
            'trial_ends_at' => today()->subDay()->toDateString(),
            'percent_markup' => 200,
        ])
        ->assertRedirect(route('admin.companies.edit', $company));

    expect($company->fresh()->status)->toBe('suspended');
});

test('super admin can extend a company trial by the requested number of days', function () {
    $startDate = today()->addDays(4);
    $company = createDashboardCompany([
        'trial_ends_at' => $startDate,
    ]);

    $this->actingAs(createSuperAdmin())
        ->post(route('admin.companies.extend-trial', $company), ['days' => 14])
        ->assertRedirect(route('admin.companies.edit', $company))
        ->assertSessionHas('success');

    expect($company->fresh()->status)->toBe('trial')
        ->and($company->fresh()->trial_ends_at->toDateString())->toBe($startDate->addDays(14)->toDateString());
});

test('company edit page shows remaining trial days', function () {
    $company = createDashboardCompany([
        'trial_ends_at' => today()->addDays(5),
    ]);

    $this->actingAs(createSuperAdmin())
        ->get(route('admin.companies.edit', $company))
        ->assertOk()
        ->assertSee('متبقي 5 أيام');
});

test('extending an active company trial keeps its active status', function () {
    $company = createDashboardCompany([
        'status' => 'active',
        'trial_ends_at' => today(),
    ]);

    $this->actingAs(createSuperAdmin())
        ->post(route('admin.companies.extend-trial', $company), ['days' => 14])
        ->assertRedirect(route('admin.companies.edit', $company));

    expect($company->fresh()->status)->toBe('active')
        ->and($company->fresh()->trial_ends_at->toDateString())->toBe(today()->addDays(14)->toDateString());
});

test('expired trials are suspended when company routes are accessed', function () {
    $company = createDashboardCompany([
        'trial_ends_at' => today()->subDay(),
    ]);
    $user = User::query()->create([
        'company_id' => $company->id,
        'name' => 'مدير الشركة',
        'email' => 'expired-company-admin@example.test',
        'password' => 'password',
        'role' => 'company_admin',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('categories.index'))
        ->assertRedirect(route('subscription.suspended'));

    expect($company->fresh()->status)->toBe('suspended');
});

test('invalid trial extension days are rejected', function () {
    $company = createDashboardCompany();

    $this->actingAs(createSuperAdmin())
        ->post(route('admin.companies.extend-trial', $company), ['days' => 0])
        ->assertSessionHasErrors('days');
});

test('super admin users page filters by company and role', function () {
    $company = createDashboardCompany(['name' => 'شركة التصفية']);
    createSuperAdmin();
    User::query()->create([
        'company_id' => $company->id,
        'name' => 'مدير الشركة',
        'email' => 'manager@example.test',
        'password' => 'password',
        'role' => 'company_admin',
        'is_active' => true,
    ]);

    $this->actingAs(User::query()->where('role', 'super_admin')->first())
        ->get(route('admin.users.index', [
            'company_id' => $company->id,
            'role' => 'company_admin',
        ]))
        ->assertOk()
        ->assertSee('مدير الشركة')
        ->assertSee('شركة التصفية')
        ->assertDontSee('platform-admin@example.test');
});

test('invalid user filters are rejected', function () {
    $this->actingAs(createSuperAdmin())
        ->get(route('admin.users.index', ['role' => 'unknown']))
        ->assertSessionHasErrors('role');
});

test('user seeder persists the super admin role on the demo account', function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\PermissionSeeder::class);
    $this->seed(\Database\Seeders\UserSeeder::class);

    $user = User::query()->where('email', 'admin@example.test')->firstOrFail();

    expect($user->role)->toBe('super_admin')
        ->and($user->hasRole('super_admin'))->toBeTrue();
});
