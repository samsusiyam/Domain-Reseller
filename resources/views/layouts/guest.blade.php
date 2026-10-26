<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50 text-slate-900 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'DomainReseller') }} — Professional Domain Registration & Reseller Platform</title>
    <meta name="description" content="Register, transfer, and resell domain names with wholesale pricing, automated WHMCS REST API, and multi-currency billing in USD & BDT.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN Fallback for shared hosting -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-slate-50 font-sans selection:bg-indigo-500 selection:text-white">

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18">
                <!-- Brand Logo -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-bold text-xl text-slate-900 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 group-hover:scale-105 transition">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </div>
                        <span class="tracking-tight text-slate-900">{{ config('app.name', 'DomainReseller') }}</span>
                    </a>

                    <!-- Desktop Nav Links -->
                    <nav class="hidden md:flex items-center space-x-6 text-sm font-semibold text-slate-600">
                        <a href="{{ route('home') }}" class="hover:text-indigo-600 transition {{ request()->routeIs('home') ? 'text-indigo-600' : '' }}">Home</a>
                        <a href="{{ route('domain.search') }}" class="hover:text-indigo-600 transition {{ request()->routeIs('domain.search') ? 'text-indigo-600' : '' }}">Domain Search</a>
                        <a href="{{ route('pricing') }}" class="hover:text-indigo-600 transition {{ request()->routeIs('pricing') ? 'text-indigo-600' : '' }}">Pricing</a>
                        <a href="{{ route('reseller.program') }}" class="hover:text-indigo-600 transition {{ request()->routeIs('reseller.program') ? 'text-indigo-600' : '' }}">Reseller Program</a>
                        <a href="{{ route('whois.index') }}" class="hover:text-indigo-600 transition {{ request()->routeIs('whois.*') ? 'text-indigo-600' : '' }}">WHOIS Lookup</a>
                        <a href="{{ route('announcements.index') }}" class="hover:text-indigo-600 transition">News</a>
                        <a href="{{ route('knowledgebase.index') }}" class="hover:text-indigo-600 transition">Knowledgebase</a>
                    </nav>
                </div>

                <!-- Right Action Buttons & Currency Selector -->
                <div class="hidden lg:flex items-center gap-4">
                    <x-currency-selector />

                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                                Admin Dashboard
                            </a>
                        @elseif(auth()->user()->role === 'reseller')
                            <a href="{{ route('reseller.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                                Reseller Portal
                            </a>
                        @else
                            <a href="{{ route('client.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                                Client Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-indigo-600 px-3 py-2 transition">Sign In</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition">
                            Get Started
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center gap-2 md:hidden">
                    <x-currency-selector />
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-b border-slate-200 bg-white px-4 pt-2 pb-4 space-y-2">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-slate-800 hover:bg-slate-50">Home</a>
            <a href="{{ route('domain.search') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-slate-800 hover:bg-slate-50">Domain Search</a>
            <a href="{{ route('pricing') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-slate-800 hover:bg-slate-50">Pricing</a>
            <a href="{{ route('reseller.program') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-slate-800 hover:bg-slate-50">Reseller Program</a>
            <a href="{{ route('whois.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-slate-800 hover:bg-slate-50">WHOIS Lookup</a>
            <a href="{{ route('knowledgebase.index') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-slate-800 hover:bg-slate-50">Knowledgebase</a>
            <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                @auth
                    <a href="{{ route('login') }}" class="w-full text-center px-4 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl text-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center px-4 py-2 border border-slate-300 font-semibold rounded-xl text-sm text-slate-700">Sign In</a>
                    <a href="{{ route('register') }}" class="w-full text-center px-4 py-2 bg-indigo-600 text-white font-semibold rounded-xl text-sm">Register</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <x-alert type="success" :message="session('success')" />
            </div>
        @endif
        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <x-alert type="error" :message="session('error')" />
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 border-t border-slate-800 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8">
                <!-- Col 1: About & Logo -->
                <div class="col-span-2">
                    <div class="flex items-center gap-2.5 text-white font-bold text-xl mb-4">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </div>
                        <span>{{ config('app.name', 'DomainReseller') }}</span>
                    </div>
                    <p class="text-sm leading-relaxed text-slate-400 mb-6 max-w-sm">
                        Enterprise-grade domain registration and reseller infrastructure. Empowering hosting providers and web agencies with automated domain provisioning and robust WHMCS integration.
                    </p>
                    <div class="flex items-center gap-3 text-xs font-semibold text-slate-400">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-800 text-slate-300">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Systems 99.99% Up
                        </span>
                        <span class="px-2.5 py-1 rounded bg-slate-800 text-slate-300">USD & BDT Accepted</span>
                    </div>
                </div>

                <!-- Col 2: Services -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Domains</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('domain.search') }}" class="hover:text-white transition">Domain Search</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-white transition">TLD Pricing</a></li>
                        <li><a href="{{ route('whois.index') }}" class="hover:text-white transition">WHOIS Search</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Domain Transfer</a></li>
                    </ul>
                </div>

                <!-- Col 3: Resellers -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Resellers</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('reseller.program') }}" class="hover:text-white transition">Reseller Program</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">WHMCS Module</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">REST API Docs</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Join as Reseller</a></li>
                    </ul>
                </div>

                <!-- Col 4: Support & Legal -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Support & Legal</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('knowledgebase.index') }}" class="hover:text-white transition">Knowledgebase</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact Us</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-white transition">Terms of Service</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-white transition">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Subfooter -->
            <div class="border-t border-slate-800 mt-12 pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'DomainReseller') }}. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <span>Payment Methods: bKash • Stripe • Binance Pay • Bank Wire</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
