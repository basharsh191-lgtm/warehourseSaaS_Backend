<?php

use App\Models\Companie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

test('registration creates a trial company and company administrator', function () {
    $response = $this->post(route('register.store'), [
        'company_name' => 'شركة الاختبار',
        'name' => 'مسؤول الشركة',
        'email' => 'admin@example.com',
        'password' => 'password-123',
        'password_confirmation' => 'password-123',
    ]);

    $response->assertRedirectToRoute('dashboard');
    $this->assertAuthenticated();

    $company = Companie::where('name', 'شركة الاختبار')->firstOrFail();
    expect($company->status)->toBe('trial')
        ->and($company->trial_ends_at->toDateString())->toBe(now()->addDays(14)->toDateString())
        ->and((float) $company->percent_markup)->toBe(200.0);

    $this->assertDatabaseHas('users', [
        'company_id' => $company->id,
        'name' => 'مسؤول الشركة',
        'email' => 'admin@example.com',
        'role' => 'company_admin',
        'is_active' => true,
    ]);

    expect(Hash::check('password-123', User::where('email', 'admin@example.com')->firstOrFail()->password))->toBeTrue();
});

test('suspended company administrators cannot log in', function () {
    $company = Companie::create([
        'name' => 'شركة موقوفة',
        'status' => 'suspended',
    ]);
    User::create([
        'company_id' => $company->id,
        'name' => 'مسؤول الشركة',
        'email' => 'suspended@example.com',
        'password' => Hash::make('password-123'),
        'role' => 'company_admin',
        'is_active' => true,
    ]);

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => 'suspended@example.com',
            'password' => 'password-123',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'email' => 'حساب الشركة موقوف، يرجى التواصل مع الإدارة.',
        ]);

    $this->assertGuest();
});

test('successful login sends each supported role to its dashboard', function (string $role, string $route) {
    User::create([
        'name' => 'مستخدم الاختبار',
        'email' => "{$role}@example.com",
        'password' => Hash::make('password-123'),
        'role' => $role,
        'is_active' => true,
    ]);

    $this->post(route('login.store'), [
        'email' => "{$role}@example.com",
        'password' => 'password-123',
    ])->assertRedirectToRoute($route);

    $this->assertAuthenticated();
})->with([
    'company administrator' => ['company_admin', 'dashboard'],
    'platform administrator' => ['super_admin', 'admin.dashboard'],
]);

test('registration screen includes the company name field', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('name="company_name"', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('data-theme-icon="moon"', false)
        ->assertSee('data-theme-icon="sun"', false);
});

test('password can be reset through the password broker', function () {
    User::create([
        'name' => 'مستخدم الاختبار',
        'email' => 'reset@example.com',
        'password' => Hash::make('old-password'),
        'role' => 'company_admin',
        'is_active' => true,
    ]);
    $token = Password::createToken(User::where('email', 'reset@example.com')->firstOrFail());

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => 'reset@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirectToRoute('login');

    expect(Hash::check(
        'new-password',
        User::where('email', 'reset@example.com')->firstOrFail()->password
    ))->toBeTrue();
});

test('logout invalidates the authenticated session', function () {
    $user = User::create([
        'name' => 'مستخدم الاختبار',
        'email' => 'logout@example.com',
        'password' => Hash::make('password-123'),
        'role' => 'company_admin',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirectToRoute('login');

    $this->assertGuest();
});

test('dashboard includes the persistent dark mode toggle', function () {
    $user = User::create([
        'name' => 'مستخدم الاختبار',
        'email' => 'theme@example.com',
        'password' => Hash::make('password-123'),
        'role' => 'company_admin',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-theme-toggle', false)
        ->assertSee('data-theme-icon="moon"', false)
        ->assertSee('data-theme-icon="sun"', false);
});
