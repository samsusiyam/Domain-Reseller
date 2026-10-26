<!DOCTYPE html>
<html lang="en" class="h-full bg-white text-[#111827] antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'DomainReseller') }} — Wholesale Domain Registration</title>
    <meta name="description" content="Register domains at wholesale prices with multi-registrar failover. Pay in BDT or USD via bKash, Stripe, or Binance Pay.">

    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Design System Tokens -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
                            bg: '#FFFFFF',
                            surface: '#F7F7F8',
                            hover: '#F3F4F6',
                            border: '#E5E7EB',
                            'border-hover': '#D1D5DB',
                            text: '#111827',
                            muted: '#6B7280',
                            dim: '#9CA3AF',
                        },
                        status: {
                            success: '#16A34A',
                            warning: '#D97706',
                            danger: '#DC2626',
                            info: '#2563EB',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    borderRadius: {
                        sm: '4px',
                        DEFAULT: '6px',
                        md: '6px',
                        lg: '8px',
                    },
                    boxShadow: {
                        dropdown: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.05)',
                        modal: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05)',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; font-size: 14px; line-height: 1.5; color: #111827; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #9CA3AF; }
    </style>

    @stack('styles')
</head>
<body class="flex flex-col min-h-screen bg-white text-[#111827]">

    <!-- HEADER: 64px, white, bottom border -->
    <header class="sticky top-0 z-40 bg-white border-b border-[#E5E7EB] transition-colors"
            x-data="{ mobileMenuOpen: false, domainsOpen: false, resourcesOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <!-- Logo Left -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-[6px] bg-[#F6821F] flex items-center justify-center text-white shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-[16px] text-[#111827] tracking-tight">
                            {{ config('app.name', 'DomainReseller') }}
                        </span>
                    </a>

                    <!-- Navigation Links -->
                    <nav class="hidden lg:flex items-center space-x-1 text-[13px] font-medium text-[#6B7280]">

                        <!-- Domains Dropdown -->
                        <div class="relative" @click.away="domainsOpen = false">
                            <button @click="domainsOpen = !domainsOpen"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-[4px] hover:text-[#111827] hover:bg-[#F7F7F8] transition {{ request()->routeIs('domain.*') || request()->routeIs('pricing') || request()->routeIs('whois.*') ? 'text-[#111827] font-semibold' : '' }}">
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
                                 class="absolute left-0 mt-1.5 w-56 bg-white rounded-[6px] border border-[#E5E7EB] shadow-dropdown py-1 z-50 text-[13px]">
                                <a href="{{ route('domain.search') }}" class="block px-3 py-2 text-[#111827] hover:bg-[#F7F7F8] transition">
                                    <span class="font-medium block">Search</span>
                                    <span class="text-[11px] text-[#6B7280]">Find single or bulk domain names</span>
                                </a>
                                <a href="{{ route('pricing') }}" class="block px-3 py-2 text-[#111827] hover:bg-[#F7F7F8] transition">
                                    <span class="font-medium block">Pricing</span>
                                    <span class="text-[11px] text-[#6B7280]">Wholesale registration and renewal matrix</span>
                                </a>
                                <a href="{{ route('whois.index') }}" class="block px-3 py-2 text-[#111827] hover:bg-[#F7F7F8] transition">
                                    <span class="font-medium block">WHOIS</span>
                                    <span class="text-[11px] text-[#6B7280]">Inspect registration records and DNS</span>
                                </a>
                            </div>
                        </div>

                        <!-- Resellers Direct Link -->
                        <a href="{{ route('reseller.program') }}"
                           class="px-3 py-1.5 rounded-[4px] hover:text-[#111827] hover:bg-[#F7F7F8] transition {{ request()->routeIs('reseller.program') ? 'text-[#111827] font-semibold' : '' }}">
                            Resellers
                        </a>

                        <!-- Resources Dropdown -->
                        <div class="relative" @click.away="resourcesOpen = false">
                            <button @click="resourcesOpen = !resourcesOpen"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-[4px] hover:text-[#111827] hover:bg-[#F7F7F8] transition {{ request()->routeIs('announcements.*') || request()->routeIs('knowledgebase.*') || request()->routeIs('contact') ? 'text-[#111827] font-semibold' : '' }}">
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
                                 class="absolute left-0 mt-1.5 w-56 bg-white rounded-[6px] border border-[#E5E7EB] shadow-dropdown py-1 z-50 text-[13px]">
                                <a href="{{ route('announcements.index') }}" class="block px-3 py-2 text-[#111827] hover:bg-[#F7F7F8] transition">
                                    <span class="font-medium block">Announcements</span>
                                    <span class="text-[11px] text-[#6B7280]">Platform updates and promo releases</span>
                                </a>
                                <a href="{{ route('knowledgebase.index') }}" class="block px-3 py-2 text-[#111827] hover:bg-[#F7F7F8] transition">
                                    <span class="font-medium block">Knowledge base</span>
                                    <span class="text-[11px] text-[#6B7280]">Guides, tutorials and API references</span>
                                </a>
                                <a href="{{ route('contact') }}" class="block px-3 py-2 text-[#111827] hover:bg-[#F7F7F8] transition">
                                    <span class="font-medium block">Contact</span>
                                    <span class="text-[11px] text-[#6B7280]">Support desk, email, and live help</span>
                                </a>
                            </div>
                        </div>

                    </nav>
                </div>

                <!-- Right: Currency Toggle + Auth Actions -->
                <div class="hidden lg:flex items-center gap-3">
                    <!-- Currency Segmented Control (USD | BDT) -->
                    <x-currency-selector />

                    @auth
                        <!-- Authenticated User Dropdown -->
                        <div class="relative" x-data="{ userOpen: false }" @click.away="userOpen = false">
                            <button @click="userOpen = !userOpen"
                                    type="button"
                                    class="inline-flex items-center gap-2 px-2.5 py-1.5 h-8 rounded-[6px] border border-[#E5E7EB] hover:bg-[#F7F7F8] text-[13px] text-[#111827] font-medium transition">
                                <span>{{ auth()->user()->name }}</span>
                                <span class="text-[11px] text-[#6B7280] uppercase">({{ auth()->user()->role }})</span>
                                <svg class="w-3.5 h-3.5 text-[#6B7280]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
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
                                 class="absolute right-0 mt-1.5 w-48 bg-white rounded-[6px] border border-[#E5E7EB] shadow-dropdown py-1 z-50 text-[13px]">
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-1.5 text-[#111827] hover:bg-[#F7F7F8]">Admin dashboard</a>
                                @elseif(auth()->user()->role === 'reseller')
                                    <a href="{{ route('reseller.dashboard') }}" class="block px-3 py-1.5 text-[#111827] hover:bg-[#F7F7F8]">Reseller portal</a>
                                @else
                                    <a href="{{ route('client.dashboard') }}" class="block px-3 py-1.5 text-[#111827] hover:bg-[#F7F7F8]">Client dashboard</a>
                                @endif
                                <div class="border-t border-[#E5E7EB] my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-1.5 text-[#DC2626] hover:bg-[#FEF2F2]">
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest Auth Buttons -->
                        <a href="{{ route('login') }}" class="cf-btn cf-btn-ghost cf-btn-sm text-[#111827]">
                            Sign in
                        </a>
                        <a href="{{ route('register') }}" class="cf-btn cf-btn-primary cf-btn-sm">
                            Get started
                        </a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex items-center gap-2 lg:hidden">
                    <x-currency-selector />
                    <button @click="mobileMenuOpen = true"
                            type="button"
                            class="p-2 text-[#6B7280] hover:text-[#111827] rounded-[4px] hover:bg-[#F7F7F8] transition"
                            title="Open menu">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Menu Full-Height Drawer -->
        <div x-show="mobileMenuOpen"
             x-cloak
             class="fixed inset-0 z-50 lg:hidden"
             role="dialog"
             aria-modal="true">

            <!-- Backdrop -->
            <div x-show="mobileMenuOpen"
                 x-transition:enter="ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobileMenuOpen = false"
                 class="fixed inset-0 bg-[#111827]/40"></div>

            <!-- Drawer Panel -->
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transform transition ease-out duration-150"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in duration-100"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="fixed inset-y-0 right-0 w-full max-w-xs bg-white border-l border-[#E5E7EB] shadow-modal flex flex-col p-5 text-[14px]">

                <div class="flex items-center justify-between pb-4 border-b border-[#E5E7EB]">
                    <span class="font-semibold text-[#111827]">Menu</span>
                    <button @click="mobileMenuOpen = false" type="button" class="p-1.5 text-[#6B7280] hover:text-[#111827] rounded-[4px]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto py-4 space-y-3">
                    <div class="space-y-1">
                        <div class="text-[11px] font-medium text-[#6B7280] uppercase tracking-wider">Domains</div>
                        <a href="{{ route('domain.search') }}" class="block px-2 py-1.5 text-[#111827] hover:bg-[#F7F7F8] rounded-[4px]">Domain search</a>
                        <a href="{{ route('pricing') }}" class="block px-2 py-1.5 text-[#111827] hover:bg-[#F7F7F8] rounded-[4px]">TLD pricing</a>
                        <a href="{{ route('whois.index') }}" class="block px-2 py-1.5 text-[#111827] hover:bg-[#F7F7F8] rounded-[4px]">WHOIS lookup</a>
                    </div>

                    <div class="border-t border-[#E5E7EB] pt-3 space-y-1">
                        <div class="text-[11px] font-medium text-[#6B7280] uppercase tracking-wider">Partners</div>
                        <a href="{{ route('reseller.program') }}" class="block px-2 py-1.5 text-[#111827] hover:bg-[#F7F7F8] rounded-[4px]">Reseller program</a>
                    </div>

                    <div class="border-t border-[#E5E7EB] pt-3 space-y-1">
                        <div class="text-[11px] font-medium text-[#6B7280] uppercase tracking-wider">Resources</div>
                        <a href="{{ route('announcements.index') }}" class="block px-2 py-1.5 text-[#111827] hover:bg-[#F7F7F8] rounded-[4px]">Announcements</a>
                        <a href="{{ route('knowledgebase.index') }}" class="block px-2 py-1.5 text-[#111827] hover:bg-[#F7F7F8] rounded-[4px]">Knowledge base</a>
                        <a href="{{ route('contact') }}" class="block px-2 py-1.5 text-[#111827] hover:bg-[#F7F7F8] rounded-[4px]">Contact support</a>
                    </div>
                </div>

                <div class="pt-4 border-t border-[#E5E7EB] space-y-2">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="cf-btn cf-btn-secondary cf-btn-md w-full text-center">Admin dashboard</a>
                        @elseif(auth()->user()->role === 'reseller')
                            <a href="{{ route('reseller.dashboard') }}" class="cf-btn cf-btn-secondary cf-btn-md w-full text-center">Reseller portal</a>
                        @else
                            <a href="{{ route('client.dashboard') }}" class="cf-btn cf-btn-secondary cf-btn-md w-full text-center">Client dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="cf-btn cf-btn-secondary cf-btn-md w-full text-center">Sign in</a>
                        <a href="{{ route('register') }}" class="cf-btn cf-btn-primary cf-btn-md w-full text-center">Get started</a>
                    @endauth
                </div>

            </div>
        </div>
    </header>

    <!-- MAIN BODY -->
    <main class="flex-1">
        <!-- Toast Alerts -->
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3 bg-[#F0FDF4] border border-[#BBF7D0] rounded-[6px] text-[13px] text-[#166534] flex items-center justify-between" role="alert">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-[#166534] hover:opacity-75">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3 bg-[#FEF2F2] border border-[#FECACA] rounded-[6px] text-[13px] text-[#991B1B] flex items-center justify-between" role="alert">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#DC2626]"></span>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-[#991B1B] hover:opacity-75">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- FOOTER: Simple, 4 columns max, small gray text, no big badges -->
    <footer class="bg-white border-t border-[#E5E7EB] pt-12 pb-8 text-[13px] text-[#6B7280]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 pb-10 border-b border-[#E5E7EB]">

                <!-- Col 1: Platform Summary & Support -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded bg-[#F6821F] flex items-center justify-center text-white text-[11px] font-bold">
                            DR
                        </div>
                        <span class="font-semibold text-[14px] text-[#111827]">{{ config('app.name', 'DomainReseller') }}</span>
                    </div>
                    <p class="text-[12px] leading-relaxed">
                        Wholesale domain registration platform connecting upstream registries with dual USD and BDT billing.
                    </p>
                    <div class="text-[12px] space-y-1">
                        <div>Support: <a href="mailto:support@domainreseller.local" class="text-[#111827] hover:underline">support@domainreseller.local</a></div>
                        <div>WhatsApp: <span class="text-[#111827] font-mono">+880 1700-000000</span></div>
                    </div>
                </div>

                <!-- Col 2: Domains -->
                <div>
                    <h3 class="text-[12px] font-semibold text-[#111827] uppercase tracking-wider mb-3">Domains</h3>
                    <ul class="space-y-2 text-[12px]">
                        <li><a href="{{ route('domain.search') }}" class="hover:text-[#111827] transition">Domain search</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-[#111827] transition">TLD wholesale pricing</a></li>
                        <li><a href="{{ route('whois.index') }}" class="hover:text-[#111827] transition">WHOIS lookup</a></li>
                        <li><a href="{{ route('domain.search') }}?tld=.com" class="hover:text-[#111827] transition">Register .com domain</a></li>
                        <li><a href="{{ route('domain.search') }}?tld=.net" class="hover:text-[#111827] transition">Register .net domain</a></li>
                    </ul>
                </div>

                <!-- Col 3: Resellers & API -->
                <div>
                    <h3 class="text-[12px] font-semibold text-[#111827] uppercase tracking-wider mb-3">Resellers</h3>
                    <ul class="space-y-2 text-[12px]">
                        <li><a href="{{ route('reseller.program') }}" class="hover:text-[#111827] transition">Wholesale program</a></li>
                        <li><a href="{{ route('reseller.program') }}#whmcs" class="hover:text-[#111827] transition">WHMCS registrar module</a></li>
                        <li><a href="{{ route('reseller.program') }}#api" class="hover:text-[#111827] transition">REST API documentation</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-[#111827] transition">Create reseller account</a></li>
                    </ul>
                </div>

                <!-- Col 4: Resources & Trust -->
                <div>
                    <h3 class="text-[12px] font-semibold text-[#111827] uppercase tracking-wider mb-3">Resources & Legal</h3>
                    <ul class="space-y-2 text-[12px]">
                        <li><a href="{{ route('knowledgebase.index') }}" class="hover:text-[#111827] transition">Knowledge base</a></li>
                        <li><a href="{{ route('announcements.index') }}" class="hover:text-[#111827] transition">Announcements</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-[#111827] transition">Contact support</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-[#111827] transition">Terms of service</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-[#111827] transition">Privacy policy</a></li>
                    </ul>
                </div>

            </div>

            <!-- Payment Methods & Status Line -->
            <div class="py-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-[#E5E7EB] text-[12px]">
                <!-- Payment Methods -->
                <div class="flex items-center gap-3">
                    <span class="text-[#6B7280]">Payment methods:</span>
                    <span class="font-medium text-[#111827] bg-[#F7F7F8] border border-[#E5E7EB] px-2 py-0.5 rounded-[4px]">bKash</span>
                    <span class="font-medium text-[#111827] bg-[#F7F7F8] border border-[#E5E7EB] px-2 py-0.5 rounded-[4px]">Stripe</span>
                    <span class="font-medium text-[#111827] bg-[#F7F7F8] border border-[#E5E7EB] px-2 py-0.5 rounded-[4px]">Binance Pay</span>
                    <span class="font-medium text-[#111827] bg-[#F7F7F8] border border-[#E5E7EB] px-2 py-0.5 rounded-[4px]">Bank transfer</span>
                </div>

                <!-- Public Status Link -->
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                    <span class="text-[#111827] font-medium">All systems operational</span>
                </div>
            </div>

            <!-- Bottom Line: Legal & Copyright -->
            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[12px] text-[#6B7280]">
                <p>© {{ date('Y') }} {{ config('app.name', 'DomainReseller') }}. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('terms') }}" class="hover:text-[#111827] transition">Terms</a>
                    <a href="{{ route('privacy') }}" class="hover:text-[#111827] transition">Privacy</a>
                    <a href="{{ route('contact') }}" class="hover:text-[#111827] transition">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
