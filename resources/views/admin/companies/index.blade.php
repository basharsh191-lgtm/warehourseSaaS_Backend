@extends('admin.layout')

@section('title', 'إدارة الشركات')

@section('content')
    <header class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">إدارة المنصة</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">الشركات</h1>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400">عدد النتائج: {{ number_format($companies->total()) }}</p>
    </header>

    <section class="app-card overflow-hidden rounded-2xl border">
        <div class="overflow-x-auto">
            <table class="app-table w-full min-w-[1000px] text-right text-sm">
                <thead class="text-xs text-slate-500 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">الشركة</th>
                        <th scope="col" class="px-5 py-3 font-semibold">التواصل</th>
                        <th scope="col" class="px-5 py-3 font-semibold">الحالة</th>
                        <th scope="col" class="px-5 py-3 font-semibold">انتهاء التجربة</th>
                        <th scope="col" class="px-5 py-3 font-semibold">الزيادة السعرية</th>
                        <th scope="col" class="px-5 py-3 font-semibold">المستخدمون</th>
                        <th scope="col" class="px-5 py-3 font-semibold">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($companies as $company)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-white">{{ $company->name }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">
                                <span class="block">{{ $company->email ?: '—' }}</span>
                                <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $company->phone ?: '—' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' => $company->status === 'active',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' => $company->status === 'trial',
                                    'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' => $company->status === 'suspended',
                                ])>{{ ['active' => 'نشطة', 'trial' => 'تجريبية', 'suspended' => 'موقوفة'][$company->status] ?? $company->status }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">
                                @if ($company->trial_ends_at)
                                    @php($daysRemaining = today()->diffInDays($company->trial_ends_at, false))
                                    <span class="block">{{ $company->trial_ends_at->format('Y-m-d') }}</span>
                                    <span @class([
                                        'mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                        'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' => $daysRemaining < 0,
                                        'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' => $daysRemaining == 0,
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' => $daysRemaining > 0,
                                    ])>
                                        @if ($daysRemaining < 0)
                                            انتهت التجربة
                                        @elseif ($daysRemaining == 0)
                                            تنتهي اليوم
                                        @else
                                            متبقي {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'يوم' : 'أيام' }}
                                        @endif
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ number_format((float) $company->percent_markup, 2) }}%</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ number_format($company->users_count) }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('admin.companies.edit', $company) }}" class="inline-flex rounded-lg border border-indigo-200 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-50 dark:border-indigo-900 dark:text-indigo-300 dark:hover:bg-indigo-950">تعديل</a>
                                    @if ($company->status !== 'suspended')
                                        <form method="POST" action="{{ route('admin.companies.terminate-subscription', $company) }}" onsubmit="return confirm('هل تريد إنهاء اشتراك هذه الشركة وإيقافها فوراً؟')">
                                            @csrf
                                            <button type="submit" class="inline-flex rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300 dark:hover:bg-rose-950">إنهاء الاشتراك</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">لا توجد شركات مسجلة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($companies->hasPages())
            <div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $companies->links() }}</div>
        @endif
    </section>
@endsection
