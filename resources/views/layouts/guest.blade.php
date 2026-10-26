<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100 antialiased selection:bg-indigo-500 selection:text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'DomainReseller') }} — Professional Domain Registration & Reseller Platform</title>
    <meta name="description" content="Wholesale domain registration and automated WHMCS REST API platform with Namecheap, Spaceship, Dynadot, AtakDomain, NameSilo, and dual USD/BDT billing.">

    <!-- Preconnect Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
                            200: '#c7d2fe',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        },
                        obsidian: {
                            800: '#131b2e',
                            850: '#0f172a',
                            900: '#0b1120',
                            950: '#060913',
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        html { scroll-behavior: smooth; }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0b1120; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
        .glass-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-header {
            background: rgba(11, 17, 32, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-obsidian-950 text-slate-100 font-sans selection:bg-indigo-500 selection:text-white">

    <!-- Top Announcement Bar -->
    <div class="bg-gradient-to-r from-indigo-900/60 via-purple-900/40 to-slate-900/60 border-b border-indigo-500/20 py-1.5 px-4 text-center text-xs text-slate-300">
        <span class="inline-flex items-center gap-1.5 font-medium">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            <span class="text-indigo-300 font-semibold">Special Offer:</span>
            <span>Register .COM at only <strong>$10.49/yr</strong> (or ~৳1,258 BDT) — Zero Setup Fee for Resellers!</span>
            <a href="{{ route('pricing') }}" class="underline text-indigo-400 hover:text-white font-bold ml-1 transition">View Pricing →</a>
        </span>
    </div>

    <!-- Sticky Glass Header / Navbar -->
    <header class="sticky top-0 z-50 glass-header" x-data="{ mobileMenuOpen: false, domainsDropdown: false, resourcesDropdown: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand Logo -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform duration-200 ring-1 ring-white/20">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xl font-extrabold tracking-tight text-white block leading-none">
                                Domain<span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">Reseller</span>
                            </span>
                            <span class="text-[10px] tracking-widest uppercase font-mono text-indigo-300/80 font-bold">Wholesale Registry</span>
                        </div>
                    </a>

                    <!-- Desktop Navigation Links -->
                    <nav class="hidden lg:flex items-center space-x-1 text-sm font-medium text-slate-300">
                        <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('home') ? 'text-white bg-white/5 font-semibold' : 'hover:text-white hover:bg-white/5' }}">
                            Home
                        </a>

                        <!-- Domains Dropdown -->
                        <div class="relative" @click.away="domainsDropdown = false">
                            <button @click="domainsDropdown = !domainsDropdown" type="button"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition hover:text-white hover:bg-white/5 {{ request()->routeIs('domain.*') || request()->routeIs('pricing') || request()->routeIs('whois.*') ? 'text-white bg-white/5 font-semibold' : '' }}">
                                <span>Domains</span>
                                <svg class="w-4 h-4 opacity-70 transition-transform duration-200" :class="domainsDropdown ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="domainsDropdown" x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                class="absolute left-0 mt-2 w-72 p-2 rounded-2xl glass-card shadow-2xl shadow-black/80 border border-white/10 z-50">
                                <a href="{{ route('domain.search') }}" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-white/5 transition group">
                                    <div class="w-9 h-9 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white group-hover:text-indigo-300 transition">Domain Search</p>
                                        <p class="text-xs text-slate-400">Search single or bulk domain availability</p>
                                    </div>
                                </a>

                                <a href="{{ route('pricing') }}" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-white/5 transition group">
                                    <div class="w-9 h-9 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white group-hover:text-emerald-300 transition">TLD Wholesale Pricing</p>
                                        <p class="text-xs text-slate-400">Full register, renew & transfer pricing matrix</p>
                                    </div>
                                </a>

                                <a href="{{ route('whois.index') }}" class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-white/5 transition group">
                                    <div class="w-9 h-9 rounded-lg bg-violet-500/10 border border-violet-500/20 text-violet-400 flex items-center justify-center shrink-0 group-hover:bg-violet-600 group-hover:text-white transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white group-hover:text-violet-300 transition">WHOIS Lookup</p>
                                        <p class="text-xs text-slate-400">Inspect registrar, DNS & expiry records</p>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Reseller Program Link -->
                        <a href="{{ route('reseller.program') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('reseller.program') ? 'text-white bg-white/5 font-semibold' : 'hover:text-white hover:bg-white/5' }}">
                            <span class="flex items-center gap-1.5">
                                Reseller Program
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">WHMCS</span>
                            </span>
                        </a>

                        <!-- Resources Dropdown -->
                        <div class="relative" @click.away="resourcesDropdown = false">
                            <button @click="resourcesDropdown = !resourcesDropdown" type="button"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition hover:text-white hover:bg-white/5 {{ request()->routeIs('announcements.*') || request()->routeIs('knowledgebase.*') || request()->routeIs('contact') ? 'text-white bg-white/5 font-semibold' : '' }}">
                                <span>Resources</span>
                                <svg class="w-4 h-4 opacity-70 transition-transform duration-200" :class="resourcesDropdown ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="resourcesDropdown" x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                class="absolute left-0 mt-2 w-64 p-2 rounded-2xl glass-card shadow-2xl shadow-black/80 border border-white/10 z-50">
                                <a href="{{ route('announcements.index') }}" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/5 text-sm text-white transition">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                    <span>Announcements & News</span>
                                </a>
                                <a href="{{ route('knowledgebase.index') }}" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/5 text-sm text-white transition">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <span>Knowledgebase</span>
                                </a>
                                <a href="{{ route('contact') }}" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/5 text-sm text-white transition">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span>Contact Support</span>
                                </a>
                            </div>
                        </div>
                    </nav>
                </div>

                <!-- Right Action Buttons & Currency Selector -->
                <div class="hidden lg:flex items-center gap-4">
                    <!-- Dual Currency Pill -->
                    <x-currency-selector />

                    @auth
                        <!-- Authenticated User Dropdown -->
                        <div class="relative" x-data="{ userMenuOpen: false }" @click.away="userMenuOpen = false">
                            <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2.5 p-1.5 pl-3 pr-2 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition text-sm">
                                <span class="font-semibold text-white">{{ auth()->user()->name }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wide bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                    {{ auth()->user()->role }}
                                </span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="userMenuOpen" x-cloak
                                class="absolute right-0 mt-2 w-56 p-2 rounded-2xl glass-card shadow-2xl border border-white/10 z-50">
                                <div class="px-3 py-2 border-b border-white/10 mb-1">
                                    <p class="text-xs text-slate-400">Signed in as</p>
                                    <p class="text-sm font-bold text-white truncate">{{ auth()->user()->email }}</p>
                                </div>

                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-indigo-300 hover:bg-indigo-600 hover:text-white transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                        <span>Admin Control Panel</span>
                                    </a>
                                @elseif(auth()->user()->role === 'reseller')
                                    <a href="{{ route('reseller.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-purple-300 hover:bg-purple-600 hover:text-white transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span>Reseller Portal</span>
                                    </a>
                                @else
                                    <a href="{{ route('client.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-indigo-300 hover:bg-indigo-600 hover:text-white transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        <span>Client Dashboard</span>
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('logout') }}" class="mt-1 pt-1 border-t border-white/10">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-rose-400 hover:bg-rose-500/20 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        <span>Sign Out</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest Auth Buttons -->
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-300 hover:text-white px-3 py-2 transition">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-indigo-500 via-indigo-600 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98] transition-all duration-150 ring-1 ring-white/20">
                            <span>Get Started</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center gap-3 lg:hidden">
                    <x-currency-selector />
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                        class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-300 hover:text-white hover:bg-white/10 transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Drawer -->
        <div x-show="mobileMenuOpen" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
            class="lg:hidden glass-card border-b border-white/10 px-4 pt-3 pb-6 space-y-2">
            <a href="{{ route('home') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-white hover:bg-white/5">Home</a>
            <a href="{{ route('domain.search') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-white hover:bg-white/5">Domain Search</a>
            <a href="{{ route('pricing') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-white hover:bg-white/5">TLD Pricing</a>
            <a href="{{ route('reseller.program') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-white hover:bg-white/5">Reseller Program (WHMCS)</a>
            <a href="{{ route('whois.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-white hover:bg-white/5">WHOIS Lookup</a>
            <a href="{{ route('announcements.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-white hover:bg-white/5">Announcements</a>
            <a href="{{ route('knowledgebase.index') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-white hover:bg-white/5">Knowledgebase</a>
            <a href="{{ route('contact') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-white hover:bg-white/5">Contact Support</a>

            <div class="pt-4 border-t border-white/10 flex flex-col gap-2">
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="w-full text-center py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm">Admin Control Panel</a>
                    @elseif(auth()->user()->role === 'reseller')
                        <a href="{{ route('reseller.dashboard') }}" class="w-full text-center py-2.5 rounded-xl bg-purple-600 text-white font-bold text-sm">Reseller Portal</a>
                    @else
                        <a href="{{ route('client.dashboard') }}" class="w-full text-center py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm">Client Dashboard</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-xl bg-white/10 text-white font-semibold text-sm hover:bg-white/15">Sign In</a>
                    <a href="{{ route('register') }}" class="w-full text-center py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700">Create Account</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1">
        <!-- Toast Alerts -->
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-sm flex items-center justify-between backdrop-blur">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div class="p-4 rounded-2xl bg-rose-950/80 border border-rose-500/40 text-rose-200 text-sm flex items-center justify-between backdrop-blur">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- World-Class Obsidian Footer -->
    <footer class="bg-obsidian-900 border-t border-white/10 pt-16 pb-12 text-slate-400 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-white/10">
                <!-- Col 1: Brand Info -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                            DR
                        </div>
                        <span class="text-lg font-extrabold text-white">Domain<span class="text-indigo-400">Reseller</span></span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        High-availability multi-registrar wholesale infrastructure. Resell top-level domains seamlessly via official WHMCS modules, Blesta plugins, or our 44-endpoint REST API with native dual-currency USD/BDT billing.
                    </p>
                    <div class="flex items-center gap-2 pt-2">
                        <span class="px-2 py-1 rounded bg-white/5 border border-white/10 text-[10px] font-mono text-indigo-300">WHMCS v8.x Ready</span>
                        <span class="px-2 py-1 rounded bg-white/5 border border-white/10 text-[10px] font-mono text-emerald-300">99.99% Registry Uptime</span>
                    </div>
                </div>

                <!-- Col 2: Domains -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Domains</h3>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="{{ route('domain.search') }}" class="hover:text-indigo-400 transition">Domain Search</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-indigo-400 transition">TLD Wholesale Pricing</a></li>
                        <li><a href="{{ route('whois.index') }}" class="hover:text-indigo-400 transition">WHOIS Lookup Tool</a></li>
                        <li><a href="{{ route('domain.search') }}?tld=.com" class="hover:text-indigo-400 transition">Register .COM ($10.49)</a></li>
                        <li><a href="{{ route('domain.search') }}?tld=.xyz" class="hover:text-indigo-400 transition">Register .XYZ ($2.49)</a></li>
                    </ul>
                </div>

                <!-- Col 3: Resellers & API -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Reseller Solutions</h3>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="{{ route('reseller.program') }}" class="hover:text-indigo-400 transition">Reseller Overview</a></li>
                        <li><a href="{{ route('reseller.program') }}#whmcs" class="hover:text-indigo-400 transition">WHMCS Registrar Module</a></li>
                        <li><a href="{{ route('reseller.program') }}#api" class="hover:text-indigo-400 transition">External REST API</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-indigo-400 transition">Apply for Wholesale Tier</a></li>
                    </ul>
                </div>

                <!-- Col 4: Support & Legal -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Support & Trust</h3>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="{{ route('knowledgebase.index') }}" class="hover:text-indigo-400 transition">Knowledgebase</a></li>
                        <li><a href="{{ route('announcements.index') }}" class="hover:text-indigo-400 transition">System Announcements</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-indigo-400 transition">Contact Support</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-indigo-400 transition">Terms of Service</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-indigo-400 transition">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>

            <!-- Upstream & Payment Badges -->
            <div class="py-8 flex flex-col md:flex-row items-center justify-between gap-6 border-b border-white/10 text-xs">
                <div class="flex flex-wrap items-center gap-4">
                    <span class="text-slate-500 font-semibold uppercase text-[10px] tracking-wider">Upstream Registrars:</span>
                    <span class="font-mono text-slate-300">Namecheap</span>
                    <span class="text-slate-600">•</span>
                    <span class="font-mono text-slate-300">Spaceship</span>
                    <span class="text-slate-600">•</span>
                    <span class="font-mono text-slate-300">Dynadot</span>
                    <span class="text-slate-600">•</span>
                    <span class="font-mono text-slate-300">AtakDomain</span>
                    <span class="text-slate-600">•</span>
                    <span class="font-mono text-slate-300">NameSilo</span>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-slate-500 font-semibold uppercase text-[10px] tracking-wider">Payment Gateways:</span>
                    <span class="px-2 py-0.5 rounded bg-pink-500/10 border border-pink-500/20 text-pink-400 font-bold text-[10px]">bKash Direct</span>
                    <span class="px-2 py-0.5 rounded bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-bold text-[10px]">Stripe / Cards</span>
                    <span class="px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/20 text-amber-400 font-bold text-[10px]">Binance Pay</span>
                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-bold text-[10px]">Bank Transfer</span>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>© {{ date('Y') }} {{ config('app.name', 'DomainReseller') }}. All rights reserved. Automated Multi-Registrar Wholesale Domain Platform.</p>
                <p class="flex items-center gap-2">
                    <span>Base Currency: <strong>USD ($)</strong></span>
                    <span>•</span>
                    <span>Instant BDT (৳) Conversion</span>
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
