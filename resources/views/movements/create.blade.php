@extends('layouts.app')

@section('title', 'إنشاء فاتورة')

@section('content')
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">حركة مخزون جديدة</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">إنشاء فاتورة</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">أدخل بيانات الحركة والمواد لتحديث أرصدة المخزون تلقائياً.</p>
        </div>
        <a href="{{ route('movements.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">العودة إلى الفواتير</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" role="alert">
            <p class="mb-1 font-bold">تعذر حفظ الفاتورة:</p>
            <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('movements.store') }}" x-data="movementForm(@js($productsPayload), @js((float) ($company->percent_markup ?? 200)), @js($parties->map(fn ($party) => ['id' => $party->id, 'name' => $party->name, 'type' => $party->type])->values()), @js(old('items', [])), @js(['type' => old('type', 'sale'), 'partyId' => old('party_id', ''), 'discount' => old('discount', 0), 'status' => old('status', 'confirmed')]))" @submit.prevent="submitForm($event)" class="space-y-6">
        @csrf
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div class="mb-5">
                <h2 class="font-bold text-slate-900 dark:text-white">بيانات الحركة</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">حدّد نوع الفاتورة والجهة المرتبطة بها.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label class="block"><span class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">نوع الحركة</span>
                    <select name="type" x-model="type" @change="changeType()" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="sale" @selected(old('type', 'sale') === 'sale')>فاتورة بيع</option><option value="purchase" @selected(old('type') === 'purchase')>فاتورة شراء</option>
                    </select>
                </label>
                <label class="block"><span class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">العميل / المورد <span class="font-normal text-slate-400">(اختياري)</span></span>
                    <select name="party_id" x-model="partyId" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">نقدي مباشر</option>
                        <template x-for="party in partiesForType()" :key="party.id"><option :value="party.id" x-text="party.name"></option></template>
                    </select>
                </label>
                <label class="block"><span class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">رقم المستند</span><input type="text" name="document_no" value="{{ old('document_no') }}" maxlength="255" placeholder="رقم فاتورة المورد (اختياري)" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"></label>
                <label class="block"><span class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">التاريخ</span><input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"></label>
                <label class="block"><span class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">حالة الفاتورة</span><select name="status" x-model="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"><option value="confirmed">تأكيد الفاتورة وتحديث المخزون</option><option value="draft">حفظ كمسودة دون تحديث المخزون</option></select></label>
            </div>
        </section>

        <section class="overflow-visible rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col justify-between gap-4 border-b border-slate-200 p-5 dark:border-slate-800 sm:flex-row sm:items-center">
                <div><h2 class="font-bold text-slate-900 dark:text-white">مواد الفاتورة</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">ابحث بالاسم أو امسح الباركود لإضافة مادة بسرعة.</p></div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <div class="relative min-w-64">
                        <input type="search" x-model="search" @keydown="searchKeydown($event)" autocomplete="off" placeholder="ابحث عن مادة أو امسح الباركود" aria-label="بحث عن مادة" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/40">
                        <div x-cloak x-show="filteredProducts.length" class="absolute inset-x-0 top-full z-20 mt-2 max-h-64 overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                            <template x-for="product in filteredProducts" :key="product.id"><button type="button" @click="addProduct(product)" class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-right hover:bg-slate-50 dark:hover:bg-slate-800"><span><span class="block text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="product.name"></span><span class="block text-xs text-slate-500" x-text="product.barcode || 'بدون باركود'"></span></span><span class="text-xs text-slate-400" x-text="'المتاح: ' + product.stock"></span></button></template>
                        </div>
                    </div>
                    <button type="button" @click="addBlankItem()" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300 dark:hover:bg-emerald-950">إضافة سطر</button>
                </div>
            </div>

            <div x-cloak x-show="error" x-text="error" role="alert" class="mx-5 mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300"></div>
            <div class="overflow-x-auto">
                <table class="min-w-[1100px] w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/60"><tr class="text-right text-xs font-semibold text-slate-500 dark:text-slate-400"><th class="px-4 py-3">المادة والباركود</th><th class="px-4 py-3">شكل المادة / الموديل</th><th class="px-4 py-3">المخزون المتاح</th><th class="px-4 py-3">الكمية</th><th class="px-4 py-3">سعر الوحدة</th><th class="px-4 py-3">الإجمالي</th><th class="px-4 py-3"><span class="sr-only">حذف</span></th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="min-w-64 px-4 py-3">
                                    <select x-model="item.product_id" @change="selectProduct(item, productForId($event.target.value))" :name="'items[' + index + '][product_id]'" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                        <option value="">اختر المادة</option><template x-for="product in products" :key="product.id"><option :value="product.id" x-text="product.name + (product.barcode ? ' · ' + product.barcode : '')"></option></template>
                                    </select>
                                    <p class="mt-1 text-xs text-slate-500" x-text="item.barcode || '—'"></p>
                                </td>
                                <td class="min-w-48 px-4 py-3">
                                    <input type="text" x-model="item.item_shape" :name="'items[' + index + '][item_shape]'" maxlength="255" placeholder="مثال: ربطة، تزينة، سادة..." class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                </td>
                                <td class="whitespace-nowrap px-4 py-3"><span x-show="type === 'sale'" :class="item.quantity > item.stock ? 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" x-text="item.stock + ' متاح'"></span><span x-show="type !== 'sale'" class="text-xs text-slate-400">غير مطلوب</span></td>
                                <td class="px-4 py-3"><input type="number" min="0.001" step="0.001" max="999999999.999" x-model.number="item.quantity" :name="'items[' + index + '][quantity]'" required class="w-28 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm tabular-nums text-slate-900 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"></td>
                                <td class="px-4 py-3"><input type="number" min="0" step="0.01" x-model.number="item.price_unit" :name="'items[' + index + '][price_unit]'" required class="w-32 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm tabular-nums text-slate-900 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"></td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold tabular-nums text-slate-900 dark:text-slate-100" x-text="((Number(item.quantity) || 0) * (Number(item.price_unit) || 0)).toFixed(2)"></td>
                                <td class="px-4 py-3"><button type="button" @click="removeItem(index)" class="rounded-lg p-2 text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-950/50" :aria-label="'حذف السطر ' + (index + 1)"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M10 11v6m4-6v6M5 7l1 13h12l1-13M9 7V4h6v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button></td>
                            </tr>
                        </template>
                        <tr x-show="items.length === 0"><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500 dark:text-slate-400">لا توجد مواد مضافة. استخدم البحث أو زر «إضافة سطر» للبدء.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div class="ml-auto max-w-lg space-y-4">
                <div class="flex items-center justify-between text-sm"><span class="text-slate-500 dark:text-slate-400">الإجمالي الفرعي</span><span class="font-semibold tabular-nums text-slate-900 dark:text-white" x-text="subtotal.toFixed(2)"></span></div>
                <label class="flex items-center justify-between gap-4 text-sm"><span class="font-medium text-slate-600 dark:text-slate-300">خصم الفاتورة</span><input type="number" name="discount" x-model.number="discount" min="0" step="0.01" :max="subtotal" value="{{ old('discount', 0) }}" class="w-40 rounded-lg border border-slate-200 bg-white px-3 py-2 text-left text-sm tabular-nums text-slate-900 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"></label>
                <div class="flex items-center justify-between border-t border-slate-200 pt-4 dark:border-slate-700"><span class="font-bold text-slate-900 dark:text-white">الصافي المستحق</span><span class="text-xl font-extrabold tabular-nums text-emerald-700 dark:text-emerald-400" x-text="total.toFixed(2)"></span></div>
                <p class="text-xs text-slate-500 dark:text-slate-400">يتم احتساب الإجمالي تلقائياً. الفاتورة المسودة لا تؤثر على المخزون حتى تأكيدها.</p>
            </div>
            <div class="mt-6 flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-5 dark:border-slate-800 sm:flex-row">
                <a href="{{ route('movements.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">إلغاء</a>
                <button type="submit" :disabled="submitting" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-70">
                    <svg x-show="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    <span x-text="submitting ? 'جارٍ حفظ الفاتورة…' : 'حفظ الفاتورة'"></span>
                </button>
            </div>
        </section>
    </form>
@endsection
