<!DOCTYPE html>
<html lang="en" class="h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'DomainReseller') }} — Wholesale Domain Registration</title>
    <meta name="description" content="Register domains at wholesale prices with multi-registrar failover. Pay in BDT or USD via bKash, Stripe, or Binance Pay.">

    <!-- Anti-flash inline theme loader -->
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

    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Design System Tokens -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') . '?v=' . @filemtime(public_path('css/design-system.css')) }}">

    <!-- Tailwind CSS CDN -->
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

    <!-- Alpine.js & Global Scripts -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="{{ asset('js/theme.js') }}"></script>
    <script defer src="{{ asset('js/currency.js') }}"></script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
    </style>

    @stack('styles')
</head>
<body class="flex flex-col min-h-screen" x-data="{ mobileMenuOpen: false }">

    <!-- HEADER: 64px, bottom border on scroll, Cloudflare minimal -->
    <header class="sticky top-0 z-40 bg-white/95 dark:bg-[#0F1115]/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 transition-colors"
            x-data="{ domainsOpen: false, resourcesOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <!-- Logo Left -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <x-brand-logo class="h-8 w-auto" :show-text="false" />
                    </a>

                    <!-- Navigation Links -->
                    <nav class="hidden lg:flex items-center space-x-1 text-[13px] font-medium" style="color: var(--text-muted);">

                        <!-- Domains Dropdown -->
                        <div class="relative" @click.away="domainsOpen = false">
                            <button @click="domainsOpen = !domainsOpen"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-[4px] hover:text-[var(--text)] transition {{ request()->routeIs('domain.*') || request()->routeIs('pricing') || request()->routeIs('whois.*') ? 'font-semibold' : '' }}"
                                    :style="domainsOpen ? 'background-color: var(--surface); color: var(--text);' : ''">
                                <span>Domains</span>
                                <svg class="w-3.5 h-3.5 opacity-70 transition-transform duration-150" :class="domainsOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div x-show="domainsOpen"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-0 mt-2 w-64 rounded-xl bg-white dark:bg-[#171A21] border border-slate-200 dark:border-slate-800 shadow-2xl p-1.5 z-50 text-[13px] ring-1 ring-black/5">
                                <a href="{{ route('domain.search') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors">
                                    <span class="font-semibold block text-slate-900 dark:text-white">Search</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Find single or bulk domain names</span>
                                </a>
<a href="{{ route('domain.transfer') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors">
                                    <span class="font-semibold block text-slate-900 dark:text-white">Transfer</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Migrate existing domains with seamless renewal</span>
                                </a>
                                <a href="{{ route('pricing') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors">
                                    <span class="font-semibold block text-slate-900 dark:text-white">Pricing</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Wholesale registration and renewal matrix</span>
                                </a>
                                <a href="{{ route('whois.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors">
                                    <span class="font-semibold block text-slate-900 dark:text-white">WHOIS</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Inspect registration records and DNS</span>
                                </a>
                            </div>
                        </div>

                        <!-- Resellers Direct Link -->
                        <a href="{{ route('reseller.program') }}"
                           class="px-3 py-1.5 rounded-[4px] hover:text-[var(--text)] transition {{ request()->routeIs('reseller.program') ? 'font-semibold' : '' }}">
                            Resellers
                        </a>

                        <!-- Resources Dropdown -->
                        <div class="relative" @click.away="resourcesOpen = false">
                            <button @click="resourcesOpen = !resourcesOpen"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-[4px] hover:text-[var(--text)] transition {{ request()->routeIs('announcements.*') || request()->routeIs('knowledgebase.*') || request()->routeIs('contact') ? 'font-semibold' : '' }}"
                                    :style="resourcesOpen ? 'background-color: var(--surface); color: var(--text);' : ''">
                                <span>Resources</span>
                                <svg class="w-3.5 h-3.5 opacity-70 transition-transform duration-150" :class="resourcesOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div x-show="resourcesOpen"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-0 mt-2 w-64 rounded-xl bg-white dark:bg-[#171A21] border border-slate-200 dark:border-slate-800 shadow-2xl p-1.5 z-50 text-[13px] ring-1 ring-black/5">
                                <a href="{{ route('announcements.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors">
                                    <span class="font-semibold block text-slate-900 dark:text-white">Announcements</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Platform updates and promo releases</span>
                                </a>
                                <a href="{{ route('knowledgebase.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors">
                                    <span class="font-semibold block text-slate-900 dark:text-white">Knowledge base</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Guides, tutorials and API references</span>
                                </a>
                                <a href="{{ route('contact') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors">
                                    <span class="font-semibold block text-slate-900 dark:text-white">Contact</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Support desk, email, and live help</span>
                                </a>
                            </div>
                        </div>

                    </nav>
                </div>

                <!-- Right: Currency + Theme Toggle + Cart + Auth Actions -->
                <div class="hidden lg:flex items-center gap-3">
                    <!-- Currency Segmented Control (USD | BDT) -->
                    <x-currency-selector />

                    <!-- Theme Toggle (Light / Dark / System) -->
                    <x-theme-toggle />

                    <!-- Cart Icon Button -->
                    @php
                        $cartItemsCount = count(session('cart', []));
                    @endphp
                    <a href="{{ route('cart.index') }}"
                       class="relative inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition shadow-sm group"
                       title="View shopping cart">
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span id="header-cart-badge"
                              class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-extrabold bg-[#F6821F] text-white rounded-full {{ $cartItemsCount > 0 ? '' : 'hidden' }}">
                            {{ $cartItemsCount }}
                        </span>
                    </a>

                    @auth
                        <!-- Authenticated User Dropdown -->
                        <div class="relative" x-data="{ userOpen: false }" @click.away="userOpen = false">
                            <button @click="userOpen = !userOpen"
                                    type="button"
                                    class="inline-flex items-center gap-2 px-2.5 py-1.5 h-8 rounded-[6px] text-[13px] font-medium transition"
                                    style="border: 1px solid var(--border); color: var(--text);">
                                <span>{{ auth()->user()->name }}</span>
                                <span class="text-[11px]" style="color: var(--text-muted);">({{ auth()->user()->role }})</span>
                                <svg class="w-3.5 h-3.5" style="color: var(--text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div x-show="userOpen"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-1.5 w-48 rounded-[6px] shadow-dropdown py-1 z-50 text-[13px]"
                                 style="background-color: var(--bg); border: 1px solid var(--border);">
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-1.5 hover:bg-[var(--surface)]" style="color: var(--text);">Admin dashboard</a>
                                @elseif(auth()->user()->role === 'reseller')
                                    <a href="{{ route('reseller.dashboard') }}" class="block px-3 py-1.5 hover:bg-[var(--surface)]" style="color: var(--text);">Reseller portal</a>
                                @else
                                    <a href="{{ route('client.dashboard') }}" class="block px-3 py-1.5 hover:bg-[var(--surface)]" style="color: var(--text);">Client dashboard</a>
                                @endif
                                <div class="my-1 border-t" style="border-color: var(--border);"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-1.5 text-[#DC2626] hover:bg-[var(--danger-bg)]">
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest Auth Buttons -->
                        <a href="{{ route('login') }}" class="cf-btn cf-btn-ghost cf-btn-sm" style="color: var(--text);">
                            Sign in
                        </a>
                        <a href="{{ route('register') }}" class="cf-btn cf-btn-primary cf-btn-sm">
                            Get started
                        </a>
                    @endauth
                </div>

                <!-- Mobile Header Right Actions -->
                <div class="flex items-center gap-2 lg:hidden">
                    <a href="{{ route('cart.index') }}"
                       class="relative inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300"
                       title="Shopping Cart">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span id="mobile-cart-badge"
                              class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 text-[9px] font-bold bg-[#F6821F] text-white rounded-full flex items-center justify-center {{ $cartItemsCount > 0 ? '' : 'hidden' }}">
                            {{ $cartItemsCount }}
                        </span>
                    </a>
                    <button @click="mobileMenuOpen = true"
                            type="button"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 transition"
                            title="Open menu">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        
    </header>

    <!-- Main Content Area -->
    <main class="flex-1">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div class="p-3 rounded-[6px] text-xs flex items-center justify-between"
                     style="background-color: var(--success-bg); border: 1px solid var(--success); color: var(--success);">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="hover:opacity-75">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div class="p-3 rounded-[6px] text-xs flex items-center justify-between"
                     style="background-color: var(--danger-bg); border: 1px solid var(--danger); color: var(--danger);">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#DC2626]"></span>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="hover:opacity-75">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- FOOTER: Simple, 4 columns max, small gray text, no big badges -->
    <footer class="pt-12 pb-8 text-[13px] border-t"
            style="background-color: var(--surface); border-color: var(--border); color: var(--text-muted);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 pb-10 border-b" style="border-color: var(--border);">

                <!-- Col 1: Platform Summary & Support -->
                <div class="space-y-3">
                    <div class="mb-3">
                        <x-brand-logo class="h-7 w-auto" :show-text="false" />
                    </div>
                    <p class="text-[12px] leading-relaxed">
                        Wholesale domain registration platform connecting upstream registries with dual USD and BDT billing.
                    </p>
                    <div class="text-[12px] space-y-1">
                        <div>Support: <a href="mailto:{{ config('app.support_email', config('mail.from.address', 'support@hostnibo.com')) }}" class="hover:underline font-medium" style="color: var(--text);">{{ config('app.support_email', config('mail.from.address', 'support@hostnibo.com')) }}</a></div>
                        <div>WhatsApp: <span class="font-mono font-medium" style="color: var(--text);">{{ config('app.support_whatsapp', '+880 1700-000000') }}</span></div>
                    </div>
                </div>

                <!-- Col 2: Domains -->
                <div>
                    <h3 class="text-[12px] font-semibold uppercase tracking-wider mb-3" style="color: var(--text);">Domains</h3>
                    <ul class="space-y-2 text-[12px]">
                        <li><a href="{{ route('domain.search') }}" class="hover:text-[var(--text)] transition">Domain search</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-[var(--text)] transition">TLD wholesale pricing</a></li>
                        <li><a href="{{ route('whois.index') }}" class="hover:text-[var(--text)] transition">WHOIS lookup</a></li>
                        <li><a href="{{ route('domain.search') }}?tld=.com" class="hover:text-[var(--text)] transition">Register .com domain</a></li>
                        <li><a href="{{ route('domain.search') }}?tld=.net" class="hover:text-[var(--text)] transition">Register .net domain</a></li>
                    </ul>
                </div>

                <!-- Col 3: Resellers & API -->
                <div>
                    <h3 class="text-[12px] font-semibold uppercase tracking-wider mb-3" style="color: var(--text);">Resellers</h3>
                    <ul class="space-y-2 text-[12px]">
                        <li><a href="{{ route('reseller.program') }}" class="hover:text-[var(--text)] transition">Wholesale program</a></li>
                        <li><a href="{{ route('reseller.program') }}#whmcs" class="hover:text-[var(--text)] transition">WHMCS registrar module</a></li>
                        <li><a href="{{ route('reseller.program') }}#api" class="hover:text-[var(--text)] transition">REST API documentation</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-[var(--text)] transition">Create reseller account</a></li>
                    </ul>
                </div>

                <!-- Col 4: Resources & Legal -->
                <div>
                    <h3 class="text-[12px] font-semibold uppercase tracking-wider mb-3" style="color: var(--text);">Resources</h3>
                    <ul class="space-y-2 text-[12px]">
                        <li><a href="{{ route('knowledgebase.index') }}" class="hover:text-[var(--text)] transition">Knowledge base</a></li>
                        <li><a href="{{ route('announcements.index') }}" class="hover:text-[var(--text)] transition">Announcements</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-[var(--text)] transition">Contact support</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-[var(--text)] transition">Terms of service</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-[var(--text)] transition">Privacy policy</a></li>
                    </ul>
                </div>

            </div>

            <!-- Payment Methods & Status Line with real SVG logos -->
            <div class="py-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-b text-[12px]" style="border-color: var(--border);">
                <!-- Payment Methods with Real Logos -->
                <div class="flex items-center gap-4 flex-wrap">
                    <span style="color: var(--text-muted);">Payment methods:</span>

                    <!-- bKash Logo Badge -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] font-medium text-xs" style="background-color: var(--bg); border: 1px solid var(--border); color: var(--text);">
                        <svg class="w-4 h-4 text-[#E2136E]" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L3 7v10l9 5 9-5V7l-9-5zm0 2.2l6.8 3.8-2.9 4-3.9-2.2v-5.6zm-1.5 5.6l3.9 2.2-3.9 5.5-3.9-5.5 3.9-2.2z"/>
                        </svg>
                        <span>bKash</span>
                    </span>

                    <!-- Stripe Logo Badge -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] font-medium text-xs" style="background-color: var(--bg); border: 1px solid var(--border); color: var(--text);">
                        <svg class="w-4 h-4 text-[#635BFF]" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.97 15.754.5 12.52.5 6.643.5 2.66 3.657 2.66 8.643c0 7.842 10.778 6.61 10.778 10.01 0 1.053-.923 1.517-2.227 1.517-2.645 0-5.474-1.108-7.382-2.099L2.83 23.543C4.78 24.502 7.87 25 11.173 25c6.07 0 10.168-3.007 10.168-8.083 0-8.243-10.778-6.966-10.778-10.01 0-.962.825-1.44 2.05-1.44 1.83 0 3.96.678 5.363 1.44z"/>
                        </svg>
                        <span>Stripe</span>
                    </span>

                    <!-- Binance Pay Logo Badge -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] font-medium text-xs" style="background-color: var(--bg); border: 1px solid var(--border); color: var(--text);">
                        <svg class="w-4 h-4 text-[#F3BA2F]" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0l-3.35 3.35L12 6.7l3.35-3.35L12 0zm-7.65 7.65L1 11l3.35 3.35L7.7 11 4.35 7.65zm15.3 0L16.3 11l3.35 3.35L23 11l-3.35-3.35zM12 7.65L8.65 11 12 14.35 15.35 11 12 7.65zM4.35 16.35L1 19.7l3.35 3.35 3.35-3.35-3.35-3.35zm15.3 0l-3.35 3.35 3.35 3.35 3.35-3.35-3.35-3.35zM12 17.3l-3.35 3.35L12 24l3.35-3.35L12 17.3z"/>
                        </svg>
                        <span>Binance Pay</span>
                    </span>

                    <!-- Bank Transfer Badge -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] font-medium text-xs" style="background-color: var(--bg); border: 1px solid var(--border); color: var(--text);">
                        <svg class="w-4 h-4" style="color: var(--text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5M4.5 21V10.5M3 21h18M4.5 10.5h15" />
                        </svg>
                        <span>Bank transfer</span>
                    </span>
                </div>

                <!-- Public Status Link -->
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                    <span class="font-medium" style="color: var(--text);">All registry nodes operational</span>
                </div>
            </div>

            <!-- Bottom Line: Legal & Copyright -->
            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[12px]" style="color: var(--text-muted);">
                <p>© {{ date('Y') }} {{ config('app.name', 'DomainReseller') }}. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('terms') }}" class="hover:text-[var(--text)] transition">Terms</a>
                    <a href="{{ route('privacy') }}" class="hover:text-[var(--text)] transition">Privacy</a>
                    <a href="{{ route('contact') }}" class="hover:text-[var(--text)] transition">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Drawer Menu -->
        <div x-show="mobileMenuOpen"
             x-cloak
             class="fixed inset-0 z-50 lg:hidden"
             role="dialog"
             aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="mobileMenuOpen = false"></div>
            <div class="fixed inset-y-0 right-0 w-full max-w-xs p-5 shadow-2xl flex flex-col justify-between bg-white dark:bg-[#151921] border-l border-slate-200 dark:border-slate-800"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                <div>
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-[5px] bg-[#F6821F] flex items-center justify-center text-white shrink-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/>
                                </svg>
                            </div>
                            <span class="font-bold text-sm text-slate-900 dark:text-white">{{ config('app.name', 'DomainReseller') }}</span>
                        </div>
                        <button @click="mobileMenuOpen = false" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Preferences in Mobile Menu -->
                    <div class="flex items-center justify-between py-3 border-b border-slate-200 dark:border-slate-800">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Preferences</span>
                        <div class="flex items-center gap-2">
                            <x-currency-selector />
                            <x-theme-toggle />
                        </div>
                    </div>

                    <nav class="mt-4 space-y-1">
                        <a href="{{ route('domain.search') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <span>Domain search</span>
                            <span class="text-xs text-slate-400">&rarr;</span>
                        </a>
                        <a href="{{ route('pricing') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <span>Pricing matrix</span>
                            <span class="text-xs text-slate-400">&rarr;</span>
                        </a>
                        <a href="{{ route('whois.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <span>WHOIS lookup</span>
                            <span class="text-xs text-slate-400">&rarr;</span>
                        </a>
                        <a href="{{ route('reseller.program') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <span>Resellers</span>
                            <span class="text-xs text-[#F6821F] font-bold">WHMCS</span>
                        </a>
                        <a href="{{ route('announcements.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <span>Announcements</span>
                        </a>
                        <a href="{{ route('knowledgebase.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <span>Knowledge base</span>
                        </a>
                        <a href="{{ route('contact') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <span>Contact support</span>
                        </a>
                    </nav>
                </div>

                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-2">
                    @auth
                        <a href="{{ route('client.dashboard') }}" class="cf-btn cf-btn-secondary w-full text-center">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="cf-btn cf-btn-danger w-full mt-1">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="cf-btn cf-btn-secondary w-full text-center">Sign in</a>
                        <a href="{{ route('register') }}" class="cf-btn cf-btn-primary w-full text-center">Get started</a>
                    @endauth
                </div>
            </div>
        </div>


    @stack('scripts')
</body>
</html>
