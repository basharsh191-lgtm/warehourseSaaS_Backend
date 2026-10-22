@extends('admin.layout')

@section('title', 'تعديل الشركة')

@section('content')
    <header class="mb-6">
        <a href="{{ route('admin.companies.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">العودة إلى الشركات</a>
        <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">تعديل بيانات الشركة</h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $company->name }}</p>
        @php
            $trialDaysRemaining = $company->trial_ends_at
                ? today()->diffInDays($company->trial_ends_at, false)
                : null;
        @endphp
        <div class="mt-4">
            @if ($trialDaysRemaining === null)
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">لا يوجد تاريخ انتهاء للتجربة</span>
            @elseif ($trialDaysRemaining < 0)
                <span class="inline-flex rounded-full bg-rose-100 px-3 py-1.5 text-sm font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-300">انتهت التجربة</span>
            @elseif ($trialDaysRemaining == 0)
                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1.5 text-sm font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-300">تنتهي التجربة اليوم</span>
            @else
                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1.5 text-sm font-semibold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">متبقي {{ $trialDaysRemaining }} {{ $trialDaysRemaining === 1 ? 'يوم' : 'أيام' }}</span>
            @endif
        </div>
    </header>

    <section class="app-card mb-5 flex flex-col gap-4 rounded-2xl border p-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="font-bold text-slate-900 dark:text-white">تمديد الفترة التجريبية</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">تُضاف الأيام إلى تاريخ الانتهاء الحالي، أو من اليوم إذا انتهت الفترة.</p>
        </div>
        <form method="POST" action="{{ route('admin.companies.extend-trial', $company) }}" x-data="{ days: 14 }" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf
            <div>
                <label for="extension_days" class="mb-2 block text-sm font-semibold">عدد الأيام</label>
                <input id="extension_days" name="days" type="number" min="1" max="3650" required x-model.number="days" value="{{ old('days', 14) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
            </div>
            <button type="submit" class="btn-primary rounded-xl px-5 py-2.5 text-sm font-semibold">تمديد التجربة <span x-text="days">14</span> يوماً إضافياً</button>
        </form>
    </section>

    <form method="POST" action="{{ route('admin.companies.update', $company) }}" class="app-card max-w-3xl space-y-6 rounded-2xl border p-5 sm:p-7">
        @csrf
        @method('PUT')

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="mb-2 block text-sm font-semibold">اسم الشركة</label>
                <input id="name" name="name" type="text" required maxlength="255" value="{{ old('name', $company->name) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
            </div>
            <div>
                <label for="email" class="mb-2 block text-sm font-semibold">البريد الإلكتروني</label>
                <input id="email" name="email" type="email" maxlength="255" value="{{ old('email', $company->email) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
            </div>
            <div>
                <label for="phone" class="mb-2 block text-sm font-semibold">الهاتف</label>
                <input id="phone" name="phone" type="text" maxlength="50" value="{{ old('phone', $company->phone) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
            </div>
            <div class="sm:col-span-2">
                <label for="address" class="mb-2 block text-sm font-semibold">العنوان</label>
                <input id="address" name="address" type="text" maxlength="255" value="{{ old('address', $company->address) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
            </div>
            <div>
                <label for="status" class="mb-2 block text-sm font-semibold">حالة الاشتراك</label>
                <select id="status" name="status" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
                    <option value="trial" @selected(old('status', $company->status) === 'trial')>تجريبية</option>
                    <option value="active" @selected(old('status', $company->status) === 'active')>نشطة</option>
                    <option value="suspended" @selected(old('status', $company->status) === 'suspended')>موقوفة</option>
                </select>
            </div>
            <div>
                <label for="trial_ends_at" class="mb-2 block text-sm font-semibold">تاريخ انتهاء التجربة</label>
                <input id="trial_ends_at" name="trial_ends_at" type="date" value="{{ old('trial_ends_at', $company->trial_ends_at?->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">إذا كان التاريخ قبل اليوم فستُحوّل حالة الشركة تلقائياً إلى موقوفة.</p>
            </div>
            <div>
                <label for="percent_markup" class="mb-2 block text-sm font-semibold">نسبة الزيادة السعرية (%)</label>
                <input id="percent_markup" name="percent_markup" type="number" min="0" max="9999.99" step="0.01" required value="{{ old('percent_markup', $company->percent_markup) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
            <a href="{{ route('admin.companies.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-center text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">إلغاء</a>
            <button type="submit" class="btn-primary rounded-xl px-5 py-2.5 text-sm font-semibold">حفظ التغييرات</button>
        </div>
    </form>
@endsection
