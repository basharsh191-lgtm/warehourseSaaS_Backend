@extends('layouts.app')

@section('title', 'الفئات')

@section('content')
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">تنظيم المخزون</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">الفئات</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">نظّم المواد ضمن فئات واضحة.</p>
        </div>
        <a href="{{ route('categories.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            إضافة فئة
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" role="alert">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
            <h2 class="font-bold text-slate-900 dark:text-white">قائمة الفئات</h2>
            <span class="theme-counter rounded-full px-3 py-1 text-xs font-semibold">{{ $categories->total() }} فئة</span>
        </div>
        @if ($categories->isEmpty())
            <div class="px-6 py-14 text-center">
                <p class="font-semibold text-slate-700 dark:text-slate-200">لا توجد فئات بعد</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">أضف فئتك الأولى لبدء تنظيم المواد.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/60">
                        <tr class="text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <th class="px-5 py-3">اسم الفئة</th>
                            <th class="px-5 py-3">الوصف</th>
                            <th class="px-5 py-3">الحالة</th>
                            <th class="px-5 py-3 text-left">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($categories as $category)
                            <tr class="text-sm transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $category->name }}</td>
                                <td class="max-w-md px-5 py-4 text-slate-500 dark:text-slate-400">{{ $category->description ?: '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'status-badge' => $category->is_active,
                                        'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' => ! $category->is_active,
                                    ])>{{ $category->is_active ? 'نشطة' : 'متوقفة' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('categories.edit', $category) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700 dark:border-slate-700 dark:text-slate-200 dark:hover:border-emerald-700 dark:hover:text-emerald-300">تعديل</a>
                                        <form method="POST" action="{{ route('categories.toggle-status', $category) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-amber-300 hover:text-amber-700 dark:border-slate-700 dark:text-slate-200 dark:hover:border-amber-700 dark:hover:text-amber-300">{{ $category->is_active ? 'إيقاف' : 'تفعيل' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($categories->hasPages())
                <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $categories->links() }}</div>
            @endif
        @endif
    </section>
@endsection
