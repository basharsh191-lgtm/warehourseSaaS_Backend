@extends('admin.layout')

@section('title', 'لوحة إدارة المنصة')

@section('content')
    <header class="mb-6">
        <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">نظرة عامة</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">لوحة إدارة المنصة</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">مرحباً {{ auth()->user()->name }}، إليك ملخص حالة الشركات.</p>
    </header>

    <section aria-label="إحصائيات الشركات" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'إجمالي الشركات', 'value' => $stats['total'], 'style' => 'indigo'],
            ['label' => 'الشركات النشطة', 'value' => $stats['active'], 'style' => 'emerald'],
            ['label' => 'الفترة التجريبية', 'value' => $stats['trial'], 'style' => 'amber'],
            ['label' => 'الشركات الموقوفة', 'value' => $stats['suspended'], 'style' => 'rose'],
        ] as $stat)
            <article class="app-card rounded-2xl border p-5">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</p>
                <p class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($stat['value']) }}</p>
            </article>
        @endforeach
    </section>

    <section class="app-card mt-7 overflow-hidden rounded-2xl border">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">أحدث الشركات</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">آخر الشركات التي سُجلت في المنصة.</p>
            </div>
            <a href="{{ route('admin.companies.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">عرض جميع الشركات</a>
        </div>

        <div class="overflow-x-auto">
            <table class="app-table w-full min-w-[700px] text-right text-sm">
                <thead class="text-xs text-slate-500 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">الشركة</th>
                        <th scope="col" class="px-5 py-3 font-semibold">الحالة</th>
                        <th scope="col" class="px-5 py-3 font-semibold">تاريخ التسجيل</th>
                        <th scope="col" class="px-5 py-3 font-semibold">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($recentCompanies as $company)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $company->name }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $company->email ?: 'لا يوجد بريد مسجل' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' => $company->status === 'active',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' => $company->status === 'trial',
                                    'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' => $company->status === 'suspended',
                                ])>{{ ['active' => 'نشطة', 'trial' => 'تجريبية', 'suspended' => 'موقوفة'][$company->status] ?? $company->status }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $company->created_at?->format('Y-m-d') }}</td>
                            <td class="px-5 py-4"><a href="{{ route('admin.companies.edit', $company) }}" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">إدارة الشركة</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">لا توجد شركات مسجلة بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
