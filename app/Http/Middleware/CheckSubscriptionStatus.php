<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role === 'super_admin') {
            return $next($request);
        }

        if (! $user->company_id) {
            abort(403, 'يجب ربط المستخدم بشركة للوصول إلى هذه الصفحة.');
        }

        $status = $user->company()->value('status');

        if ($status === 'suspended') {
            return redirect()
                ->route('subscription.suspended')
                ->with('error', 'حساب الشركة موقوف، يرجى التواصل مع الدعم');
        }

        if (! in_array($status, ['active', 'trial'], true)) {
            abort(403, 'حالة الاشتراك لا تسمح بالوصول إلى هذه الصفحة.');
        }

        return $next($request);
    }
}
