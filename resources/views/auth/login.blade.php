@extends('layouts.auth')

@section('title', 'تسجيل الدخول')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="mb-7 text-center">
            <span class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-900/50 dark:text-emerald-300 dark:ring-emerald-800">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M15 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM4 20a8 8 0 0 1 16 0M19 8h3m-1.5-1.5v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">مرحباً بعودتك</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">سجّل الدخول لمتابعة إدارة مساحة عملك.</p>
        </div>

        @if (session('status'))
            <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">البريد الإلكتروني</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="name@company.com" @class([
                    'block w-full rounded-xl border bg-white/80 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500',
                    'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('email'),
                    'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100' => ! $errors->has('email'),
                ])>
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <label for="password" class="text-sm font-semibold text-slate-700 dark:text-slate-200">كلمة المرور</label>
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-emerald-700 transition hover:text-emerald-800 hover:underline">نسيت كلمة المرور؟</a>
                </div>
                <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="أدخل كلمة المرور" class="block w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:ring-emerald-900">
            </div>

            <label for="remember" class="inline-flex cursor-pointer items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                <input id="remember" name="remember" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                تذكرني على هذا الجهاز
            </label>

            <button type="submit" class="group flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                تسجيل الدخول
                <svg class="h-4 w-4 transition group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M19 12H5m0 0 7-7m-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </form>

        <div class="my-7 flex items-center gap-4">
            <span class="h-px flex-1 bg-slate-100 dark:bg-slate-700"></span>
            <span class="text-xs text-slate-400 dark:text-slate-500">أو</span>
            <span class="h-px flex-1 bg-slate-100 dark:bg-slate-700"></span>
        </div>

        <p class="text-center text-sm text-slate-500 dark:text-slate-400">
            جديد هنا؟
            <a href="{{ route('register') }}" class="font-bold text-emerald-700 underline-offset-4 transition hover:text-emerald-800 hover:underline">إنشاء حساب شركة جديدة</a>
        </p>
    </div>
@endsection
