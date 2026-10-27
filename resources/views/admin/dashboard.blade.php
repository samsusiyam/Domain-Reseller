@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{
    drawerOpen: false,
    selectedOrder: null,
    openOrder(order) {
        this.selectedOrder = order;
        this.drawerOpen = true;
    },
    closeDrawer() {
        this.drawerOpen = false;
        this.selectedOrder = null;
    }
}">

    <!-- PAGE HEADER: Breadcrumb -> Title (24px) -> Controls -->
    <div>
        <nav class="flex items-center gap-1.5 text-[12px] text-[#6B7280] mb-1">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-[#111827]">Admin</a>
            <span>/</span>
            <span class="text-[#111827] font-medium">Overview</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-[24px] font-semibold text-[#111827] tracking-tight leading-tight">Overview</h1>
                <p class="text-[13px] text-[#6B7280] mt-0.5">Platform operations and real-time business telemetry.</p>
            </div>

            <!-- Right Controls: Date Range Selector + Registrar API Status Popover -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Date Range Selector -->
                <div class="inline-flex rounded-[6px] border border-[#E5E7EB] bg-[#F7F7F8] p-0.5 text-[12px] font-medium">
                    @foreach(['today' => 'Today', '7d' => '7d', '30d' => '30d', 'mtd' => 'MTD', 'ytd' => 'YTD'] as $key => $label)
                        <a href="{{ route('admin.dashboard', ['range' => $key]) }}"
                           class="px-2.5 py-1 rounded-[4px] transition-colors {{ $range === $key ? 'bg-white text-[#111827] font-semibold border border-[#E5E7EB]' : 'text-[#6B7280] hover:text-[#111827]' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <!-- API Status Popover -->
                <div class="relative" x-data="{ statusOpen: false }">
                    <button @click="statusOpen = !statusOpen"
                            type="button"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 h-8 rounded-[6px] border border-[#E5E7EB] bg-white hover:bg-[#F7F7F8] text-[12px] text-[#111827] font-medium transition">
                        <span class="w-2 h-2 rounded-full {{ $lowBalanceRegistrars->count() > 0 ? 'bg-[#D97706]' : 'bg-[#16A34A]' }}"></span>
                        <span>{{ $lowBalanceRegistrars->count() > 0 ? 'Degraded upstream' : 'All systems operational' }}</span>
                        <svg class="w-3.5 h-3.5 text-[#6B7280]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="statusOpen"
                         x-cloak
                         @click.away="statusOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-1.5 w-80 bg-white rounded-[6px] border border-[#E5E7EB] shadow-dropdown p-3 z-50 text-[13px]">
                        <div class="flex items-center justify-between pb-2 border-b border-[#E5E7EB]">
                            <span class="font-semibold text-[#111827] text-xs">Registrar API status</span>
                            <span class="text-[11px] text-[#6B7280]">{{ $registrars->count() }} active modules</span>
                        </div>
                        <div class="divide-y divide-[#E5E7EB] my-1 max-h-56 overflow-y-auto">
                            @forelse($registrars as $reg)
                                <div class="py-2 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ (float)$reg->balance < (float)($reg->low_balance_threshold ?? 50) ? 'bg-[#D97706]' : 'bg-[#16A34A]' }}"></span>
                                        <span class="font-medium text-[#111827]">{{ $reg->name }}</span>
                                        <span class="text-[10px] text-[#6B7280] uppercase">({{ $reg->environment }})</span>
                                    </div>
                                    <span class="tabular-nums font-mono text-[#111827]">${{ number_format((float)$reg->balance, 2) }}</span>
                                </div>
                            @empty
                                <div class="py-3 text-center text-xs text-[#6B7280]">No active registrar modules found.</div>
                            @endforelse
                        </div>
                        <div class="pt-2 border-t border-[#E5E7EB] text-right">
                            <a href="{{ route('admin.registrars.index') }}" class="text-[12px] text-[#F6821F] hover:underline font-medium">Manage registrars &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI STRIP: ONE bordered panel divided into 4 equal columns by vertical dividers (No icons) -->
    <div class="bg-white border border-[#E5E7EB] rounded-[8px] overflow-hidden">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-[#E5E7EB]">

            <!-- Column 1: Revenue -->
            <div class="p-5 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-[#6B7280]">Revenue ({{ strtoupper($range) }})</span>
                <div class="mt-2">
                    <div class="text-[28px] font-semibold text-[#111827] tabular-nums tracking-tight leading-none">
                        ${{ number_format($revenueUsd, 2) }}
                    </div>
                    <div class="mt-2 flex items-center gap-1.5 text-[12px]">
                        @if($revenueDelta >= 0)
                            <span class="font-medium text-[#16A34A] tabular-nums">+{{ $revenueDelta }}%</span>
                        @else
                            <span class="font-medium text-[#DC2626] tabular-nums">{{ $revenueDelta }}%</span>
                        @endif
                        <span class="text-[#6B7280]">vs last period</span>
                    </div>
                    <div class="text-[12px] text-[#6B7280] tabular-nums mt-0.5">
                        ≈ ৳{{ number_format($revenueBdt, 2) }} BDT
                    </div>
                </div>
            </div>

            <!-- Column 2: Active Domains -->
            <div class="p-5 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-[#6B7280]">Active domains</span>
                <div class="mt-2">
                    <div class="text-[28px] font-semibold text-[#111827] tabular-nums tracking-tight leading-none">
                        {{ number_format($totalActiveDomains) }}
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('admin.domains.index') }}" class="text-[12px] text-[#F6821F] hover:underline font-medium">
                            {{ $expiringIn30Days }} expiring in 30 days &rarr;
                        </a>
                    </div>
                    <div class="text-[12px] text-[#6B7280] mt-0.5">
                        Managed on upstream registrars
                    </div>
                </div>
            </div>

            <!-- Column 3: Clients -->
            <div class="p-5 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-[#6B7280]">Clients</span>
                <div class="mt-2">
                    <div class="text-[28px] font-semibold text-[#111827] tabular-nums tracking-tight leading-none">
                        {{ number_format($totalClients) }}
                    </div>
                    <div class="mt-2 text-[12px] text-[#6B7280]">
                        Direct retail accounts
                    </div>
                    <div class="text-[12px] text-[#6B7280] mt-0.5">
                        <a href="{{ route('admin.clients.index') }}" class="text-[#6B7280] hover:text-[#111827]">View all clients &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Column 4: Resellers -->
            <div class="p-5 flex flex-col justify-between">
                <span class="text-[12px] font-medium text-[#6B7280]">Resellers</span>
                <div class="mt-2">
                    <div class="text-[28px] font-semibold text-[#111827] tabular-nums tracking-tight leading-none">
                        {{ number_format($totalResellers) }}
                    </div>
                    <div class="mt-2 text-[12px] text-[#6B7280]">
                        Active wholesale partners
                    </div>
                    <div class="text-[12px] text-[#6B7280] mt-0.5">
                        <a href="{{ route('admin.resellers.index') }}" class="text-[#6B7280] hover:text-[#111827]">View reseller matrix &rarr;</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ALERT STRIP: Only renders if attention items exist, otherwise hidden -->
    @php
        $hasAlerts = ($lowBalanceRegistrars->count() > 0) || ($pendingManualPaymentsCount > 0) || ($failedOrdersCount > 0) || ($openTicketsCount > 0);
    @endphp
    @if($hasAlerts)
        <div class="border border-[#E5E7EB] bg-[#F7F7F8] rounded-[8px] p-3.5 space-y-2 text-[13px]">
            @if($lowBalanceRegistrars->count() > 0)
                <div class="flex items-center justify-between text-[#111827]">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#D97706] shrink-0"></span>
                        <span>Low registrar balance: <strong>{{ $lowBalanceRegistrars->pluck('name')->join(', ') }}</strong> below configured threshold.</span>
                    </div>
                    <a href="{{ route('admin.registrars.index') }}" class="text-[#F6821F] hover:underline font-medium shrink-0 ml-3">Review &rarr;</a>
                </div>
            @endif

            @if($pendingManualPaymentsCount > 0)
                <div class="flex items-center justify-between text-[#111827]">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#D97706] shrink-0"></span>
                        <span><strong>{{ $pendingManualPaymentsCount }}</strong> manual bank/bKash deposit requests awaiting verification.</span>
                    </div>
                    <a href="{{ route('admin.payments.manual.index') }}" class="text-[#F6821F] hover:underline font-medium shrink-0 ml-3">Review &rarr;</a>
                </div>
            @endif

            @if($failedOrdersCount > 0)
                <div class="flex items-center justify-between text-[#111827]">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#DC2626] shrink-0"></span>
                        <span><strong>{{ $failedOrdersCount }}</strong> domain provisioning orders failed.</span>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="text-[#F6821F] hover:underline font-medium shrink-0 ml-3">Review &rarr;</a>
                </div>
            @endif

            @if($openTicketsCount > 0)
                <div class="flex items-center justify-between text-[#111827]">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#2563EB] shrink-0"></span>
                        <span><strong>{{ $openTicketsCount }}</strong> customer support tickets awaiting reply.</span>
                    </div>
                    <a href="{{ route('admin.tickets.index') }}" class="text-[#F6821F] hover:underline font-medium shrink-0 ml-3">Review &rarr;</a>
                </div>
            @endif
        </div>
    @endif

    <!-- TWO-COLUMN SECTION: Left (2/3 Revenue line chart) | Right (1/3 Needs attention) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left: Revenue line chart (8 of 12 cols = 2/3) -->
        <div class="lg:col-span-8 bg-white border border-[#E5E7EB] rounded-[8px] p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-[16px] font-semibold text-[#111827] leading-tight">Revenue</h2>
                    <p class="text-[12px] text-[#6B7280] mt-0.5">Calculated from credit transactions over {{ strtoupper($range) }}.</p>
                </div>
                <div class="text-right">
                    <span class="text-[12px] text-[#6B7280]">Total</span>
                    <span class="block text-[14px] font-semibold text-[#111827] tabular-nums">${{ number_format($revenueUsd, 2) }}</span>
                </div>
            </div>

            <!-- SVG Line Chart with light gridlines or Empty State if no data -->
            @php
                $amounts = array_column($chartPoints, 'amount');
                $maxAmount = !empty($amounts) ? max($amounts) : 0;
            @endphp

            @if($maxAmount <= 0)
                <div class="h-52 flex flex-col items-center justify-center text-center p-6 rounded-[6px] border border-dashed border-[#E5E7EB] bg-[#F7F7F8]">
                    <svg class="w-8 h-8 text-[#9CA3AF] mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                    <p class="text-[13px] font-medium text-[#111827]">No revenue recorded for {{ strtoupper($range) }}</p>
                    <p class="text-[12px] text-[#6B7280] mt-0.5 max-w-sm">Domain registrations, renewals, and wallet deposits will populate this chart automatically.</p>
                </div>
            @else
            @php
                $chartMax = $maxAmount > 0 ? ceil($maxAmount * 1.25) : 100;
                $chartMax = max($chartMax, 10);
                $pointCount = count($chartPoints);
                $svgWidth = 720;
                $svgHeight = 200;
                $paddingTop = 15;
                $paddingBottom = 25;
                $paddingLeft = 45;
                $paddingRight = 15;
                $plotWidth = $svgWidth - $paddingLeft - $paddingRight;
                $plotHeight = $svgHeight - $paddingTop - $paddingBottom;

                $coordinates = [];
                if ($pointCount > 1) {
                    $stepX = $plotWidth / ($pointCount - 1);
                    foreach ($chartPoints as $index => $pt) {
                        $x = $paddingLeft + ($index * $stepX);
                        $y = $paddingTop + $plotHeight - (($pt['amount'] / $chartMax) * $plotHeight);
                        $coordinates[] = [
                            'x' => round($x, 1),
                            'y' => round($y, 1),
                            'date' => $pt['date'],
                            'amount' => $pt['amount'],
                        ];
                    }
                } elseif ($pointCount === 1) {
                    $pt = $chartPoints[0];
                    $coordinates[] = [
                        'x' => $paddingLeft + ($plotWidth / 2),
                        'y' => $paddingTop + $plotHeight - (($pt['amount'] / $chartMax) * $plotHeight),
                        'date' => $pt['date'],
                        'amount' => $pt['amount'],
                    ];
                }
                $pointsString = implode(' ', array_map(fn($c) => "{$c['x']},{$c['y']}", $coordinates));
            @endphp

            <div class="relative" x-data="{ tooltip: null }">
                <!-- Hover Tooltip Box -->
                <div x-show="tooltip"
                     x-cloak
                     :style="`left: ${tooltip ? tooltip.x : 0}px; top: ${tooltip ? tooltip.y - 38 : 0}px;`"
                     class="absolute pointer-events-none -translate-x-1/2 bg-[#111827] text-white text-[11px] px-2.5 py-1 rounded-[4px] shadow-dropdown whitespace-nowrap z-20 transition-all duration-75">
                    <span x-text="tooltip?.date" class="text-[#9CA3AF]"></span>:
                    $<span x-text="tooltip?.amount" class="font-semibold text-white"></span>
                </div>

                <div class="w-full overflow-x-auto">
                    <svg viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}" class="w-full h-52 select-none">
                        <!-- Y-Axis Gridlines & Labels -->
                        @for($i = 0; $i <= 3; $i++)
                            @php
                                $val = round($chartMax * (1 - ($i / 3)));
                                $lineY = $paddingTop + ($plotHeight * ($i / 3));
                            @endphp
                            <line x1="{{ $paddingLeft }}" y1="{{ $lineY }}" x2="{{ $svgWidth - $paddingRight }}" y2="{{ $lineY }}"
                                  stroke="#E5E7EB" stroke-width="1" />
                            <text x="{{ $paddingLeft - 8 }}" y="{{ $lineY + 3 }}"
                                  fill="#9CA3AF" font-size="10" text-anchor="end" font-family="'JetBrains Mono', monospace">
                                ${{ $val }}
                            </text>
                        @endfor

                        <!-- Date labels along X axis -->
                        @if(!empty($coordinates))
                            @php
                                $stepLabel = max(1, floor(count($coordinates) / 6));
                            @endphp
                            @foreach($coordinates as $idx => $c)
                                @if($idx === 0 || $idx === count($coordinates) - 1 || $idx % $stepLabel === 0)
                                    <text x="{{ $c['x'] }}" y="{{ $svgHeight - 6 }}"
                                          fill="#9CA3AF" font-size="10" text-anchor="middle" font-family="'Inter', sans-serif">
                                        {{ $c['date'] }}
                                    </text>
                                @endif
                            @endforeach
                        @endif

                        <!-- The Single Accent Revenue Line -->
                        @if(count($coordinates) > 1)
                            <polyline points="{{ $pointsString }}"
                                      fill="none"
                                      stroke="#F6821F"
                                      stroke-width="2"
                                      stroke-linecap="round"
                                      stroke-linejoin="round" />
                        @endif

                        <!-- Data Points -->
                        @foreach($coordinates as $c)
                            <circle cx="{{ $c['x'] }}"
                                    cy="{{ $c['y'] }}"
                                    r="3"
                                    fill="#FFFFFF"
                                    stroke="#F6821F"
                                    stroke-width="2"
                                    class="cursor-pointer transition-transform hover:scale-150"
                                    @mouseenter="tooltip = { x: {{ $c['x'] }}, y: {{ $c['y'] }}, date: '{{ $c['date'] }}', amount: '{{ number_format($c['amount'], 2) }}' }"
                                    @mouseleave="tooltip = null" />
                        @endforeach
                    </svg>
                </div>
            </div>
            @endif
        </div>

        <!-- Right: Needs attention list (4 of 12 cols = 1/3) -->
        <div class="lg:col-span-4 bg-white border border-[#E5E7EB] rounded-[8px] p-5">
            <h2 class="text-[16px] font-semibold text-[#111827] leading-tight">Needs attention</h2>
            <p class="text-[12px] text-[#6B7280] mt-0.5 mb-3">Pending administrative approvals and notices.</p>

            <div class="divide-y divide-[#E5E7EB] text-[13px]">

                <!-- Item 1: Manual payment approvals -->
                <a href="{{ route('admin.payments.manual.index') }}"
                   class="py-2.5 flex items-center justify-between hover:text-[#F6821F] group transition">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full {{ $pendingManualPaymentsCount > 0 ? 'bg-[#D97706]' : 'bg-[#E5E7EB]' }} shrink-0"></span>
                        <span class="truncate text-[#111827] group-hover:text-[#F6821F]">Manual payment approvals</span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="px-1.5 py-0.5 rounded text-[11px] font-mono font-medium {{ $pendingManualPaymentsCount > 0 ? 'bg-[#FFFBEB] text-[#D97706] border border-[#FDE68A]' : 'bg-[#F7F7F8] text-[#6B7280]' }}">
                            {{ $pendingManualPaymentsCount }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-[#6B7280] group-hover:text-[#F6821F]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                <!-- Item 2: Open support tickets -->
                <a href="{{ route('admin.tickets.index') }}"
                   class="py-2.5 flex items-center justify-between hover:text-[#F6821F] group transition">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full {{ $openTicketsCount > 0 ? 'bg-[#2563EB]' : 'bg-[#E5E7EB]' }} shrink-0"></span>
                        <span class="truncate text-[#111827] group-hover:text-[#F6821F]">Open support tickets</span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="px-1.5 py-0.5 rounded text-[11px] font-mono font-medium {{ $openTicketsCount > 0 ? 'bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE]' : 'bg-[#F7F7F8] text-[#6B7280]' }}">
                            {{ $openTicketsCount }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-[#6B7280] group-hover:text-[#F6821F]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                <!-- Item 3: Expiring domains -->
                <a href="{{ route('admin.domains.index') }}"
                   class="py-2.5 flex items-center justify-between hover:text-[#F6821F] group transition">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full {{ $expiringIn30Days > 0 ? 'bg-[#D97706]' : 'bg-[#E5E7EB]' }} shrink-0"></span>
                        <span class="truncate text-[#111827] group-hover:text-[#F6821F]">Domains expiring in 30 days</span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="px-1.5 py-0.5 rounded text-[11px] font-mono font-medium {{ $expiringIn30Days > 0 ? 'bg-[#FFFBEB] text-[#D97706] border border-[#FDE68A]' : 'bg-[#F7F7F8] text-[#6B7280]' }}">
                            {{ $expiringIn30Days }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-[#6B7280] group-hover:text-[#F6821F]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

                <!-- Item 4: Failed orders -->
                <a href="{{ route('admin.orders.index') }}"
                   class="py-2.5 flex items-center justify-between hover:text-[#F6821F] group transition">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full {{ $failedOrdersCount > 0 ? 'bg-[#DC2626]' : 'bg-[#E5E7EB]' }} shrink-0"></span>
                        <span class="truncate text-[#111827] group-hover:text-[#F6821F]">Failed provisioning orders</span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0 ml-2">
                        <span class="px-1.5 py-0.5 rounded text-[11px] font-mono font-medium {{ $failedOrdersCount > 0 ? 'bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA]' : 'bg-[#F7F7F8] text-[#6B7280]' }}">
                            {{ $failedOrdersCount }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-[#6B7280] group-hover:text-[#F6821F]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>

            </div>

            <div class="mt-4 pt-3 border-t border-[#E5E7EB] text-right">
                <a href="{{ route('admin.orders.index') }}" class="text-[12px] text-[#F6821F] hover:underline font-medium">View all queues &rarr;</a>
            </div>
        </div>

    </div>

    <!-- REGISTRAR BALANCES: Compact table -->
    <div class="bg-white border border-[#E5E7EB] rounded-[8px] overflow-hidden">
        <div class="p-4 border-b border-[#E5E7EB] flex items-center justify-between">
            <div>
                <h2 class="text-[16px] font-semibold text-[#111827] leading-tight">Registrar balances</h2>
                <p class="text-[12px] text-[#6B7280] mt-0.5">Live wholesale balances at upstream domain suppliers.</p>
            </div>
            <div class="text-right">
                <span class="text-[12px] text-[#6B7280]">Total upstream balance</span>
                <span class="block text-[15px] font-semibold text-[#111827] tabular-nums">${{ number_format($totalRegistrarBalance, 2) }} USD</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[12px] text-[#6B7280] font-normal">
                    <tr>
                        <th class="px-4 py-2.5 font-medium">Registrar</th>
                        <th class="px-4 py-2.5 font-medium">Environment</th>
                        <th class="px-4 py-2.5 font-medium text-right">Balance</th>
                        <th class="px-4 py-2.5 font-medium">Status</th>
                        <th class="px-4 py-2.5 font-medium">Last synced</th>
                        <th class="px-4 py-2.5 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($registrars as $reg)
                        @php
                            $isLow = (float) $reg->balance < (float) ($reg->low_balance_threshold ?? 50.00);
                        @endphp
                        <tr class="hover:bg-[#F7F7F8] transition-colors h-[44px]">
                            <!-- Registrar -->
                            <td class="px-4 py-2 text-[#111827] font-medium">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-[#F7F7F8] border border-[#E5E7EB] text-[#111827] flex items-center justify-center font-bold text-[10px]">
                                        {{ substr($reg->name, 0, 2) }}
                                    </div>
                                    <span>{{ $reg->name }}</span>
                                </div>
                            </td>

                            <!-- Environment -->
                            <td class="px-4 py-2 text-[#6B7280]">
                                <span class="capitalize">{{ $reg->environment }}</span>
                            </td>

                            <!-- Balance -->
                            <td class="px-4 py-2 text-right">
                                <div class="tabular-nums font-mono font-medium {{ $isLow ? 'text-[#D97706]' : 'text-[#111827]' }}">
                                    ${{ number_format((float)$reg->balance, 2) }}
                                </div>
                                @if($isLow)
                                    <a href="{{ route('admin.registrars.edit', $reg->id) }}" class="text-[11px] text-[#F6821F] hover:underline block">
                                        Top up
                                    </a>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-2">
                                <x-cf.status-dot :status="$reg->is_active ? 'success' : 'neutral'" :label="$reg->is_active ? 'Active' : 'Disabled'" />
                            </td>

                            <!-- Last synced -->
                            <td class="px-4 py-2 text-[#6B7280] text-[12px]">
                                {{ $reg->updated_at ? $reg->updated_at->diffForHumans() : 'Never' }}
                            </td>

                            <!-- Action -->
                            <td class="px-4 py-2 text-right">
                                <form action="{{ route('admin.registrars.sync-balance', $reg->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="cf-btn cf-btn-secondary cf-btn-sm">
                                        Sync
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-[#6B7280]">
                                No registrar modules configured yet. <a href="{{ route('admin.registrars.index') }}" class="text-[#F6821F] hover:underline font-medium">Configure modules</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Footer row: Total balance -->
                @if($registrars->isNotEmpty())
                    <tfoot class="bg-[#F7F7F8] border-t border-[#E5E7EB] font-medium text-[13px] text-[#111827]">
                        <tr>
                            <td class="px-4 py-3" colspan="2">Total upstream balance</td>
                            <td class="px-4 py-3 text-right font-mono font-semibold tabular-nums">
                                ${{ number_format($totalRegistrarBalance, 2) }}
                            </td>
                            <td class="px-4 py-3 text-[12px] text-[#6B7280]" colspan="3">
                                Across {{ $registrars->count() }} active modules (≈ ৳{{ number_format($totalRegistrarBalanceBdt, 2) }} BDT)
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- RECENT ORDERS TABLE (Last 10) -->
    <div class="bg-white border border-[#E5E7EB] rounded-[8px] overflow-hidden">
        <div class="p-4 border-b border-[#E5E7EB] flex items-center justify-between">
            <div>
                <h2 class="text-[16px] font-semibold text-[#111827] leading-tight">Recent orders</h2>
                <p class="text-[12px] text-[#6B7280] mt-0.5">Latest registrations, transfers and renewals.</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-[12px] text-[#F6821F] hover:underline font-medium">
                All orders &rarr;
            </a>
        </div>

        @if($recentOrders->isEmpty())
            <div class="p-8">
                <x-cf.empty-state
                    title="No orders yet"
                    description="Orders appear here when customers register domains."
                    action-label="Create test order"
                    action-url="{{ route('admin.orders.index') }}"
                />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead class="bg-[#F7F7F8] border-b border-[#E5E7EB] text-[12px] text-[#6B7280] font-normal">
                        <tr>
                            <th class="px-4 py-2.5 font-medium">Order ID</th>
                            <th class="px-4 py-2.5 font-medium">Customer</th>
                            <th class="px-4 py-2.5 font-medium">Domain</th>
                            <th class="px-4 py-2.5 font-medium">Type</th>
                            <th class="px-4 py-2.5 font-medium text-right">Amount</th>
                            <th class="px-4 py-2.5 font-medium">Status</th>
                            <th class="px-4 py-2.5 font-medium">Time</th>
                            <th class="px-4 py-2.5 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @foreach($recentOrders as $order)
                            @php
                                $firstItem = $order->items->first();
                                $itemCount = $order->items->count();
                                $domainDisplay = $firstItem?->domain_name ?? ($firstItem?->description ?? '—');
                                $orderType = ucfirst($firstItem?->action_type ?? 'Registration');
                                $statusVariant = match($order->status) {
                                    'completed' => 'success',
                                    'pending' => 'warning',
                                    'failed' => 'danger',
                                    default => 'neutral'
                                };
                                $orderPayload = [
                                    'id' => $order->id,
                                    'order_number' => $order->order_number ?? ('ORD-' . $order->id),
                                    'customer_name' => $order->user?->name ?? 'Guest',
                                    'customer_email' => $order->user?->email ?? '—',
                                    'domain' => $domainDisplay,
                                    'item_count' => $itemCount,
                                    'type' => $orderType,
                                    'amount' => number_format($order->total_amount, 2),
                                    'status' => ucfirst($order->status),
                                    'status_variant' => $statusVariant,
                                    'time' => $order->created_at->diffForHumans(),
                                    'date' => $order->created_at->format('M d, Y H:i'),
                                    'notes' => $order->notes ?? 'No provisioning issues logged.',
                                    'view_url' => route('admin.orders.show', $order->id),
                                ];
                            @endphp
                            <tr class="hover:bg-[#F7F7F8] transition-colors cursor-pointer h-[44px]"
                                @click="openOrder(@js($orderPayload))">
                                <!-- Order ID -->
                                <td class="px-4 py-2 font-mono text-xs font-semibold text-[#111827]">
                                    #{{ $order->order_number ?? ('ORD-' . $order->id) }}
                                </td>

                                <!-- Customer -->
                                <td class="px-4 py-2">
                                    <div class="font-medium text-[#111827] truncate">{{ $order->user?->name ?? 'Guest' }}</div>
                                    <div class="text-[11px] text-[#6B7280] truncate">{{ $order->user?->email }}</div>
                                </td>

                                <!-- Domain -->
                                <td class="px-4 py-2 font-mono text-xs text-[#111827]">
                                    {{ $domainDisplay }}
                                    @if($itemCount > 1)
                                        <span class="text-[11px] text-[#6B7280] font-sans">+{{ $itemCount - 1 }} more</span>
                                    @endif
                                </td>

                                <!-- Type -->
                                <td class="px-4 py-2 text-[#6B7280]">
                                    {{ $orderType }}
                                </td>

                                <!-- Amount -->
                                <td class="px-4 py-2 text-right font-mono font-medium text-[#111827] tabular-nums">
                                    ${{ number_format($order->total_amount, 2) }}
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-2">
                                    <x-cf.status-dot :status="$statusVariant" :label="ucfirst($order->status)" />
                                </td>

                                <!-- Time -->
                                <td class="px-4 py-2 text-[12px] text-[#6B7280]">
                                    {{ $order->created_at->diffForHumans() }}
                                </td>

                                <!-- Action Menu -->
                                <td class="px-4 py-2 text-right" @click.stop>
                                    <div class="relative inline-block" x-data="{ menuOpen: false }">
                                        <button @click="menuOpen = !menuOpen"
                                                type="button"
                                                class="w-7 h-7 flex items-center justify-center text-[#6B7280] hover:text-[#111827] hover:bg-[#E5E7EB]/50 rounded-[4px] transition">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <circle cx="12" cy="5" r="1.75" />
                                                <circle cx="12" cy="12" r="1.75" />
                                                <circle cx="12" cy="19" r="1.75" />
                                            </svg>
                                        </button>
                                        <div x-show="menuOpen"
                                             x-cloak
                                             @click.away="menuOpen = false"
                                             class="absolute right-0 mt-1 w-36 bg-white rounded-[6px] border border-[#E5E7EB] shadow-dropdown py-1 z-30 text-[12px]">
                                            <button type="button"
                                                    @click="menuOpen = false; openOrder(@js($orderPayload))"
                                                    class="w-full text-left px-3 py-1.5 text-[#111827] hover:bg-[#F7F7F8]">
                                                Quick view
                                            </button>
                                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                               class="block px-3 py-1.5 text-[#111827] hover:bg-[#F7F7F8]">
                                                Order details
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- SLIDE-OVER SIDE DRAWER: Order Details & Provisioning Log -->
    <div x-show="drawerOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden"
         role="dialog"
         aria-modal="true">

        <!-- Backdrop -->
        <div x-show="drawerOpen"
             x-transition:enter="ease-in-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeDrawer()"
             class="fixed inset-0 bg-[#111827]/40"></div>

        <!-- Slide Drawer Panel -->
        <div class="fixed inset-y-0 right-0 max-w-full flex">
            <div x-show="drawerOpen"
                 x-transition:enter="transform transition ease-in-out duration-150"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-150"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="w-screen max-w-md bg-white border-l border-[#E5E7EB] shadow-modal flex flex-col">

                <!-- Drawer Header -->
                <div class="h-14 px-5 border-b border-[#E5E7EB] flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-[14px] font-semibold text-[#111827] font-mono" x-text="`Order #${selectedOrder?.order_number}`"></h3>
                        <p class="text-[11px] text-[#6B7280]" x-text="selectedOrder?.date"></p>
                    </div>
                    <button @click="closeDrawer()"
                            type="button"
                            class="p-1.5 text-[#6B7280] hover:text-[#111827] rounded-[4px] hover:bg-[#F7F7F8]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Drawer Body -->
                <div class="flex-1 overflow-y-auto p-5 space-y-5 text-[13px]">

                    <!-- Customer Section -->
                    <div class="space-y-1">
                        <span class="text-[11px] font-medium text-[#6B7280]">Customer</span>
                        <div class="p-3 bg-[#F7F7F8] border border-[#E5E7EB] rounded-[6px]">
                            <p class="font-medium text-[#111827]" x-text="selectedOrder?.customer_name"></p>
                            <p class="text-[12px] text-[#6B7280]" x-text="selectedOrder?.customer_email"></p>
                        </div>
                    </div>

                    <!-- Domain & Items -->
                    <div class="space-y-1">
                        <span class="text-[11px] font-medium text-[#6B7280]">Domain Item</span>
                        <div class="p-3 bg-[#F7F7F8] border border-[#E5E7EB] rounded-[6px] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-medium text-[#111827]" x-text="selectedOrder?.domain"></span>
                                <span class="text-[11px] text-[#6B7280]" x-text="selectedOrder?.type"></span>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-[#E5E7EB] text-xs">
                                <span class="text-[#6B7280]">Amount:</span>
                                <span class="font-mono font-semibold text-[#111827] tabular-nums" x-text="`$${selectedOrder?.amount}`"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="space-y-1">
                        <span class="text-[11px] font-medium text-[#6B7280]">Provisioning Status</span>
                        <div class="p-3 bg-[#F7F7F8] border border-[#E5E7EB] rounded-[6px] flex items-center justify-between">
                            <span class="font-medium text-[#111827]" x-text="selectedOrder?.status"></span>
                            <span class="text-[11px] text-[#6B7280]" x-text="selectedOrder?.time"></span>
                        </div>
                    </div>

                    <!-- Provisioning Logs -->
                    <div class="space-y-1">
                        <span class="text-[11px] font-medium text-[#6B7280]">Provisioning Log & Notes</span>
                        <div class="p-3 bg-[#111827] text-[#F9FAFB] rounded-[6px] font-mono text-[11px] leading-relaxed max-h-48 overflow-y-auto">
                            <p class="text-[#9CA3AF] mb-1">// System audit log</p>
                            <p x-text="selectedOrder?.notes"></p>
                        </div>
                    </div>

                </div>

                <!-- Drawer Footer -->
                <div class="p-4 border-t border-[#E5E7EB] flex items-center justify-between gap-3 shrink-0">
                    <button @click="closeDrawer()"
                            type="button"
                            class="cf-btn cf-btn-secondary cf-btn-sm flex-1">
                        Close
                    </button>
                    <a :href="selectedOrder?.view_url"
                       class="cf-btn cf-btn-primary cf-btn-sm flex-1 text-center">
                        View full order
                    </a>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection
