<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="rtl"
    data-theme="default"
>
<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="theme-color"
        content="#312e81"
    >

    <title>
        @yield('title') - {{ config('app.name', 'Warehouse SaaS') }}
    </title>

    {{-- الثيم --}}
    <script>
        (() => {
            const accentThemes = ['default', 'green', 'red', 'yellow'];
            const savedAccentTheme = localStorage.getItem('warehouse-accent-theme');

            if (accentThemes.includes(savedAccentTheme)) {
                document.documentElement.dataset.theme = savedAccentTheme;
            }

            const savedTheme = localStorage.getItem('warehouse-theme');

            const prefersDark =
                window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (
                savedTheme === 'dark' ||
                (!savedTheme && prefersDark)
            ) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased dark:bg-[#0b1220] dark:text-slate-100">
    <div
        x-data="{ mobileNavOpen: false }"
        @keydown.escape.window="mobileNavOpen = false"
        class="flex min-h-screen w-full flex-row bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-emerald-100/55 via-slate-100 to-slate-100 dark:from-emerald-950/20 dark:via-[#0b1220] dark:to-[#0b1220]"
    >
        @include('layouts.navigation')

        <button
            x-cloak
            x-show="mobileNavOpen"
            type="button"
            class="fixed inset-0 z-40 cursor-default bg-slate-950/45 backdrop-blur-[2px] lg:hidden"
            aria-label="إغلاق قائمة التنقل"
            @click="mobileNavOpen = false"
        ></button>

        <main class="min-w-0 flex-1 px-4 pb-8 pt-4 sm:px-6 sm:pt-6 lg:px-8 lg:pt-8">
            <div class="mx-auto w-full max-w-7xl">
                <div class="mb-5 flex items-center justify-between lg:hidden">
                    <button
                        type="button"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-emerald-300 hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-emerald-700 dark:hover:text-emerald-300"
                        aria-label="فتح قائمة التنقل"
                        :aria-expanded="mobileNavOpen.toString()"
                        @click="mobileNavOpen = true"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <span class="flex min-w-0 items-center gap-2">
                        @if (auth()->user()?->company?->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists(auth()->user()->company->logo_path))
                            <img src="{{ asset('storage/'.ltrim(auth()->user()->company->logo_path, '/')) }}" alt="" class="h-8 w-8 shrink-0 rounded-lg border border-slate-200 bg-white object-cover dark:border-slate-700 dark:bg-slate-800">
                        @endif
                        <span class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">
                            {{ auth()->user()?->company?->name ?? config('app.name', 'Warehouse SaaS') }}
                        </span>
                    </span>
                </div>

                @if (session('success'))
                    <div
                        class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm font-medium text-emerald-900 shadow-sm dark:border-emerald-900/70 dark:bg-emerald-950/45 dark:text-emerald-200"
                        role="status"
                    >
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white" aria-hidden="true">✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>
