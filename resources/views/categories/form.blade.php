@extends('layouts.app')

@section('title', $category->exists ? 'تعديل فئة' : 'إضافة فئة')

@section('content')
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('categories.index') }}" class="text-sm font-semibold text-emerald-700 hover:underline dark:text-emerald-400">← العودة إلى الفئات</a>
        <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $category->exists ? 'تعديل الفئة' : 'إضافة فئة جديدة' }}</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">أدخل اسم الفئة ووصفاً اختيارياً.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" role="alert">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="mt-6 space-y-5">
                @csrf
                @if ($category->exists)
                    @method('PUT')
                @endif

                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">اسم الفئة</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $category->name) }}" required maxlength="255" autofocus @class([
                        'w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:ring-4 dark:bg-slate-800 dark:text-white',
                        'border-rose-300 focus:border-rose-500 focus:ring-rose-100' => $errors->has('name'),
                        'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100 dark:border-slate-700 dark:focus:ring-emerald-900' => ! $errors->has('name'),
                    ])>
                    @error('name')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">الوصف <span class="font-normal text-slate-400">(اختياري)</span></label>
                    <textarea id="description" name="description" rows="4" maxlength="5000" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:ring-emerald-900">{{ old('description', $category->description) }}</textarea>
                    @error('description')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                    <a href="{{ route('categories.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">إلغاء</a>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">{{ $category->exists ? 'حفظ التغييرات' : 'إضافة الفئة' }}</button>
                </div>
            </form>
        </section>
    </div>
@endsection
