@extends('layouts.app')

@section('title', $isSuperAdmin ? 'لوحة مدير المنصة' : 'لوحة الشركة')

@section('content')

    {{-- ========================= --}}
    {{-- رأس لوحة التحكم --}}
    {{-- ========================= --}}

    <section
        class="overflow-hidden rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl shadow-slate-900/5 backdrop-blur sm:p-10 dark:border-slate-800 dark:bg-slate-900/80 dark:shadow-black/20"
    >

        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

            {{-- النص --}}
            <div>

                <p class="mb-2 text-sm font-semibold text-emerald-700 dark:text-emerald-400">
                    {{ $isSuperAdmin ? 'لوحة الإدارة' : 'مساحة العمل' }}
                </p>

                <h1
                    class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                >
                    {{ $isSuperAdmin ? 'لوحة مدير المنصة' : 'لوحة الشركة' }}
                </h1>

                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    مرحباً {{ auth()->user()->name }}، يسعدنا وجودك مجدداً.
                </p>

            </div>


            {{-- الأيقونة --}}
            <div
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300"
            >
                <svg
                    class="h-7 w-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M3 20h18M5 20V8l7-4 7 4v12M9 10h.01M15 10h.01M9 14h.01M15 14h.01M10 20v-3h4v3"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>

        </div>

    </section>


    {{-- ========================= --}}
    {{-- بطاقات الشركة --}}
    {{-- ========================= --}}

    @unless ($isSuperAdmin)

        <section class="mt-6 grid gap-4 sm:grid-cols-2">

            {{-- الفئات --}}
            <a
                href="{{ route('categories.index') }}"
                class="group rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900/80 dark:hover:border-emerald-800"
            >

                <span
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300"
                >
                    <svg
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M4 5h7v6H4zM13 5h7v6h-7zM4 13h7v6H4zM13 13h7v6h-7z"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linejoin="round"
                        />
                    </svg>
                </span>

                <h2
                    class="mt-4 font-bold text-slate-900 group-hover:text-emerald-700 dark:text-white dark:group-hover:text-emerald-300"
                >
                    إدارة الفئات
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    أضف الفئات ونظّم المواد حسب نوعها.
                </p>

            </a>


            {{-- المواد --}}
            <a
                href="{{ route('products.index') }}"
                class="group rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900/80 dark:hover:border-emerald-800"
            >

                <span
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300"
                >
                    <svg
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="m12 3 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 16l9 5 9-5"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </span>

                <h2
                    class="mt-4 font-bold text-slate-900 group-hover:text-emerald-700 dark:text-white dark:group-hover:text-emerald-300"
                >
                    إدارة المواد
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    تابع الباركود والوحدات ومواقع التخزين.
                </p>

            </a>

        </section>

    @endunless

@endsection
