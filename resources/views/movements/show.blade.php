@extends('layouts.app')

@section('title', 'تفاصيل الفاتورة')

@section('content')
    @php
        $typeLabels = ['sale' => 'بيع', 'purchase' => 'شراء', 'purchase_return' => 'مرتجع شراء', 'sale_return' => 'مرتجع بيع'];
    @endphp
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">تفاصيل الحركة</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">فاتورة رقم #{{ $movement->number }}</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $typeLabels[$movement->type] ?? $movement->type }} · {{ $movement->date?->format('Y-m-d') ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('movements.pdf', $movement) }}" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">تصدير PDF</a>
            @if ($movement->status === 'draft')
                <form method="POST" action="{{ route('movements.confirm', $movement) }}">
                    @csrf
                    <button class="rounded-xl border border-emerald-200 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-900 dark:bg-slate-900 dark:text-emerald-300 dark:hover:bg-emerald-950">تأكيد المسودة</button>
                </form>
            @endif
            <a href="{{ route('movements.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">العودة إلى الفواتير</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" role="alert">
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><p class="text-xs font-semibold text-slate-500 dark:text-slate-400">الطرف</p><p class="mt-2 font-bold text-slate-900 dark:text-white">{{ $movement->party?->name ?? 'نقدي مباشر' }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><p class="text-xs font-semibold text-slate-500 dark:text-slate-400">رقم المستند</p><p class="mt-2 font-bold text-slate-900 dark:text-white">{{ $movement->document_no ?: '—' }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><p class="text-xs font-semibold text-slate-500 dark:text-slate-400">الحالة</p><p class="mt-2 font-bold text-emerald-700 dark:text-emerald-400">{{ ['confirmed' => 'مؤكدة', 'draft' => 'مسودة', 'cancelled' => 'ملغاة'][$movement->status] ?? $movement->status }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"><p class="text-xs font-semibold text-slate-500 dark:text-slate-400">الإجمالي الصافي</p><p class="mt-2 text-xl font-extrabold tabular-nums text-slate-900 dark:text-white">{{ number_format((float) $movement->total, 2) }}</p></div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"><h2 class="font-bold text-slate-900 dark:text-white">مواد الفاتورة</h2></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950/60"><tr class="text-right text-xs font-semibold text-slate-500 dark:text-slate-400"><th class="px-5 py-3">المادة</th><th class="px-5 py-3">الكمية</th><th class="px-5 py-3">سعر الوحدة</th><th class="px-5 py-3">الإجمالي</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($movement->items as $item)
                        <tr class="text-sm"><td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $item->product?->name ?? 'مادة محذوفة' }}</td><td class="px-5 py-4 tabular-nums text-slate-600 dark:text-slate-300">{{ number_format((float) $item->quantity, 3) }}</td><td class="px-5 py-4 tabular-nums text-slate-600 dark:text-slate-300">{{ number_format((float) $item->price_unit, 2) }}</td><td class="px-5 py-4 font-semibold tabular-nums text-slate-900 dark:text-slate-100">{{ number_format((float) $item->total_line, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="ml-auto max-w-sm space-y-3 border-t border-slate-200 p-5 dark:border-slate-800">
            <div class="flex justify-between text-sm text-slate-500 dark:text-slate-400"><span>الإجمالي الفرعي</span><span class="tabular-nums">{{ number_format($movement->items->sum(fn ($item) => (float) $item->total_line), 2) }}</span></div>
            <div class="flex justify-between text-sm text-slate-500 dark:text-slate-400"><span>الخصم</span><span class="tabular-nums">{{ number_format((float) $movement->discount, 2) }}</span></div>
            <div class="flex justify-between border-t border-slate-200 pt-3 font-bold text-slate-900 dark:border-slate-700 dark:text-white"><span>الصافي المستحق</span><span class="tabular-nums">{{ number_format((float) $movement->total, 2) }}</span></div>
        </div>
    </section>
@endsection
