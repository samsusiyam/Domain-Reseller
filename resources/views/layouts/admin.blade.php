<!DOCTYPE html>
<html lang="en" class="h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Overview' }} — {{ config('app.name', 'DomainReseller') }}</title>

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
                            'success-bg': '#F0FDF4',
                            warning: '#D97706',
                            'warning-bg': '#FFFBEB',
                            danger: '#DC2626',
                            'danger-bg': '#FEF2F2',
                            info: '#2563EB',
                            'info-bg': '#EFF6FF',
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

    <!-- Alpine.js & Theme/Currency Scripts -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="{{ asset('js/theme.js') }}"></script>
    <script defer src="{{ asset('js/currency.js') }}"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; font-size: 14px; line-height: 1.4; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        /* Minimalist scrollbars */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #9CA3AF; }
    </style>

    @stack('styles')
</head>
<body class="h-full flex overflow-hidden bg-slate-50 dark:bg-[#0B0F19] text-slate-900 dark:text-slate-100 antialiased"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('cf_sidebar_collapsed') === 'true',
          commandPaletteOpen: false,
          toggleCollapse() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('cf_sidebar_collapsed', this.sidebarCollapsed);
          }
      }"
      @keydown.window.prevent.ctrl.k="commandPaletteOpen = true"
      @keydown.window.prevent.cmd.k="commandPaletteOpen = true">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen"
         x-cloak
         x-transition:enter="transition-opacity ease-linear duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-[#111827]/40 lg:hidden"></div>

    <!-- LEFT SIDEBAR: 240px (collapsible to 56px), 1px border, white bg -->
    <aside :class="[
               sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0',
               sidebarCollapsed ? 'lg:w-14 w-64' : 'lg:w-60 w-64'
           ]"
           class="fixed inset-y-0 left-0 z-50 lg:static lg:z-auto bg-white dark:bg-[#111827] border-r border-slate-200 dark:border-slate-800 flex flex-col transition-all duration-200 ease-in-out shrink-0 select-none">

        <!-- Sidebar Top: Brand Logo + Name only -->
        <div class="h-14 flex items-center justify-between px-3.5 border-b border-slate-200 dark:border-slate-800 shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 overflow-hidden">
                <x-brand-logo class="h-7 w-auto" :show-text="false" />
            </a>

            <!-- Collapse Toggle for Desktop -->
            <button @click="toggleCollapse()"
                    type="button"
                    title="Toggle sidebar"
                    class="hidden lg:inline-flex items-center justify-center w-7 h-7 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60 rounded-[4px] transition">
                <svg x-show="!sidebarCollapsed" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                <svg x-show="sidebarCollapsed" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>

            <!-- Mobile Close Button -->
            <button @click="sidebarOpen = false"
                    type="button"
                    title="Close sidebar"
                    class="inline-flex lg:hidden items-center justify-center w-7 h-7 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60 rounded-[4px] transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation (Scrollable independently, Settings always reachable) -->
        <nav class="flex-1 min-h-0 overflow-y-auto px-2 py-3 space-y-4 text-[13px]">

            <!-- GROUP: Overview -->
            <div>
                <a href="{{ route('admin.dashboard') }}"
                   title="Overview"
                   class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Overview</span>
                </a>
            </div>

            <!-- GROUP: Customers -->
            <div>
                <div x-show="!sidebarCollapsed" class="px-2.5 pb-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">Customers</div>
                <div x-show="sidebarCollapsed" class="my-1 border-t border-slate-200 dark:border-slate-800"></div>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.clients.index') }}"
                       title="Clients"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.clients.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.clients.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Clients</span>
                    </a>
                    <a href="{{ route('admin.resellers.index') }}"
                       title="Resellers"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.resellers.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.resellers.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Resellers</span>
                    </a>
                </div>
            </div>

            <!-- GROUP: Domains -->
            <div>
                <div x-show="!sidebarCollapsed" class="px-2.5 pb-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">Domains</div>
                <div x-show="sidebarCollapsed" class="my-1 border-t border-slate-200 dark:border-slate-800"></div>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.domains.lifecycle') }}"
                       title="Domain Lifecycle"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.domains.lifecycle') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.domains.lifecycle') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Lifecycle & ICANN</span>
                    </a>
                    <a href="{{ route('admin.domains.index') }}"
                       title="All domains"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.domains.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.domains.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-.778.099-1.533.284-2.253" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">All domains</span>
                    </a>
                    <a href="{{ route('admin.tlds.index') }}"
                       title="TLD pricing"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.tlds.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.tlds.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.386a11.944 11.944 0 003.5-3.5c.486-.827.313-1.908-.386-2.607L9.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">TLD pricing</span>
                    </a>
                    <a href="{{ route('admin.registrars.index') }}"
                       title="Registrars"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.registrars.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.registrars.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.75 5.1a2.25 2.25 0 011.8-1.1h8.9a2.25 2.25 0 011.8 1.1l2.1 3.45c.6.98.9 2.1.9 3.2" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Registrars</span>
                    </a>
                </div>
            </div>

            <!-- GROUP: Billing -->
            <div>
                <div x-show="!sidebarCollapsed" class="px-2.5 pb-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">Billing</div>
                <div x-show="sidebarCollapsed" class="my-1 border-t border-slate-200 dark:border-slate-800"></div>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.orders.index') }}"
                       title="Orders"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.orders.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.orders.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Orders</span>
                    </a>
                    <a href="{{ route('admin.invoices.index') }}"
                       title="Invoices"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.invoices.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.invoices.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Invoices</span>
                    </a>
                    <a href="{{ route('admin.transactions.index') }}"
                       title="Ledger"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.transactions.index') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.transactions.index') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Ledger</span>
                    </a>
                    <a href="{{ route('admin.payments.manual.index') }}"
                       title="Manual payments"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.payments.manual.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.payments.manual.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Manual payments</span>
                    </a>
                </div>
            </div>

            <!-- GROUP: Support -->
            <div>
                <div x-show="!sidebarCollapsed" class="px-2.5 pb-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">Support</div>
                <div x-show="sidebarCollapsed" class="my-1 border-t border-slate-200 dark:border-slate-800"></div>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.tickets.index') }}"
                       title="Tickets"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.tickets.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.tickets.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Tickets</span>
                    </a>
                    <a href="{{ route('admin.inquiries.index') }}"
                       title="Inquiries"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.inquiries.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.inquiries.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Inquiries</span>
                    </a>
                    <a href="{{ route('admin.email-templates.index') }}"
                       title="Email templates"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.email-templates.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.email-templates.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Email templates</span>
                    </a>
                    <a href="{{ route('admin.promo-codes.index') }}"
                       title="Promo codes"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.promo-codes.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.promo-codes.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Promo codes</span>
                    </a>
                    <a href="{{ route('admin.announcements.index') }}"
                       title="Announcements"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.announcements.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.announcements.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Announcements</span>
                    </a>
                </div>
            </div>

            <!-- GROUP: Reports -->
            <div>
                <div x-show="!sidebarCollapsed" class="px-2.5 pb-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">Reports</div>
                <div x-show="sidebarCollapsed" class="my-1 border-t border-slate-200 dark:border-slate-800"></div>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.reports.revenue') }}"
                       title="Business reports"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.reports.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.reports.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Business reports</span>
                    </a>
                    <a href="{{ route('admin.api-logs.index') }}"
                       title="API Inspector"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.api-logs.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.api-logs.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">API Inspector</span>
                    </a>
                    <a href="{{ route('admin.activity-logs.index') }}"
                       title="Audit log"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.activity-logs.*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.activity-logs.*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Audit log</span>
                    </a>
                    <a href="{{ route('admin.system-health') }}"
                       title="System Health"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.system-health') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.system-health') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">System health</span>
                    </a>
                </div>
            </div>

            <!-- GROUP: Settings -->
            <div>
                <div x-show="!sidebarCollapsed" class="px-2.5 pb-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">Settings</div>
                <div x-show="sidebarCollapsed" class="my-1 border-t border-slate-200 dark:border-slate-800"></div>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.settings.general') }}"
                       title="General settings"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.settings.general*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.settings.general*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">General settings</span>
                    </a>
                    <a href="{{ route('admin.settings.sms') }}"
                       title="SMS Gateway"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.settings.sms*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.settings.sms*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">SMS Gateway</span>
                    </a>
                    <a href="{{ route('admin.settings.gateways') }}"
                       title="Payment gateways"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.settings.gateways*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.settings.gateways*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Payment gateways</span>
                    </a>
                    <a href="{{ route('admin.settings.nameservers') }}"
                       title="Nameservers"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.settings.nameservers*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.settings.nameservers*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.75 5.1a2.25 2.25 0 011.8-1.1h8.9a2.25 2.25 0 011.8 1.1l2.1 3.45c.6.98.9 2.1.9 3.2" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Nameservers</span>
                    </a>
                    <a href="{{ route('admin.settings.currencies') }}"
                       title="Currencies"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.settings.currencies*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.settings.currencies*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Currencies</span>
                    </a>
                    <a href="{{ route('admin.settings.email') }}"
                       title="SMTP settings"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.settings.email*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.settings.email*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m4.179 2.51l4.66 2.51m-8.839-2.51l-4.66 2.51" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">SMTP settings</span>
                    </a>
                    <a href="{{ route('admin.settings.security') }}"
                       title="Security policy"
                       class="group flex items-center gap-2.5 px-2.5 py-1.5 rounded-[4px] transition-colors {{ request()->routeIs('admin.settings.security*') ? 'bg-[#FFF7ED] text-[#F6821F] font-medium border-l-2 border-[#F6821F]' : 'border-l-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.settings.security*') ? 'text-[#F6821F]' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:text-slate-100' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Security policy</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Sidebar Bottom: Admin avatar, name, email & logout dropdown -->
        <div class="shrink-0 border-t border-slate-200 dark:border-slate-800 bg-white p-2.5"
             x-data="{ userMenuOpen: false }">
            <div class="relative">
                <button @click="userMenuOpen = !userMenuOpen"
                        type="button"
                        class="w-full flex items-center gap-2.5 p-1 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-left transition focus:outline-none">
                    <!-- Avatar circle -->
                    <div class="w-7 h-7 rounded-[4px] bg-[#111827] text-white flex items-center justify-center font-semibold text-[11px] shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div x-show="!sidebarCollapsed" class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-slate-900 dark:text-slate-100 truncate leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate leading-tight">{{ auth()->user()->email }}</p>
                    </div>
                    <svg x-show="!sidebarCollapsed" class="w-4 h-4 text-slate-500 dark:text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                    </svg>
                </button>

                <!-- Popover / Dropdown Menu -->
                <div x-show="userMenuOpen"
                     x-cloak
                     @click.away="userMenuOpen = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute bottom-full left-0 mb-1.5 w-52 bg-white dark:bg-[#111827] rounded-[6px] border border-slate-200 dark:border-slate-800 shadow-dropdown py-1 z-50 text-[13px]">
                    <div class="px-3 py-1.5 border-b border-slate-200 dark:border-slate-800">
                        <p class="font-medium text-slate-900 dark:text-slate-100 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('admin.settings.general') }}"
                       class="flex items-center gap-2 px-3 py-1.5 text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60 transition">
                        <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                        </svg>
                        Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-3 py-1.5 text-[#DC2626] hover:bg-[#FEF2F2] transition text-left">
                            <svg class="w-4 h-4 text-[#DC2626]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">

        <!-- TOP BAR: 56px, white, 1px bottom border -->
        <header class="h-14 bg-white dark:bg-[#111827] border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 z-10 shrink-0">

            <!-- Left: Mobile Menu toggle + Command Palette Trigger -->
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true"
                        type="button"
                        class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 lg:hidden transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Global Search Trigger / Command Palette Button -->
                <button @click="commandPaletteOpen = true"
                        type="button"
                        class="w-64 sm:w-80 h-9 px-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800 hover:border-[#D1D5DB] rounded-[6px] text-left flex items-center justify-between text-[13px] text-slate-500 dark:text-slate-400 transition focus:outline-none focus:border-[#F6821F]">
                    <div class="flex items-center gap-2 truncate">
                        <svg class="w-4 h-4 text-slate-500 dark:text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span class="truncate">Search domains, clients...</span>
                    </div>
                    <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-mono font-medium text-slate-500 dark:text-slate-400 bg-white border border-slate-200 dark:border-slate-800 rounded">Ctrl K</kbd>
                </button>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2">

                <!-- "+ Add" Dropdown -->
                <div class="relative" x-data="{ addMenuOpen: false }">
                    <button @click="addMenuOpen = !addMenuOpen"
                            type="button"
                            class="cf-btn cf-btn-primary cf-btn-sm inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Add</span>
                        <svg class="w-3 h-3 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="addMenuOpen"
                         x-cloak
                         @click.away="addMenuOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-1.5 w-52 bg-white dark:bg-[#111827] rounded-lg border border-slate-200 dark:border-slate-800 shadow-dropdown py-1.5 z-50 text-[13px]">
                        <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Quick Actions
                        </div>
                        <a href="{{ route('admin.clients.create') }}"
                           class="flex items-center gap-2.5 px-3 py-1.5 text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                            <svg class="w-4 h-4 text-[#F6821F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            <span>New Client</span>
                        </a>
                        <a href="{{ route('admin.orders.create') }}"
                           class="flex items-center gap-2.5 px-3 py-1.5 text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                            <svg class="w-4 h-4 text-[#F6821F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span>New Order</span>
                        </a>
                        <a href="{{ route('admin.invoices.create') }}"
                           class="flex items-center gap-2.5 px-3 py-1.5 text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                            <svg class="w-4 h-4 text-[#F6821F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span>New Invoice</span>
                        </a>
                        <a href="{{ route('admin.tickets.create') }}"
                           class="flex items-center gap-2.5 px-3 py-1.5 text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                            <svg class="w-4 h-4 text-[#F6821F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v5.018z" />
                            </svg>
                            <span>New Ticket</span>
                        </a>

                        <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>

                        <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Catalogue
                        </div>
                        <a href="{{ route('admin.resellers.create') }}"
                           class="flex items-center gap-2.5 px-3 py-1.5 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            <span>New Reseller</span>
                        </a>
                        <a href="{{ route('admin.tlds.create') }}"
                           class="flex items-center gap-2.5 px-3 py-1.5 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.386a11.944 11.944 0 003.5-3.5c.486-.827.313-1.908-.386-2.607L9.568 3z" />
                            </svg>
                            <span>New TLD Extension</span>
                        </a>
                        <a href="{{ route('admin.promo-codes.create') }}"
                           class="flex items-center gap-2.5 px-3 py-1.5 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                            </svg>
                            <span>New Promo Code</span>
                        </a>
                    </div></div>
                </div>

                <!-- "View storefront" Ghost Button -->
                <a href="{{ route('home') }}"
                   target="_blank"
                   class="cf-btn cf-btn-ghost cf-btn-sm inline-flex items-center gap-1.5 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100">
                    <span>View storefront</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>

                <!-- Theme Toggle -->
                <x-theme-toggle />

                <!-- Notifications Bell -->
                @php
                    $pendingTotal = ($pendingManualPaymentsCount ?? 0) + ($failedOrdersCount ?? 0) + ($openTicketsCount ?? 0);
                @endphp
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen"
                            type="button"
                            class="relative p-2 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 transition"
                            title="Notifications">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        @if($pendingTotal > 0)
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#DC2626]"></span>
                        @endif
                    </button>

                    <div x-show="notifOpen"
                         x-cloak
                         @click.away="notifOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-1.5 w-72 bg-white dark:bg-[#111827] rounded-[6px] border border-slate-200 dark:border-slate-800 shadow-dropdown py-2 z-50 text-[13px]">
                        <div class="px-3 pb-2 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <span class="font-semibold text-slate-900 dark:text-slate-100">Notifications</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $pendingTotal }} pending</span>
                        </div>
                        <div class="divide-y divide-[#E5E7EB] max-h-60 overflow-y-auto">
                            @if(($pendingManualPaymentsCount ?? 0) > 0)
                                <a href="{{ route('admin.payments.manual.index') }}" class="block px-3 py-2 hover:bg-slate-50 dark:bg-slate-800/60 transition">
                                    <p class="font-medium text-slate-900 dark:text-slate-100 text-xs">{{ $pendingManualPaymentsCount }} manual payments pending</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Review offline deposit slips</p>
                                </a>
                            @endif
                            @if(($failedOrdersCount ?? 0) > 0)
                                <a href="{{ route('admin.orders.index') }}" class="block px-3 py-2 hover:bg-slate-50 dark:bg-slate-800/60 transition">
                                    <p class="font-medium text-[#DC2626] text-xs">{{ $failedOrdersCount }} orders failed provisioning</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Retry registrar provisioning</p>
                                </a>
                            @endif
                            @if(($openTicketsCount ?? 0) > 0)
                                <a href="{{ route('admin.tickets.index') }}" class="block px-3 py-2 hover:bg-slate-50 dark:bg-slate-800/60 transition">
                                    <p class="font-medium text-slate-900 dark:text-slate-100 text-xs">{{ $openTicketsCount }} open support tickets</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Awaiting administrator response</p>
                                </a>
                            @endif
                            @if($pendingTotal === 0)
                                <div class="px-3 py-4 text-center text-slate-500 dark:text-slate-400 text-xs">
                                    All systems operational. No alerts.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Admin Avatar Dropdown -->
                <div class="relative" x-data="{ avatarMenuOpen: false }">
                    <button @click="avatarMenuOpen = !avatarMenuOpen"
                            type="button"
                            class="w-8 h-8 rounded-[4px] bg-[#111827] text-white flex items-center justify-center font-semibold text-xs transition hover:opacity-90">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </button>

                    <div x-show="avatarMenuOpen"
                         x-cloak
                         @click.away="avatarMenuOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-1.5 w-48 bg-white dark:bg-[#111827] rounded-[6px] border border-slate-200 dark:border-slate-800 shadow-dropdown py-1 z-50 text-[13px]">
                        <div class="px-3 py-1.5 border-b border-slate-200 dark:border-slate-800">
                            <p class="font-medium text-slate-900 dark:text-slate-100 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('admin.settings.general') }}" class="block px-3 py-1.5 text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60 transition">General settings</a>
                        <a href="{{ route('admin.settings.currencies') }}" class="block px-3 py-1.5 text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60 transition">Currencies</a>
                        <a href="{{ route('admin.settings.security') }}" class="block px-3 py-1.5 text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:bg-slate-800/60 transition">Security policy</a>
                        <div class="border-t border-slate-200 dark:border-slate-800 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-1.5 text-[#DC2626] hover:bg-[#FEF2F2] transition">Log out</button>
                        </form>
                    </div>
                </div>

            </div>
        </header>

        <!-- MAIN PAGE SCROLLER -->
        <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-[#0B0F19] p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Session Alerts -->
                @if(session('success'))
                    <div class="p-3 bg-[#F0FDF4] border border-[#BBF7D0] rounded-[6px] text-[13px] text-[#166534] flex items-center justify-between" role="alert">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#16A34A]"></span>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" @click="$el.parentElement.remove()" class="text-[#166534] hover:opacity-75">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="p-3 bg-[#FEF2F2] border border-[#FECACA] rounded-[6px] text-[13px] text-[#991B1B] flex items-center justify-between" role="alert">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#DC2626]"></span>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" @click="$el.parentElement.remove()" class="text-[#991B1B] hover:opacity-75">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>

    <!-- COMMAND PALETTE MODAL (Ctrl + K) -->
    <div x-show="commandPaletteOpen"
         x-cloak
         @keydown.escape.window="commandPaletteOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20 flex justify-center items-start"
         role="dialog"
         aria-modal="true">
        <!-- Backdrop -->
        <div x-show="commandPaletteOpen"
             x-transition:enter="ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="commandPaletteOpen = false"
             class="fixed inset-0 bg-[#111827]/40"></div>

        <!-- Dialog Box -->
        <div x-show="commandPaletteOpen"
             x-transition:enter="ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-xl bg-white rounded-[8px] border border-slate-200 dark:border-slate-800 shadow-modal overflow-hidden text-[13px]"
             x-data="{ query: '' }">

            <!-- Search Field -->
            <div class="flex items-center px-3.5 border-b border-slate-200 dark:border-slate-800">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text"
                       x-model="query"
                       x-ref="commandInput"
                       placeholder="Jump to client, domain, order, invoice, or setting..."
                       class="w-full h-11 px-3 bg-transparent text-slate-900 dark:text-slate-100 placeholder-[#9CA3AF] text-[13px] border-0 focus:outline-none focus:ring-0">
                <kbd class="px-1.5 py-0.5 text-[10px] font-mono font-medium text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800 rounded">ESC</kbd>
            </div>

            <!-- Jump List -->
            <div class="max-h-80 overflow-y-auto p-2 space-y-1">
                <div class="px-2 py-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">Quick Jump</div>

                <a href="{{ route('admin.dashboard') }}"
                   x-show="!query || 'overview dashboard'.includes(query.toLowerCase())"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 transition">
                    <span class="font-medium">Overview Dashboard</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">/admin/dashboard</span>
                </a>
                <a href="{{ route('admin.clients.index') }}"
                   x-show="!query || 'clients direct customer'.includes(query.toLowerCase())"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 transition">
                    <span class="font-medium">Clients</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">/admin/clients</span>
                </a>
                <a href="{{ route('admin.resellers.index') }}"
                   x-show="!query || 'resellers wholesale partner'.includes(query.toLowerCase())"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 transition">
                    <span class="font-medium">Resellers</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">/admin/resellers</span>
                </a>
                <a href="{{ route('admin.domains.index') }}"
                   x-show="!query || 'domains all master'.includes(query.toLowerCase())"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 transition">
                    <span class="font-medium">All Domains</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">/admin/domains</span>
                </a>
                <a href="{{ route('admin.tlds.index') }}"
                   x-show="!query || 'tld pricing matrix extension'.includes(query.toLowerCase())"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 transition">
                    <span class="font-medium">TLD Pricing Matrix</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">/admin/tlds</span>
                </a>
                <a href="{{ route('admin.registrars.index') }}"
                   x-show="!query || 'registrars modules namecheap spaceship dynadot'.includes(query.toLowerCase())"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 transition">
                    <span class="font-medium">Registrar Modules</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">/admin/registrars</span>
                </a>
                <a href="{{ route('admin.orders.index') }}"
                   x-show="!query || 'orders provisioning queue'.includes(query.toLowerCase())"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 transition">
                    <span class="font-medium">Orders</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">/admin/orders</span>
                </a>
                <a href="{{ route('admin.invoices.index') }}"
                   x-show="!query || 'invoices billing payments'.includes(query.toLowerCase())"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 transition">
                    <span class="font-medium">Invoices</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">/admin/invoices</span>
                </a>
                <a href="{{ route('admin.transactions.index') }}"
                   x-show="!query || 'ledger transactions finance'.includes(query.toLowerCase())"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 transition">
                    <span class="font-medium">Financial Ledger</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">/admin/transactions</span>
                </a>
                <a href="{{ route('admin.payments.manual.index') }}"
                   x-show="!query || 'manual offline bank bkash payment'.includes(query.toLowerCase())"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 transition">
                    <span class="font-medium">Manual Payments</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">/admin/payments/manual-requests</span>
                </a>
                <a href="{{ route('admin.tickets.index') }}"
                   x-show="!query || 'tickets support helpdesk'.includes(query.toLowerCase())"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 transition">
                    <span class="font-medium">Support Tickets</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">/admin/tickets</span>
                </a>
                <a href="{{ route('admin.settings.general') }}"
                   x-show="!query || 'settings config system general'.includes(query.toLowerCase())"
                   class="flex items-center justify-between px-2.5 py-1.5 rounded-[4px] hover:bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100 transition">
                    <span class="font-medium">General Settings</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">/admin/settings/general</span>
                </a>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
