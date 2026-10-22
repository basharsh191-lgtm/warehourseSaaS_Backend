<?php

namespace App\Http\Controllers;

use App\Models\Companie;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'بيانات تسجيل الدخول غير صحيحة.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (! $user->is_active) {
            $this->invalidateSession($request);

            return back()
                ->withErrors(['email' => 'حساب المستخدم غير فعال، يرجى التواصل مع الإدارة.'])
                ->onlyInput('email');
        }

        if ($user->role === 'company_admin' && $user->company?->status === 'suspended') {
            $this->invalidateSession($request);

            return back()
                ->withErrors(['email' => 'حساب الشركة موقوف، يرجى التواصل مع الإدارة.'])
                ->onlyInput('email');
        }

        return match ($user->role) {
            'super_admin' => redirect()->route('admin.dashboard'),
            'company_admin' => redirect()->route('dashboard'),
            default => $this->rejectUnsupportedRole($request),
        };
    }

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $company = Companie::create([
                'name' => $validated['company_name'],
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(config('subscription.trial_days', 14)),
                'percent_markup' => 200.00,
            ]);

            $user = User::create([
                'company_id' => $company->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'company_admin', // يمكنك إزالته إن كنت تعتمد كلياً على Spatie
                'is_active' => true,
            ]);

            // إسناد الدور من خلال Spatie داخل الـ Transaction ضماناً للتكاملية
            $user->assignRole('company_admin');

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->invalidateSession($request);

        return redirect()->route('login');
    }

    private function rejectUnsupportedRole(Request $request): RedirectResponse
    {
        $this->invalidateSession($request);

        return redirect()->route('login')
            ->withErrors(['email' => 'لا يملك هذا الحساب صلاحية تسجيل الدخول.']);
    }

    private function invalidateSession(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
