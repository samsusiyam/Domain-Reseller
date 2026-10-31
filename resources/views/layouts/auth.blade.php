<!DOCTYPE html>
<html lang="en" class="h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Account' }} — {{ config('app.name', 'DomainReseller') }}</title>

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

    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Design System Tokens -->
    <link rel="stylesheet" href="{{ asset('css/app.min.css') . '?v=' . @filemtime(public_path('css/app.min.css')) }}">
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
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="{{ asset('js/theme.js') }}"></script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--surface);
            color: var(--text);
        }
    </style>
</head>
<body class="min-h-full flex flex-col justify-center py-10 px-4 sm:px-6">

    <!-- Top Navigation Utility (Theme Toggle + Home link) -->
    <div class="fixed top-4 right-4 z-20 flex items-center gap-3">
        <x-theme-toggle />
    </div>

    <!-- Brand Logo Header -->
    <div class="sm:mx-auto sm:w-full sm:max-w-[400px] text-center mb-6 flex justify-center">
        <a href="{{ route('home') }}" class="inline-flex items-center justify-center hover:opacity-90 transition">
            <x-brand-logo class="h-9 w-auto max-h-9" :show-text="false" />
        </a>
    </div>

    <!-- Main Auth Card: 400px centered, 1px border, 8px radius, no shadow -->
    <div class="sm:mx-auto sm:w-full sm:max-w-[400px]">
        <div class="rounded-[8px] p-6 sm:p-7" style="background-color: var(--bg); border: 1px solid var(--border);">

            @if(session('success'))
                <div class="mb-4 p-3 rounded-[6px] text-xs flex items-center gap-2 font-medium"
                     style="background-color: var(--success-bg); border: 1px solid var(--success); color: var(--success);">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-3 rounded-[6px] text-xs flex items-center gap-2 font-medium"
                     style="background-color: var(--danger-bg); border: 1px solid var(--danger); color: var(--danger);">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </div>

        <!-- Back to Homepage Link -->
        <p class="mt-5 text-center text-xs" style="color: var(--text-muted);">
            <a href="{{ route('home') }}" class="hover:text-[#F6821F] transition inline-flex items-center gap-1.5 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Back to storefront</span>
            </a>
        </p>
    </div>

</body>
</html>
