<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f0fdf4">
    <title>@yield('title') - {{ config('app.name', 'Warehouse SaaS') }}</title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('warehouse-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-green-100 font-sans text-slate-800 antialiased dark:from-slate-950 dark:via-slate-950 dark:to-emerald-950">
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-emerald-200/40 blur-3xl dark:bg-emerald-900/20"></div>
        <div class="absolute -bottom-40 -left-24 h-96 w-96 rounded-full bg-green-200/40 blur-3xl dark:bg-green-900/20"></div>
    </div>

    <main class="relative flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:px-6">
        <div class="mb-7 flex w-full max-w-2xl items-center justify-between gap-3">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-3 rounded-full border border-white/80 bg-white/70 px-4 py-2 shadow-sm backdrop-blur transition hover:bg-white dark:border-slate-700 dark:bg-slate-900/70 dark:hover:bg-slate-800">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm shadow-emerald-900/15">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 8.5 12 4l8 4.5v8L12 21l-8-4.5v-8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="m8 10.75 4 2.25 4-2.25M12 13v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="text-right">
                    <span class="block text-sm font-bold tracking-tight text-slate-900 dark:text-white">{{ config('app.name', 'Warehouse SaaS') }}</span>
                    <span class="block text-xs text-slate-500 dark:text-slate-400">إدارة أسهل لأعمالك</span>
                </span>
            </a>

            <button
                type="button"
                data-theme-toggle
                aria-label="تفعيل الوضع الداكن"
                aria-pressed="false"
                class="inline-flex h-11 items-center justify-center gap-2 rounded-full border border-white/80 bg-white/70 px-3 text-sm font-semibold text-slate-700 shadow-sm backdrop-blur transition hover:border-emerald-300 hover:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200 dark:hover:border-emerald-700 dark:hover:bg-slate-800 dark:focus:ring-emerald-900/50"
            >
                <svg data-theme-icon="moon" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M20.2 15.1A8.5 8.5 0 0 1 8.9 3.8 8.5 8.5 0 1 0 20.2 15.1Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <svg data-theme-icon="sun" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <span class="hidden sm:inline" data-theme-label>الوضع الداكن</span>
            </button>
        </div>

        <section class="w-full max-w-2xl rounded-3xl border border-white/80 bg-white/85 p-6 shadow-xl shadow-emerald-950/5 backdrop-blur-xl sm:p-9 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
            @yield('content')
        </section>

        <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">مساحة عملك الآمنة لإدارة المخزون والأعمال</p>
    </main>
</body>
</html>
