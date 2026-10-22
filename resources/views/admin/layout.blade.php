<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" data-theme="default">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#312e81">
    <title>@yield('title') - {{ config('app.name', 'Warehouse SaaS') }}</title>
    <script>
        (() => {
            const accentThemes = ['default', 'green', 'red', 'yellow'];
            const savedAccentTheme = localStorage.getItem('warehouse-accent-theme');

            if (accentThemes.includes(savedAccentTheme)) {
                document.documentElement.dataset.theme = savedAccentTheme;
            }

            const savedTheme = localStorage.getItem('warehouse-theme');

            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased dark:bg-[#0b1220] dark:text-slate-100">
    <div x-data="{ mobileNavOpen: false }" @keydown.escape.window="mobileNavOpen = false" class="flex min-h-screen">
        <aside
            :class="{ 'is-open': mobileNavOpen }"
            class="app-sidebar fixed inset-y-0 right-0 z-50 flex h-screen w-[min(88vw,288px)] flex-col border-l border-slate-200 bg-white px-4 py-5 shadow-2xl transition-transform lg:sticky lg:w-64 lg:translate-x-0 lg:shadow-none dark:border-slate-800 dark:bg-slate-900"
        >
            <a href="{{ route('admin.dashboard') }}" class="mb-6 border-b border-slate-200 px-2 pb-5 dark:border-slate-800">
                <span class="block text-sm font-bold text-slate-900 dark:text-white">{{ config('app.name', 'Warehouse SaaS') }}</span>
                <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">إدارة المنصة</span>
            </a>

            <nav aria-label="قائمة إدارة المنصة" class="flex flex-col gap-2">
                <a href="{{ route('admin.dashboard') }}" @class([
                    'rounded-xl px-4 py-3 text-sm font-semibold transition',
                    'is-active text-white' => request()->routeIs('admin.dashboard'),
                    'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! request()->routeIs('admin.dashboard'),
                ])>لوحة الإحصائيات</a>
                <a href="{{ route('admin.companies.index') }}" @class([
                    'rounded-xl px-4 py-3 text-sm font-semibold transition',
                    'is-active text-white' => request()->routeIs('admin.companies.*'),
                    'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! request()->routeIs('admin.companies.*'),
                ])>إدارة الشركات</a>
                <a href="{{ route('admin.users.index') }}" @class([
                    'rounded-xl px-4 py-3 text-sm font-semibold transition',
                    'is-active text-white' => request()->routeIs('admin.users.*'),
                    'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! request()->routeIs('admin.users.*'),
                ])>إدارة المستخدمين</a>
            </nav>

            <div class="mt-auto border-t border-slate-200 pt-4 dark:border-slate-800">
                <button type="button" data-theme-toggle aria-label="تفعيل الوضع الداكن" aria-pressed="false" class="mb-3 flex w-full items-center justify-between rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <span data-theme-label>الوضع الداكن</span>
                    <span class="flex items-center gap-2">
                        <svg data-theme-icon="moon" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.2 15.1A8.5 8.5 0 0 1 8.9 3.8 8.5 8.5 0 1 0 20.2 15.1Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <svg data-theme-icon="sun" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-start text-sm font-semibold text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300">تسجيل الخروج</button>
                </form>
            </div>
        </aside>

        <button x-cloak x-show="mobileNavOpen" type="button" class="fixed inset-0 z-40 bg-slate-950/45 lg:hidden" aria-label="إغلاق القائمة" @click="mobileNavOpen = false"></button>

        <main class="min-w-0 flex-1 px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto w-full max-w-7xl">
                <div class="mb-5 flex items-center justify-between lg:hidden">
                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold dark:border-slate-700 dark:bg-slate-900" :aria-expanded="mobileNavOpen.toString()" @click="mobileNavOpen = true">القائمة</button>
                    <span class="text-sm font-bold">{{ auth()->user()->name }}</span>
                </div>

                @if (session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200" role="status">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-200" role="alert">
                        <p class="font-semibold">يرجى مراجعة البيانات المدخلة:</p>
                        <ul class="mt-2 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
