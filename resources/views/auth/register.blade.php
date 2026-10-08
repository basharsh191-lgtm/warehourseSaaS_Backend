@extends('layouts.auth')

@section('title', 'تسجيل شركة جديدة')

@section('content')
    <div class="mx-auto max-w-xl">
        <div class="mb-7 text-center">
            <span class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-900/50 dark:text-emerald-300 dark:ring-emerald-800">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 20V7.5L12 4l8 3.5V20M2.5 20h19M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01M10 20v-3h4v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">ابدأ مساحة عمل شركتك</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">أنشئ حسابك وابدأ إدارة أعمالك خلال دقائق.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                <div class="mb-2 flex items-center gap-2 font-semibold">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 8v4m0 4h.01M10.3 3.9 2.8 17a2 2 0 0 0 1.74 3h14.92a2 2 0 0 0 1.74-3l-7.46-13.1a2 2 0 0 0-3.48 0Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    يرجى مراجعة البيانات التالية:
                </div>
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="company_name" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">اسم الشركة</label>
                <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" required maxlength="255" autocomplete="organization" placeholder="مثال: شركة الأفق للتجارة" @class([
                    'block w-full rounded-xl border bg-white/80 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500',
                    'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('company_name'),
                    'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100' => ! $errors->has('company_name'),
                ])>
                @error('company_name')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">اسم المسؤول</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255" autocomplete="name" placeholder="الاسم الكامل" @class([
                        'block w-full rounded-xl border bg-white/80 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500',
                        'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('name'),
                        'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100' => ! $errors->has('name'),
                    ])>
                    @error('name')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">البريد الإلكتروني</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="name@company.com" @class([
                        'block w-full rounded-xl border bg-white/80 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500',
                        'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('email'),
                        'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100' => ! $errors->has('email'),
                    ])>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">كلمة المرور</label>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" placeholder="8 أحرف على الأقل" @class([
                        'block w-full rounded-xl border bg-white/80 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500',
                        'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('password'),
                        'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100' => ! $errors->has('password'),
                    ])>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">تأكيد كلمة المرور</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" placeholder="أعد كتابة كلمة المرور" class="block w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:ring-emerald-900">
                </div>
            </div>

            <button type="submit" class="group flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                إنشاء حساب الشركة
                <svg class="h-4 w-4 transition group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M19 12H5m0 0 7-7m-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-slate-500 dark:text-slate-400">
            لديك حساب بالفعل؟
            <a href="{{ route('login') }}" class="font-bold text-emerald-700 underline-offset-4 transition hover:text-emerald-800 hover:underline">تسجيل الدخول</a>
        </p>
    </div>
@endsection
