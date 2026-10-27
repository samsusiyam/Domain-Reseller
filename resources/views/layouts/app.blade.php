<!DOCTYPE html>
<html lang="en" class="h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Client Portal' }} — {{ config('app.name', 'DomainReseller') }}</title>

    <script>
        (function() {
            var t = localStorage.getItem('theme') || 'system';
            var m = t === 'system' ? (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : t;
            document.documentElement.setAttribute('data-theme', m);
            document.documentElement.style.colorScheme = m;
            if (m === 'dark') document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        })();

        window.toggleTheme = function() {
            var isDark = document.documentElement.classList.contains('dark') || document.documentElement.getAttribute('data-theme') === 'dark';
            var next = isDark ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            document.documentElement.style.colorScheme = next;
            if (next === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            localStorage.setItem('theme', next);
            document.cookie = 'theme=' + next + ';path=/;max-age=31536000;SameSite=Lax';
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: next } }));
        };
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
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
<body class="h-full flex overflow-hidden font-sans" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"></div>

    <!-- Sidebar: 240px, 1px right border, clean functional minimal -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-60 flex flex-col transition-transform duration-150 lg:static lg:translate-x-0"
           style="background-color: var(--surface); border-right: 1px solid var(--border);">
        <!-- Brand Header -->
        <div class="h-14 flex items-center justify-between px-4 border-b shrink-0" style="border-color: var(--border);">
            <a href="{{ route('client.dashboard') }}" class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-[4px] bg-[#F6821F] flex items-center justify-center text-white shrink-0">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/>
                    </svg>
                </div>
                <span class="font-semibold text-sm tracking-tight" style="color: var(--text);">Client portal</span>
            </a>
            <button @click="sidebarOpen = false" class="p-1 rounded text-slate-500 hover:text-slate-900 lg:hidden">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto text-xs">
            <a href="{{ route('client.dashboard') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('client.dashboard') ? 'bg-[#FFF7ED] text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-[var(--bg)]' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                <span>Overview</span>
            </a>

            <div class="pt-3 pb-1 px-2.5 text-[11px] font-medium" style="color: var(--text-muted);">Domains</div>

            <a href="{{ route('client.domains.index') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('client.domains.index') || request()->routeIs('client.domains.show') ? 'bg-[#FFF7ED] text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-[var(--bg)]' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-.778.099-1.533.284-2.253" /></svg>
                <span>My domains</span>
            </a>
            <a href="{{ route('domain.search') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-[var(--bg)]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                <span>Register domain</span>
            </a>
            <a href="{{ route('client.domains.transfer') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('client.domains.transfer') ? 'bg-[#FFF7ED] text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-[var(--bg)]' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                <span>Transfer in</span>
            </a>

            <div class="pt-3 pb-1 px-2.5 text-[11px] font-medium" style="color: var(--text-muted);">Billing</div>

            <a href="{{ route('client.invoices.index') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('client.invoices.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-[var(--bg)]' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                <span>Invoices</span>
            </a>
            <a href="{{ route('client.wallet.index') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('client.wallet.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-[var(--bg)]' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" /></svg>
                <span>Wallet & funds</span>
            </a>
            <a href="{{ route('client.transactions.index') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('client.transactions.index') ? 'bg-[#FFF7ED] text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-[var(--bg)]' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
                <span>Transactions</span>
            </a>

            <div class="pt-3 pb-1 px-2.5 text-[11px] font-medium" style="color: var(--text-muted);">Support & Account</div>

            <a href="{{ route('client.tickets.index') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('client.tickets.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-[var(--bg)]' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" /></svg>
                <span>Support tickets</span>
            </a>
            <a href="{{ route('client.profile.edit') }}"
               class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition {{ request()->routeIs('client.profile.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-semibold border-l-2 border-[#F6821F]' : 'text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-[var(--bg)]' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                <span>Profile & security</span>
            </a>
        </nav>

        <!-- User Info & Logout -->
        <div class="p-3 border-t shrink-0" style="border-color: var(--border);">
            @auth
            <div class="flex items-center justify-between">
                <div class="truncate">
                    <p class="text-xs font-semibold truncate" style="color: var(--text);">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] truncate" style="color: var(--text-muted);">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-1 rounded text-[var(--danger)] hover:bg-[var(--danger-bg)]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                    </button>
                </form>
            </div>
            @endauth
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
                <h1 class="text-sm font-semibold truncate" style="color: var(--text);">{{ $header ?? 'Client Portal' }}</h1>
            </div>

            <!-- Top Right: Wallet + Currency + Theme Toggle -->
            <div class="flex items-center gap-3">
                @auth
                <div class="hidden sm:flex items-center gap-2 px-2.5 py-1 rounded-[4px] border text-xs" style="background-color: var(--surface); border-color: var(--border);">
                    <span style="color: var(--text-muted);">Wallet:</span>
                    <span class="font-mono font-semibold" style="color: var(--text);">
                        <x-price :usd="auth()->user()->balance" />
                    </span>
                    <a href="{{ route('client.wallet.index') }}" class="text-xs hover:underline font-medium" style="color: var(--accent);">+ Add funds</a>
                </div>
                @endauth

                <x-currency-selector />
                <x-theme-toggle />

                <a href="{{ route('home') }}" target="_blank" class="cf-btn cf-btn-ghost cf-btn-sm text-xs">
                    Storefront
                </a>
            </div>
        </header>

        <!-- Page Body -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8" style="background-color: var(--bg);">
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

</body>
</html>
