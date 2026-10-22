@extends('layouts.app')

@section('title', 'الإعدادات')

@section('content')
    <div class="mx-auto w-full max-w-4xl py-2 sm:py-4">
        <header class="mb-7">
            <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">إعدادات مساحة العمل</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">الإعدادات</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">أدر بيانات شركتك وإعدادات تسعير المنتجات من مكان واحد.</p>
        </header>

        <section class="app-card mb-5 rounded-2xl border p-6" aria-labelledby="accent-theme-heading">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 id="accent-theme-heading" class="font-bold text-slate-900 dark:text-white">اللون الأساسي للنظام</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">اختر لون الأزرار والعناصر النشطة في مساحة العمل.</p>
                </div>
                <div class="flex items-center gap-3" role="group" aria-label="اختر اللون الأساسي">
                    <button type="button" class="accent-color-swatch h-9 w-9 rounded-full bg-[#5D5FEF]" data-accent-theme="default" aria-label="الأزرق البنفسجي" title="الأزرق البنفسجي" aria-pressed="false"></button>
                    <button type="button" class="accent-color-swatch h-9 w-9 rounded-full bg-[#10B981]" data-accent-theme="green" aria-label="الأخضر" title="الأخضر" aria-pressed="false"></button>
                    <button type="button" class="accent-color-swatch h-9 w-9 rounded-full bg-[#EF4444]" data-accent-theme="red" aria-label="الأحمر" title="الأحمر" aria-pressed="false"></button>
                    <button type="button" class="accent-color-swatch h-9 w-9 rounded-full bg-[#F59E0B]" data-accent-theme="yellow" aria-label="الأصفر" title="الأصفر" aria-pressed="false"></button>
                </div>
            </div>
        </section>

        <div x-data="{ tab: @js($activeTab) }" class="space-y-5">
            <div class="flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white/80 p-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900/80" role="tablist" aria-label="أقسام الإعدادات">
                <button
                    type="button"
                    role="tab"
                    id="pricing-tab"
                    aria-controls="pricing-panel"
                    :aria-selected="tab === 'pricing'"
                    @click="tab = 'pricing'"
                    :class="tab === 'pricing' ? 'active text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
                    class="tab-button inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2v20m5-16H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    إعدادات التسعير
                </button>
                <button
                    type="button"
                    role="tab"
                    id="company-tab"
                    aria-controls="company-panel"
                    :aria-selected="tab === 'company'"
                    @click="tab = 'company'"
                    :class="tab === 'company' ? 'active text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
                    class="tab-button inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01M10 21v-4h4v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    بيانات الشركة
                </button>
            </div>

            @if ($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" role="alert">
                    <p class="mb-1 font-bold">يرجى مراجعة البيانات التالية:</p>
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section
                x-cloak
                x-show="tab === 'pricing'"
                id="pricing-panel"
                role="tabpanel"
                aria-labelledby="pricing-tab"
                class="app-card overflow-hidden rounded-2xl border"
            >
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <h2 class="font-bold text-slate-900 dark:text-white">نسبة هامش الربح</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">تُطبّق هذه النسبة على المنتجات التي لا تملك سعر بيع يدويًا.</p>
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
                    <label class="block max-w-xl">
                        <span class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">نسبة الزيادة (%)</span>
                        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 transition focus-within:border-indigo-500 focus-within:ring-4 focus-within:ring-indigo-100 dark:border-slate-700 dark:bg-slate-950 dark:focus-within:ring-indigo-900/40">
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
                        <span class="mt-2 block text-sm leading-5 text-slate-600 dark:text-slate-300">تُستخدم عند تسعير منتج للبيع دون سعر بيع مخصص.</span>
                    </label>
                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 dark:border-slate-800 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="currentValue = savedValue"
                            :disabled="saving || !isDirty"
                            class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus-visible:ring-offset-slate-900 sm:w-auto"
                        >
                            إلغاء التغييرات
                        </button>
                        <button
                            type="submit"
                            :disabled="saving || !isDirty"
                            class="btn-primary inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold shadow-lg shadow-indigo-900/15 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus-visible:ring-offset-slate-900 sm:w-auto"
                        >
                            <svg x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="opacity-75"/></svg>
                            <span x-text="saving ? 'جارٍ الحفظ…' : 'حفظ إعدادات التسعير'"></span>
                        </button>
                    </div>
                </form>
            </section>

            <section
                x-cloak
                x-show="tab === 'company'"
                id="company-panel"
                role="tabpanel"
                aria-labelledby="company-tab"
                class="app-card overflow-hidden rounded-2xl border"
            >
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <h2 class="font-bold text-slate-900 dark:text-white">بيانات الشركة</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">حدّث اسم الشركة ومعلومات التواصل والشعار الذي يظهر في القائمة الجانبية.</p>
                </div>
                <form
                    method="POST"
                    action="{{ route('settings.company.update', ['tab' => 'company']) }}"
                    enctype="multipart/form-data"
                    class="space-y-6 p-6"
                >
                    @csrf
                    @method('PUT')

                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">اسم الشركة <span class="text-rose-500">*</span></span>
                            <input type="text" name="name" value="{{ old('name', $company->name) }}" required maxlength="255" autocomplete="organization" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-indigo-900/40">
                            @error('name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">رقم الهاتف</span>
                            <input type="tel" name="phone" value="{{ old('phone', $company->phone) }}" maxlength="50" autocomplete="tel" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-indigo-900/40">
                            @error('phone')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">البريد الإلكتروني</span>
                            <input type="email" name="email" value="{{ old('email', $company->email) }}" maxlength="255" autocomplete="email" dir="ltr" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-left text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-indigo-900/40">
                            @error('email')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">العنوان</span>
                            <textarea name="address" rows="3" maxlength="1000" autocomplete="street-address" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-indigo-900/40">{{ old('address', $company->address) }}</textarea>
                            @error('address')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-950/50">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            @if ($company->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($company->logo_path))
                                <img src="{{ asset('storage/'.ltrim($company->logo_path, '/')) }}" alt="الشعار الحالي للشركة" class="h-16 w-16 rounded-xl border border-slate-200 bg-white object-cover dark:border-slate-700 dark:bg-slate-900">
                            @else
                                <span class="accent-icon flex h-16 w-16 items-center justify-center rounded-xl">
                                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 8.5 12 4l8 4.5v8L12 21l-8-4.5v-8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m8 10.75 4 2.25 4-2.25M12 13v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            @endif
                            <label class="block min-w-0 flex-1">
                                <span class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">شعار الشركة</span>
                                <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-slate-600 file:me-3 file:rounded-lg file:border-0 file:bg-indigo-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-800 hover:file:bg-indigo-200 dark:text-slate-300 dark:file:bg-indigo-950 dark:file:text-indigo-200 dark:hover:file:bg-indigo-900">
                                <span class="mt-1.5 block text-xs text-slate-500 dark:text-slate-400">PNG أو JPG أو WEBP، بحد أقصى 2 ميجابايت.</span>
                                @error('logo')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 pt-5 dark:border-slate-800">
                        <button type="submit" class="btn-primary inline-flex w-full items-center justify-center rounded-xl px-5 py-3 text-sm font-bold shadow-lg shadow-indigo-900/15 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900 sm:w-auto">حفظ بيانات الشركة</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
