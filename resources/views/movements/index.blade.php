@extends('layouts.app')

@section('title', 'الفواتير والحركات')

@section('content')
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">سجل المخزون</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">الفواتير والحركات</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">راجع فواتير الشراء والبيع والمرتجعات.</p>
        </div>
        <a href="{{ route('movements.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            إنشاء فاتورة
        </a>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-200 p-5 dark:border-slate-800">
            <form method="GET" action="{{ route('movements.index') }}" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_190px_180px_auto]">
                <label><span class="sr-only">رقم الحركة أو المستند</span><input type="search" name="search" value="{{ request('search') }}" placeholder="رقم الحركة أو المستند" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/40"></label>
                <select name="type" aria-label="تصفية حسب النوع" class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                    <option value="">كل أنواع الحركات</option>
                    <option value="purchase" @selected(request('type') === 'purchase')>شراء</option>
                    <option value="sale" @selected(request('type') === 'sale')>بيع</option>
                    <option value="purchase_return" @selected(request('type') === 'purchase_return')>مرتجع شراء</option>
                    <option value="sale_return" @selected(request('type') === 'sale_return')>مرتجع بيع</option>
                </select>
                <input type="date" name="date" value="{{ request('date') }}" aria-label="تصفية حسب التاريخ" class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                <button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">تصفية</button>
            </form>
        </div>
        <div class="flex items-center justify-between px-5 py-4">
            <h2 class="font-bold text-slate-900 dark:text-white">سجل الفواتير</h2>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $movements->total() }} حركة</span>
        </div>

        @if ($movements->isEmpty())
            <div class="px-6 py-14 text-center">
                <p class="font-semibold text-slate-700 dark:text-slate-200">لا توجد حركات بعد</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">ستظهر الفواتير هنا بعد تسجيلها.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/60">
                        <tr class="text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <th class="px-5 py-3">رقم الحركة</th><th class="px-5 py-3">النوع</th><th class="px-5 py-3">التاريخ</th><th class="px-5 py-3">الطرف</th><th class="px-5 py-3">الإجمالي الصافي</th><th class="px-5 py-3">الحالة</th><th class="px-5 py-3 text-left">التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($movements as $movement)
                            @php
                                $typeLabels = ['sale' => 'بيع', 'purchase' => 'شراء', 'purchase_return' => 'مرتجع شراء', 'sale_return' => 'مرتجع بيع'];
                            @endphp
                            <tr class="text-sm transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="whitespace-nowrap px-5 py-4 font-bold tabular-nums text-slate-900 dark:text-slate-100">#{{ $movement->number }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' => $movement->type === 'sale',
                                        'bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300' => $movement->type === 'purchase',
                                        'bg-orange-50 text-orange-700 dark:bg-orange-950 dark:text-orange-300' => $movement->type === 'purchase_return',
                                        'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300' => $movement->type === 'sale_return',
                                    ])>{{ $typeLabels[$movement->type] ?? $movement->type }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-600 dark:text-slate-300">{{ $movement->date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-600 dark:text-slate-300">{{ $movement->party?->name ?? 'نقدي مباشر' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 font-semibold tabular-nums text-slate-900 dark:text-slate-100">{{ number_format((float) $movement->total, 2) }}</td>
                                <td class="whitespace-nowrap px-5 py-4"><span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' => $movement->status === 'confirmed', 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300' => $movement->status === 'cancelled'])>{{ $movement->status === 'confirmed' ? 'مؤكدة' : 'ملغاة' }}</span></td>
                                <td class="whitespace-nowrap px-5 py-4 text-left"><a href="{{ route('movements.show', $movement) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700 dark:border-slate-700 dark:text-slate-200 dark:hover:border-emerald-700 dark:hover:text-emerald-300">عرض الفاتورة</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($movements->hasPages())<div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $movements->links() }}</div>@endif
        @endif
    </section>
@endsection
