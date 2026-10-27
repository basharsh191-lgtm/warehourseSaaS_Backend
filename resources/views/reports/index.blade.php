@extends('layouts.app')

@section('title', 'تقارير PDF')

@section('content')
    <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">التقارير</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">تقارير PDF</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">تُنشأ التقارير في الخلفية، وتُتاح للتنزيل بعد اكتمالها.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">تحديث الحالة</a>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-right text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500 dark:bg-slate-950/60 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-3">رقم التقرير</th>
                        <th class="px-5 py-3">الحالة</th>
                        <th class="px-5 py-3">تاريخ الطلب</th>
                        <th class="px-5 py-3">الإجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($reports as $report)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-white">#{{ $report->id }}</td>
                            <td class="px-5 py-4">
                                @if ($report->status === 'ready')
                                    <span class="font-semibold text-emerald-700 dark:text-emerald-300">جاهز للتنزيل</span>
                                @elseif ($report->status === 'failed')
                                    <span class="font-semibold text-rose-700 dark:text-rose-300">{{ $report->error }}</span>
                                @elseif ($report->status === 'processing')
                                    <span class="font-semibold text-amber-700 dark:text-amber-300">جارٍ إنشاء التقرير</span>
                                @else
                                    <span class="font-semibold text-slate-600 dark:text-slate-300">بانتظار المعالجة</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600 dark:text-slate-300">{{ $report->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-5 py-4">
                                @if ($report->status === 'ready')
                                    <a href="{{ route('reports.download', $report) }}" class="inline-flex rounded-lg border border-indigo-200 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-50 dark:border-indigo-900 dark:text-indigo-300 dark:hover:bg-indigo-950">تنزيل PDF</a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">لم يتم طلب تقارير بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reports->hasPages())
            <div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $reports->links() }}</div>
        @endif
    </section>
@endsection
