@extends('layouts.app')

@section('title', $returnType === 'sale_return' ? 'مرتجع بيع' : 'مرتجع شراء')

@section('content')
    @php
        $returnTitle = $returnType === 'sale_return' ? 'مرتجع بيع' : 'مرتجع شراء';
    @endphp
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">مرتبط بالفاتورة الأصلية #{{ $original->number }}</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">إنشاء {{ $returnTitle }}</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">أدخل الكميات المرتجعة؛ لن تتجاوز الكمية المتبقية من الفاتورة الأصلية، وسيُحتسب الخصم الأصلي بالتناسب.</p>
        </div>
        <a href="{{ route('movements.show', $original) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">العودة إلى الفاتورة الأصلية</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('movements.returns.store', $original) }}" class="space-y-6">
        @csrf
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-5 grid gap-3 sm:grid-cols-3">
                <p class="text-sm text-slate-600 dark:text-slate-300">الطرف: <strong>{{ $original->party?->name ?? 'نقدي مباشر' }}</strong></p>
                <p class="text-sm text-slate-600 dark:text-slate-300">تاريخ الأصل: <strong>{{ $original->date?->format('Y-m-d') }}</strong></p>
                <p class="text-sm text-slate-600 dark:text-slate-300">العملة: <strong>{{ $original->currency }}</strong></p>
            </div>
            <label class="mb-5 block max-w-xs">
                <span class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">تاريخ المرتجع</span>
                <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
            </label>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-right text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-500 dark:bg-slate-950/60 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">المادة</th>
                            <th class="px-4 py-3">كمية الفاتورة</th>
                            <th class="px-4 py-3">المتبقي للارتجاع</th>
                            <th class="px-4 py-3">سعر الوحدة</th>
                            <th class="px-4 py-3">كمية الإرجاع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($returnableItems as $index => $item)
                            <tr>
                                <td class="px-4 py-4 font-semibold text-slate-900 dark:text-white">
                                    {{ $item['product']?->name ?? 'مادة محذوفة' }}
                                    @if ($item['item_shape'])
                                        <span class="block text-xs font-normal text-slate-500">{{ $item['item_shape'] }}</span>
                                    @endif
                                    <input type="hidden" name="items[{{ $index }}][source_item_id]" value="{{ $item['id'] }}">
                                </td>
                                <td class="px-4 py-4">{{ number_format((float) $original->items->firstWhere('id', $item['id'])->quantity, 3) }}</td>
                                <td class="px-4 py-4">{{ number_format($item['quantity'], 3) }}</td>
                                <td class="px-4 py-4">{{ number_format($item['price_unit'], 2) }} {{ $original->currency }}</td>
                                <td class="px-4 py-4">
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][quantity]"
                                        value="{{ old("items.$index.quantity") }}"
                                        min="0"
                                        max="{{ $item['quantity'] }}"
                                        step="0.001"
                                        aria-label="كمية الإرجاع لـ{{ $item['product']?->name ?? 'المادة' }}"
                                        class="w-36 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                    >
                                </td>
                            </tr>
                        @endforeach
                        @if ($returnableItems->isEmpty())
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-slate-500">تم إرجاع جميع كميات هذه الفاتورة.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>

        <section class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:items-center">
            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">حالة المرتجع</span>
                <select name="status" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="confirmed" @selected(old('status', 'confirmed') === 'confirmed')>تأكيد وتحديث المخزون</option>
                    <option value="draft" @selected(old('status') === 'draft')>حفظ كمسودة دون تحديث المخزون</option>
                </select>
            </label>
            <button type="submit" @disabled($returnableItems->isEmpty()) class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">حفظ {{ $returnTitle }}</button>
        </section>
    </form>
@endsection
