<!DOCTYPE html>
<html lang="en" class="h-full antialiased">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Reseller Portal' }} — {{ config('app.name', 'DomainReseller') }} Wholesale</title>

    <script>
        (function() {
            var t = localStorage.getItem('theme') || 'system';
            var m = t === 'system' ? (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : t;
            document.documentElement.setAttribute('data-theme', m);
            document.documentElement.style.colorScheme = m;
            if (m === 'dark') document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.min.css') . '?v=' . @filemtime(public_path('css/app.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') . '?v=' . @filemtime(public_path('css/design-system.css')) }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: ['class', '[data-theme="dark"]'],
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#F6821F',
                            hover: '#E07116',
                            active: '#C9620E',
                            subtle: '#FFF7ED',
                        },
                        cf: {
                            bg: 'var(--bg)',
                            surface: 'var(--surface)',
                            border: 'var(--border)',
                            text: 'var(--text)',
                            muted: 'var(--text-muted)',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="{{ asset('js/theme.js') }}"></script>
    <script defer src="{{ asset('js/currency.js') }}"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background-color: var(--bg); color: var(--text); }
        .tabular-nums { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="h-full flex flex-col overflow-hidden font-sans" x-data="{ sidebarOpen: false }">
@if(session('admin_impersonator_id'))
    <div class="w-full shrink-0 bg-[#F6821F] text-white px-4 py-2 text-xs font-semibold flex items-center justify-between shadow-md z-50">
        <div class="flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-white animate-pulse"></span>
            <span>⚡ You are currently logged in as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }}).</span>
        </div>
        <form action="{{ route('impersonate.stop') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="px-3 py-1 bg-black/30 hover:bg-black/50 text-white rounded font-bold transition text-[11px] uppercase tracking-wider">
                ← Return to Admin Account
            </button>
        </form>
    </div>
@endif


    <!-- Mobile Backdrop -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"></div>

    <!-- Reseller Sidebar: 240px -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-60 flex flex-col transition-transform duration-150 lg:static lg:translate-x-0"
           style="background-color: var(--surface); border-right: 1px solid var(--border);">
        <!-- Logo Header -->
        <div class="h-14 flex items-center px-4 border-b shrink-0" style="border-color: var(--border);">
            <a href="{{ route('reseller.dashboard') }}" class="flex items-center gap-2.5 group">
                
                <div class="flex flex-col">
                    <span class="font-bold text-sm tracking-tight text-slate-900 dark:text-white leading-tight">HOST NIBO</span>
                    <span class="text-[9px] font-mono uppercase tracking-wider text-slate-400 dark:text-slate-500">Reseller Portal</span>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto text-xs">
            <a href="{{ route('reseller.dashboard') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('reseller.dashboard') ? 'bg-[#FFF7ED] dark:bg-[#F6821F]/15 text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('reseller.pricing') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('reseller.pricing') ? 'bg-[#FFF7ED] dark:bg-[#F6821F]/15 text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.386a11.944 11.944 0 003.5-3.5c.486-.827.313-1.908-.386-2.607L9.568 3z" /></svg>
                <span>Wholesale pricing</span>
            </a>

            <div class="pt-3.5 pb-1 px-2.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-400">API & Integrations</div>
            <a href="{{ route('reseller.api-keys.index') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('reseller.api-keys.*') ? 'bg-[#FFF7ED] dark:bg-[#F6821F]/15 text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                <span>API credentials</span>
            </a>
            <a href="{{ route('reseller.api-docs') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('reseller.api-docs') ? 'bg-[#FFF7ED] dark:bg-[#F6821F]/15 text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                <span>API & WHMCS guide</span>
            </a>
            <a href="{{ route('reseller.webhooks.index') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('reseller.webhooks.*') ? 'bg-[#FFF7ED] dark:bg-[#F6821F]/15 text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>
                <span>Webhooks</span>
            </a>

            <div class="pt-3.5 pb-1 px-2.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-400">Management</div>
            <a href="{{ route('reseller.sub-clients.index') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('reseller.sub-clients.*') ? 'bg-[#FFF7ED] dark:bg-[#F6821F]/15 text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                <span>Sub-clients</span>
            </a>
            <a href="{{ route('reseller.profile.edit') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('reseller.profile.*') || request()->routeIs('reseller.two-factor.*') || request()->routeIs('reseller.sessions.*') ? 'bg-[#FFF7ED] dark:bg-[#F6821F]/15 text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                <span>Password & Passkeys</span>
            </a>
            <a href="{{ route('reseller.whitelabel') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('reseller.whitelabel') ? 'bg-[#FFF7ED] dark:bg-[#F6821F]/15 text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.39m3.42 3.415a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.416a6.717 6.717 0 01-3.42-3.416" /></svg>
                <span>White-label setup</span>
            </a>
        </nav>

        <!-- Reseller Footer Brief -->
        <div class="p-3 border-t shrink-0" style="border-color: var(--border);">
            <div class="flex items-center justify-between">
                <a href="{{ route('reseller.profile.edit') }}" class="truncate block group hover:opacity-90 transition" title="Account & Security">
                    <p class="text-xs font-semibold truncate group-hover:text-[#F6821F]" style="color: var(--text);">{{ auth()->user()->company_name ?? auth()->user()->name }}</p>
                    <p class="text-[11px] truncate" style="color: var(--text-muted);">{{ auth()->user()->email }}</p>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-1 rounded text-[var(--danger)] hover:bg-[var(--danger-bg)]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Navigation Bar: 56px -->
        <header class="h-14 border-b flex items-center justify-between px-4 sm:px-6 z-10 shrink-0"
                style="background-color: var(--bg); border-color: var(--border);">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="p-1.5 rounded text-[var(--text-muted)] lg:hidden">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <h1 class="text-sm font-semibold truncate" style="color: var(--text);">{{ $header ?? 'Reseller Control Center' }}</h1>
            </div>

            <!-- Top Right: Wholesale Balance + Currency + Theme Toggle -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-2.5 py-1 rounded-[4px] border text-xs" style="background-color: var(--surface); border-color: var(--border);">
                    <span style="color: var(--text-muted);">Wholesale balance:</span>
                    <span class="font-mono font-semibold" style="color: var(--text);">
                        <x-price :usd="auth()->user()->balance" />
                    </span>
                </div>

                <x-currency-selector />
                <x-theme-toggle />

                <a href="{{ route('home') }}" target="_blank" class="cf-btn cf-btn-ghost cf-btn-sm text-xs">
                    Storefront
                </a>
            </div>
        </header>

        <!-- Page Body -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8" style="background-color: var(--bg);">
            <x-announcement-banner />
            @if(session('success'))
                <div class="mb-4 p-3 rounded-[6px] text-xs flex items-center gap-2"
                     style="background-color: var(--success-bg); border: 1px solid var(--success); color: var(--success);">
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-3 rounded-[6px] text-xs flex items-center gap-2"
                     style="background-color: var(--danger-bg); border: 1px solid var(--danger); color: var(--danger);">
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
    </div>

</body>
</html>
