@extends('layouts.app')

@section('title', 'العملاء والموردون')

@section('content')
    <div x-data="{
        modalOpen: @js($errors->any()),
        editing: @js(old('_method') === 'PUT'),
        partyId: @js(old('party_id')),
        form: { name: @js(old('name', '')), phone: @js(old('phone', '')), type: @js(old('type', 'customer')) },
        openCreate() {
            this.editing = false; this.partyId = null;
            this.form = { name: '', phone: '', type: 'customer' };
            this.modalOpen = true;
        },
        openEdit(party) {
            this.editing = true; this.partyId = party.id;
            this.form = { name: party.name, phone: party.phone || '', type: party.type };
            this.modalOpen = true;
        }
    }">
    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">إدارة العلاقات</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">العملاء والموردون</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">نظّم بيانات الأطراف وحالة التعامل معهم.</p>
        </div>
        <button type="button" @click="openCreate()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            إضافة طرف جديد
        </button>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300" role="alert">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-200 p-5 dark:border-slate-800">
                <form method="GET" action="{{ route('parties.index') }}" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_180px_auto]">
                    <label class="relative block">
                        <span class="sr-only">البحث بالاسم أو الهاتف</span>
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="ابحث بالاسم أو رقم الهاتف" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/40">
                    </label>
                    <select name="type" aria-label="تصفية حسب النوع" class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                        <option value="">كل الأنواع</option>
                        <option value="customer" @selected(request('type') === 'customer')>عميل</option>
                        <option value="supplier" @selected(request('type') === 'supplier')>مورد</option>
                    </select>
                    <select name="status" aria-label="تصفية حسب الحالة" class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                        <option value="">كل الحالات</option>
                        <option value="active" @selected(request('status') === 'active')>نشط</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>متوقف</option>
                    </select>
                    <button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">بحث</button>
                </form>
            </div>

            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="font-bold text-slate-900 dark:text-white">قائمة الأطراف</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $parties->total() }} طرف</span>
            </div>

            @if ($parties->isEmpty())
                <div class="px-6 py-14 text-center">
                    <p class="font-semibold text-slate-700 dark:text-slate-200">لا توجد نتائج</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">أضف عميلاً أو مورداً أو غيّر خيارات البحث.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-950/60">
                            <tr class="text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                <th class="px-5 py-3">الاسم</th><th class="px-5 py-3">الهاتف</th><th class="px-5 py-3">النوع</th><th class="px-5 py-3">الحالة</th><th class="px-5 py-3 text-left">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($parties as $party)
                                <tr class="text-sm transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $party->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600 dark:text-slate-300" dir="ltr">{{ $party->phone ?: '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300' => $party->type === 'customer', 'bg-violet-50 text-violet-700 dark:bg-violet-950 dark:text-violet-300' => $party->type === 'supplier'])>{{ $party->type === 'customer' ? 'عميل' : 'مورد' }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' => $party->is_active, 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' => ! $party->is_active])>{{ $party->is_active ? 'نشط' : 'متوقف' }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" @click="openEdit({ id: {{ $party->id }}, name: @js($party->name), phone: @js($party->phone), type: @js($party->type) })" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700 dark:border-slate-700 dark:text-slate-200 dark:hover:border-emerald-700 dark:hover:text-emerald-300">تعديل</button>
                                            <form method="POST" action="{{ route('parties.toggle-status', $party) }}" onsubmit="return confirm('{{ $party->is_active ? 'هل تريد إيقاف هذا الطرف؟' : 'هل تريد إعادة تفعيل هذا الطرف؟' }}')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-amber-300 hover:text-amber-700 dark:border-slate-700 dark:text-slate-200 dark:hover:border-amber-700 dark:hover:text-amber-300">{{ $party->is_active ? 'إيقاف' : 'تفعيل' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($parties->hasPages())<div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $parties->links() }}</div>@endif
            @endif
        </section>

        <div x-cloak x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm" @keydown.escape.window="modalOpen = false" @click.self="modalOpen = false" role="dialog" aria-modal="true" aria-labelledby="party-modal-title">
            <div x-transition class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div><h2 id="party-modal-title" class="text-lg font-bold text-slate-900 dark:text-white" x-text="editing ? 'تعديل بيانات الطرف' : 'إضافة طرف جديد'"></h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">أدخل المعلومات الأساسية للعميل أو المورد.</p></div>
                    <button type="button" @click="modalOpen = false" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800" aria-label="إغلاق">✕</button>
                </div>
                <form method="POST" action="{{ route('parties.store') }}" :action="editing ? '{{ url('/parties') }}/' + partyId : '{{ route('parties.store') }}'" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" :disabled="!editing">
                    <input type="hidden" name="party_id" :value="partyId">
                    <label class="block"><span class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">الاسم <span class="text-rose-500">*</span></span><input x-model="form.name" type="text" name="name" required maxlength="255" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/40"></label>
                    <label class="block"><span class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">رقم الهاتف</span><input x-model="form.phone" type="tel" name="phone" maxlength="50" dir="ltr" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-left text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-emerald-900/40"></label>
                    <fieldset>
                        <legend class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">النوع</legend>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-800 dark:border-slate-700 dark:text-slate-200 dark:has-[:checked]:border-emerald-700 dark:has-[:checked]:bg-emerald-950/50 dark:has-[:checked]:text-emerald-300">
                                <input x-model="form.type" type="radio" name="type" value="customer" required class="h-4 w-4 accent-emerald-600">
                                <span>عميل</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-800 dark:border-slate-700 dark:text-slate-200 dark:has-[:checked]:border-emerald-700 dark:has-[:checked]:bg-emerald-950/50 dark:has-[:checked]:text-emerald-300">
                                <input x-model="form.type" type="radio" name="type" value="supplier" class="h-4 w-4 accent-emerald-600">
                                <span>مورد</span>
                            </label>
                        </div>
                    </fieldset>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="modalOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">إلغاء</button>
                        <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700" x-text="editing ? 'حفظ التعديلات' : 'إضافة الطرف'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
