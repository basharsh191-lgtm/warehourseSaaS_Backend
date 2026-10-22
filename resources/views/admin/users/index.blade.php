@extends('admin.layout')

@section('title', 'إدارة المستخدمين')

@section('content')
    <header class="mb-6">
        <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">إدارة المنصة</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">المستخدمون</h1>
    </header>

    <form method="GET" action="{{ route('admin.users.index') }}" class="app-card mb-5 grid gap-4 rounded-2xl border p-5 sm:grid-cols-3 sm:items-end">
        <div>
            <label for="company_id" class="mb-2 block text-sm font-semibold">الشركة</label>
            <select id="company_id" name="company_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
                <option value="">جميع الشركات</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected((string) ($filters['company_id'] ?? '') === (string) $company->id)>{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="role" class="mb-2 block text-sm font-semibold">الدور</label>
            <select id="role" name="role" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
                <option value="">جميع الأدوار</option>
                <option value="super_admin" @selected(($filters['role'] ?? '') === 'super_admin')>مدير المنصة</option>
                <option value="company_admin" @selected(($filters['role'] ?? '') === 'company_admin')>مدير شركة</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold">تطبيق التصفية</button>
            <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold dark:border-slate-700">مسح</a>
        </div>
    </form>

    <section class="app-card overflow-hidden rounded-2xl border">
        <div class="overflow-x-auto">
            <table class="app-table w-full min-w-[760px] text-right text-sm">
                <thead class="text-xs text-slate-500 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">المستخدم</th>
                        <th scope="col" class="px-5 py-3 font-semibold">البريد الإلكتروني</th>
                        <th scope="col" class="px-5 py-3 font-semibold">الشركة</th>
                        <th scope="col" class="px-5 py-3 font-semibold">الدور</th>
                        <th scope="col" class="px-5 py-3 font-semibold">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-white">{{ $user->name }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $user->email }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $user->company?->name ?? 'إدارة المنصة' }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $user->role === 'super_admin' ? 'مدير المنصة' : 'مدير شركة' }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' => $user->is_active,
                                    'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300' => ! $user->is_active,
                                ])>{{ $user->is_active ? 'نشط' : 'غير نشط' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500 dark:text-slate-400">لا يوجد مستخدمون مطابقون لخيارات التصفية.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-slate-200 p-4 dark:border-slate-800">{{ $users->links() }}</div>
        @endif
    </section>
@endsection
