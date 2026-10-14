@php
    $isSuperAdmin = $isSuperAdmin ?? false;
    $companyName = $isSuperAdmin
        ? config('app.name')
        : (auth()->user()?->company?->name ?? 'اسم الشركة');
    $companyLogo = $isSuperAdmin ? null : auth()->user()?->company?->logo_path;
@endphp

<aside
    :class="{ 'is-open': mobileNavOpen }"
    class="app-sidebar fixed inset-y-0 right-0 z-50 flex h-screen w-[min(88vw,288px)] shrink-0 flex-col justify-between border-l border-slate-200 bg-white px-4 pb-5 pt-5 shadow-2xl shadow-slate-950/10 transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:z-40 lg:h-screen lg:w-[256px] lg:min-w-[256px] lg:shadow-none dark:border-slate-800 dark:bg-slate-900"
>
    {{-- الشعار --}}
    <div class="flex flex-col">

        <a
            href="{{ $isSuperAdmin ? route('admin.dashboard') : route('dashboard') }}"
            class="mb-5 flex items-center gap-3 border-b border-slate-200 px-2 pb-5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:border-slate-800"
        >
            @if ($companyLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($companyLogo))
<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($companyLogo) }}" alt="شعار {{ $companyName }}" class="h-10 w-10 shrink-0 rounded-xl border border-slate-200 bg-white object-cover dark:border-slate-700 dark:bg-slate-800">            @else
                <span class="brand-mark flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white shadow-lg shadow-indigo-900/20">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 8.5 12 4l8 4.5v8L12 21l-8-4.5v-8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="m8 10.75 4 2.25 4-2.25M12 13v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            @endif

            <span class="min-w-0">
                <span title="{{ $companyName }}" class="block truncate text-sm font-bold text-slate-900 dark:text-white">
                    {{ $companyName }}
                </span>

                <span class="block truncate text-xs text-slate-500 dark:text-slate-400">
                    {{ $isSuperAdmin ? 'إدارة المنصة' : 'إدارة المخزون' }}
                </span>
            </span>
        </a>

        {{-- القائمة --}}
        @unless ($isSuperAdmin)
            <nav
                aria-label="التنقل الرئيسي"
                class="flex max-h-[calc(100vh-190px)] flex-col gap-1.5 overflow-y-auto pe-1"
            >

                {{-- الرئيسية --}}
                <a
                    href="{{ route('dashboard') }}"
                    @class([
                        'app-sidebar-link relative flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500',
                        'is-active text-white shadow-md shadow-emerald-900/10 before:absolute before:top-1/2 before:h-6 before:w-[3px] before:-translate-y-1/2 before:rounded-full before:bg-white before:[inset-inline-start:0]' => request()->routeIs('dashboard'),
                        'text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/70 dark:hover:text-white' => !request()->routeIs('dashboard'),
                    ])
                >
                    <svg
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    </svg>

                    <span>الرئيسية</span>
                </a>

                {{-- الفئات --}}
                <a
                    href="{{ route('categories.index') }}"
                    @class([
                        'app-sidebar-link relative flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500',
                        'is-active text-white shadow-md shadow-emerald-900/10 before:absolute before:top-1/2 before:h-6 before:w-[3px] before:-translate-y-1/2 before:rounded-full before:bg-white before:[inset-inline-start:0]' => request()->routeIs('categories.*'),
                        'text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/70 dark:hover:text-white' => !request()->routeIs('categories.*'),
                    ])
                >
                    <svg
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>

                    <span>الفئات</span>
                </a>

                {{-- المواد --}}
                <a
                    href="{{ route('products.index') }}"
                    @class([
                        'app-sidebar-link relative flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500',
                        'is-active text-white shadow-md shadow-emerald-900/10 before:absolute before:top-1/2 before:h-6 before:w-[3px] before:-translate-y-1/2 before:rounded-full before:bg-white before:[inset-inline-start:0]' => request()->routeIs('products.*'),
                        'text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/70 dark:hover:text-white' => !request()->routeIs('products.*'),
                    ])
                >
                    <svg
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    </svg>

                    <span>المواد</span>
                </a>

                {{-- الأطراف --}}
                <a
                    href="{{ route('parties.index') }}"
                    @class([
                        'app-sidebar-link relative flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500',
                        'is-active text-white shadow-md shadow-emerald-900/10 before:absolute before:top-1/2 before:h-6 before:w-[3px] before:-translate-y-1/2 before:rounded-full before:bg-white before:[inset-inline-start:0]' => request()->routeIs('parties.*'),
                        'text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/70 dark:hover:text-white' => !request()->routeIs('parties.*'),
                    ])
                >
                    <svg
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>

                    <span>الأطراف</span>
                </a>

                {{-- الفواتير --}}
                <a
                    href="{{ route('movements.index') }}"
                    @class([
                        'app-sidebar-link relative flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500',
                        'is-active text-white shadow-md shadow-emerald-900/10 before:absolute before:top-1/2 before:h-6 before:w-[3px] before:-translate-y-1/2 before:rounded-full before:bg-white before:[inset-inline-start:0]' => request()->routeIs('movements.*'),
                        'text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/70 dark:hover:text-white' => !request()->routeIs('movements.*'),
                    ])
                >
                    <svg
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>

                    <span>الفواتير</span>
                </a>

                {{-- الإعدادات --}}
                <a
                    href="{{ route('settings.index') }}"
                    @class([
                        'app-sidebar-link relative flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500',
                        'is-active text-white shadow-md shadow-emerald-900/10 before:absolute before:top-1/2 before:h-6 before:w-[3px] before:-translate-y-1/2 before:rounded-full before:bg-white before:[inset-inline-start:0]' => request()->routeIs('settings.*'),
                        'text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/70 dark:hover:text-white' => !request()->routeIs('settings.*'),
                    ])
                >
                    <svg
                        class="h-5 w-5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z"/>
                        <path d="m19.4 15 .1.1a1.8 1.8 0 1 1-2.5 2.5l-.1-.1a1.8 1.8 0 0 0-3 .9v.2a1.8 1.8 0 1 1-3.6 0v-.2a1.8 1.8 0 0 0-3-.9l-.1.1a1.8 1.8 0 1 1-2.5-2.5l.1-.1a1.8 1.8 0 0 0-.9-3h-.2a1.8 1.8 0 1 1 0-3.6h.2a1.8 1.8 0 0 0 .9-3l-.1-.1a1.8 1.8 0 1 1 2.5-2.5l.1.1a1.8 1.8 0 0 0 3-.9v-.2a1.8 1.8 0 1 1 3.6 0v.2a1.8 1.8 0 0 0 3 .9l.1-.1a1.8 1.8 0 1 1 2.5 2.5l-.1.1a1.8 1.8 0 0 0 .9 3h.2a1.8 1.8 0 1 1 0 3.6h-.2a1.8 1.8 0 0 0-.9 3Z"/>
                    </svg>

                    <span>الإعدادات</span>
                </a>

            </nav>
        @endunless
    </div>

    {{-- أسفل القائمة --}}
    <div class="flex flex-col gap-4 border-t border-slate-200 pt-4 dark:border-slate-800">

        {{-- تبديل الوضع --}}
        <button
            type="button"
            data-theme-toggle
            aria-label="تفعيل الوضع الداكن"
            aria-pressed="false"
            class="flex w-full items-center justify-between rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-500 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-emerald-700 dark:hover:bg-slate-700"
        >
            <span class="flex items-center gap-2">

                <svg
                    data-theme-icon="moon"
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M20.2 15.1A8.5 8.5 0 0 1 8.9 3.8 8.5 8.5 0 1 0 20.2 15.1Z"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

                <svg
                    data-theme-icon="sun"
                    class="hidden h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="4"
                        stroke="currentColor"
                        stroke-width="1.8"
                    />

                    <path
                        d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>

                <span data-theme-label>الوضع الداكن</span>
            </span>
        </button>

        {{-- تسجيل الخروج --}}
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf

            <button
                type="submit"
                class="flex w-full items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-3 text-sm font-semibold text-rose-700 transition-all duration-200 hover:border-rose-300 hover:bg-rose-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2 dark:border-red-900/50 dark:bg-red-600/10 dark:text-red-400 dark:hover:border-red-700 dark:hover:bg-red-600/20 dark:hover:text-red-300 dark:focus-visible:ring-offset-slate-900"
            >
                <svg
                    class="h-5 w-5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M10 17l5-5-5-5m5 5H3"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M12 4h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

                <span>خروج</span>
            </button>
        </form>

    </div>
</aside>
