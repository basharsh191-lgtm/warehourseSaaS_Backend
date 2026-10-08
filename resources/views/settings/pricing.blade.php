@extends('layouts.app')

@section('title', 'إعدادات التسعير')

@section('content')
    <div class="mx-auto w-full max-w-[800px] px-4 py-8 sm:px-6">
        <header class="mb-6">
            <p class="text-xs font-semibold tracking-wide text-emerald-700 dark:text-emerald-400">إعدادات الشركة</p>
            <h1 class="mt-2 text-3xl font-bold leading-tight text-slate-900 dark:text-white">إعدادات التسعير</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">حدّد نسبة الزيادة الافتراضية لأسعار البيع.</p>
        </header>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                <h2 class="font-bold text-slate-900 dark:text-white">نسبة هامش الربح</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">تُطبّق هذه النسبة على المنتجات التي لا تملك سعر بيع يدوي عند احتساب سعر البيع.</p>
            </div>
            <form
                method="POST"
                action="{{ route('settings.pricing.update') }}"
                x-data="{ saving: false, savedValue: @js((string) ($company->percent_markup ?? 200)), currentValue: @js((string) old('percent_markup', $company->percent_markup ?? 200)), get isDirty() { return this.currentValue !== this.savedValue; } }"
                @submit="saving = true"
                class="space-y-6 p-6"
            >
                @csrf
                @method('PUT')
                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">نسبة الزيادة (%)</span>
                    <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 transition focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:focus-within:ring-emerald-900/40">
                        <input
                            type="number"
                            name="percent_markup"
                            value="{{ old('percent_markup', $company->percent_markup ?? 200) }}"
                            x-model="currentValue"
                            min="0"
                            max="9999.99"
                            step="0.01"
                            required
                            inputmode="decimal"
                            dir="ltr"
                            aria-label="نسبة الزيادة المئوية"
                            class="min-w-0 flex-1 border-0 bg-transparent p-0 text-start text-lg text-slate-900 outline-none focus:ring-0 dark:text-white"
                        >
                        <span class="text-base font-semibold text-slate-500 dark:text-slate-300">%</span>
                    </div>
                    <span class="mt-2 block text-sm leading-5 text-slate-600 dark:text-slate-300">تُستخدم هذه النسبة عند تسعير منتج للبيع دون سعر بيع مخصص.</span>
                </label>
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 dark:border-slate-800 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        @click="currentValue = savedValue"
                        :disabled="saving || !isDirty"
                        class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus-visible:ring-offset-slate-900 sm:w-auto"
                    >
                        إلغاء التغييرات
                    </button>
                    <button
                        type="submit"
                        :disabled="saving || !isDirty"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus-visible:ring-offset-slate-900 sm:w-auto"
                    >
                        <svg x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="opacity-75"/></svg>
                        <span x-text="saving ? 'جارٍ الحفظ…' : 'حفظ الإعدادات'"></span>
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
