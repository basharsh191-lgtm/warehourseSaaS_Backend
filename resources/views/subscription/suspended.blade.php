@extends('layouts.auth')

@section('title', 'الحساب موقوف')

@section('content')
    <div class="mx-auto max-w-md text-center">
        <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-700 ring-1 ring-amber-100 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 9v4m0 4h.01M10.3 3.9 2.8 17a2 2 0 0 0 1.74 3h14.92a2 2 0 0 0 1.74-3l-7.46-13.1a2 2 0 0 0-3.48 0Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">الاشتراك موقوف</h1>
        <p class="mt-3 leading-7 text-slate-600 dark:text-slate-300">
            {{ session('error', 'حساب الشركة موقوف، يرجى التواصل مع الدعم') }}
        </p>
        <form method="POST" action="{{ route('logout') }}" class="mt-7">
            @csrf
            <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                تسجيل الخروج
            </button>
        </form>
    </div>
@endsection
