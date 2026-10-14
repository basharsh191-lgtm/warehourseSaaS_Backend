@extends('layouts.app')

@section('title', $product->exists ? 'تعديل مادة' : 'إضافة مادة')

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('products.index') }}" class="text-sm font-semibold text-emerald-700 hover:underline dark:text-emerald-400">← العودة إلى المواد</a>
        <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $product->exists ? 'تعديل المادة' : 'إضافة مادة جديدة' }}</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">أدخل معلومات المادة لتسهيل تتبعها في المخزون.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" role="alert">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}" class="mt-6 grid gap-5 sm:grid-cols-2">
                @csrf
                @if ($product->exists)
                    @method('PUT')
                @endif

                <div class="sm:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">اسم المادة <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" required maxlength="255" autofocus placeholder="مثال: زيت زيتون 1 لتر" @class([
                        'w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500',
                        'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('name'),
                        'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 dark:border-slate-700 dark:focus:ring-emerald-900' => ! $errors->has('name'),
                    ])>
                    @error('name')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="barcode" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">الباركود <span class="font-normal text-slate-400">(اختياري)</span></label>
                    <input id="barcode" name="barcode" type="text" value="{{ old('barcode', $product->barcode) }}" maxlength="255" inputmode="numeric" placeholder="امسح الرمز أو أدخله" @class([
                        'w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500',
                        'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('barcode'),
                        'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 dark:border-slate-700 dark:focus:ring-emerald-900' => ! $errors->has('barcode'),
                    ])>
                    @error('barcode')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="category_id" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">الفئة <span class="font-normal text-slate-400">(اختياري)</span></label>
                    <select id="category_id" name="category_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-emerald-900">
                        <option value="">بدون فئة</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="unit" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">الوحدة <span class="text-rose-500">*</span></label>
                    <input id="unit" name="unit" type="text" value="{{ old('unit', $product->unit) }}" required maxlength="100" placeholder="قطعة، كرتون، كغ..." @class([
                        'w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500',
                        'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('unit'),
                        'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 dark:border-slate-700 dark:focus:ring-emerald-900' => ! $errors->has('unit'),
                    ])>
                    @error('unit')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="item_shape" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">شكل المادة / الموديل <span class="font-normal text-slate-400">(اختياري)</span></label>
                    <input id="item_shape" name="item_shape" type="text" value="{{ old('item_shape', $product->item_shape) }}" maxlength="255" placeholder="مثال: ربطة، تزينة، سادة..." @class([
                        'w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500',
                        'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('item_shape'),
                        'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 dark:border-slate-700 dark:focus:ring-emerald-900' => ! $errors->has('item_shape'),
                    ])>
                    @error('item_shape')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="location" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">الموقع / الرف <span class="font-normal text-slate-400">(اختياري)</span></label>
                    <input id="location" name="location" type="text" value="{{ old('location', $product->location) }}" maxlength="255" placeholder="مثال: مستودع A - رف 03" @class([
                        'w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500',
                        'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('location'),
                        'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 dark:border-slate-700 dark:focus:ring-emerald-900' => ! $errors->has('location'),
                    ])>
                    @error('location')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="price_sale_custom" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">سعر البيع اليدوي <span class="font-normal text-slate-400">(اختياري)</span></label>
                    <input id="price_sale_custom" name="price_sale_custom" type="number" value="{{ old('price_sale_custom', $product->price_sale_custom) }}" min="0" max="9999999999.99" step="0.01" placeholder="0.00" @class([
                        'w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500',
                        'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('price_sale_custom'),
                        'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 dark:border-slate-700 dark:focus:ring-emerald-900' => ! $errors->has('price_sale_custom'),
                    ])>
                    @error('price_sale_custom')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="stock_min" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">الحد الأدنى للمخزون <span class="font-normal text-slate-400">(اختياري)</span></label>
                    <input id="stock_min" name="stock_min" type="number" value="{{ old('stock_min', $product->stock_min) }}" min="0" max="999999999.999" step="0.001" placeholder="0" @class([
                        'w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500',
                        'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('stock_min'),
                        'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 dark:border-slate-700 dark:focus:ring-emerald-900' => ! $errors->has('stock_min'),
                    ])>
                    @error('stock_min')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-col-reverse gap-3 pt-2 sm:col-span-2 sm:flex-row sm:justify-end">
                    <a href="{{ route('products.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">إلغاء</a>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">{{ $product->exists ? 'حفظ التغييرات' : 'إضافة المادة' }}</button>
                </div>
            </form>
        </section>
    </div>
@endsection
