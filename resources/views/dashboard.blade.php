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
                class="accent-icon flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl"
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

    @if (! $isSuperAdmin && $company?->status === 'trial')
        <section class="app-card mt-6 rounded-2xl border p-5 sm:p-6" aria-labelledby="trial-period-heading">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-amber-700 dark:text-amber-300">الفترة التجريبية</p>
                    <h2 id="trial-period-heading" class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                        مدة التجربة {{ $trialDurationDays }} يوماً
                    </h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        @if ($company->trial_ends_at)
                            تاريخ انتهاء التجربة: {{ $company->trial_ends_at->format('Y-m-d') }}
                        @else
                            لم يتم تحديد تاريخ انتهاء التجربة.
                        @endif
                    </p>
                </div>

                @if ($trialDaysRemaining === null)
                    <span class="inline-flex w-fit rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        لا يوجد تاريخ انتهاء
                    </span>
                @elseif ($trialDaysRemaining < 0)
                    <span class="inline-flex w-fit rounded-full bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                        انتهت الفترة التجريبية
                    </span>
                @elseif ($trialDaysRemaining == 0)
                    <span class="inline-flex w-fit rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                        تنتهي التجربة اليوم
                    </span>
                @else
                    <span class="inline-flex w-fit rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                        متبقي {{ $trialDaysRemaining }} {{ $trialDaysRemaining === 1 ? 'يوم' : 'أيام' }}
                    </span>
                @endif
            </div>
        </section>
    @endif


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
                    class="accent-icon flex h-11 w-11 items-center justify-center rounded-xl"
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
                    class="accent-icon flex h-11 w-11 items-center justify-center rounded-xl"
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
