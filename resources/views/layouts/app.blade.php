<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="rtl"
>
<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="theme-color"
        content="#020617"
    >

    <title>
        @yield('title') - {{ config('app.name', 'Warehouse SaaS') }}
    </title>

    {{-- الثيم --}}
    <script>
        (() => {
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

<body
    class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100"
>

    {{-- الصفحة بالكامل --}}
    <div
        class="flex min-h-screen w-full flex-row bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-emerald-100/60 via-slate-100 to-slate-100 dark:from-emerald-950/30 dark:via-slate-950 dark:to-slate-950"
    >

        {{-- ========================= --}}
        {{-- Sidebar --}}
        {{-- ========================= --}}

        @include('layouts.navigation')


        {{-- ========================= --}}
        {{-- المحتوى الرئيسي --}}
        {{-- ========================= --}}

        <main class="min-w-0 flex-1 px-4 py-8 sm:px-6 lg:px-8">

            <div class="mx-auto w-full max-w-7xl">

                {{-- رسالة نجاح --}}
                @if (session('success'))

                    <div
                        class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-300"
                        role="status"
                    >
                        {{ session('success') }}
                    </div>

                @endif


                {{-- محتوى الصفحة --}}
                @yield('content')

            </div>

        </main>

    </div>

</body>
</html>
