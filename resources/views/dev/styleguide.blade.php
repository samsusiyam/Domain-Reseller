@extends('layouts.guest', ['title' => 'Design System Style Guide'])

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between pb-6 border-b" style="border-color: var(--border);">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--text);">Design System Style Guide</h1>
            <p class="text-xs mt-1" style="color: var(--text-muted);">
                Reference implementation of the Cloudflare-style minimal, functional UI components.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs" style="color: var(--text-muted);">Preview theme:</span>
            <x-theme-toggle />
        </div>
    </div>

    <!-- Section 1: Color Tokens -->
    <div class="mt-8">
        <h2 class="text-base font-semibold mb-4" style="color: var(--text);">Color Tokens</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
            <div class="p-3 rounded-[6px] border text-xs" style="background-color: var(--bg); border-color: var(--border); color: var(--text);">
                <div class="w-full h-8 rounded-[4px] mb-2 border" style="background-color: var(--bg); border-color: var(--border);"></div>
                <div class="font-medium">--bg</div>
                <div class="text-[11px]" style="color: var(--text-muted);">Base background</div>
            </div>
            <div class="p-3 rounded-[6px] border text-xs" style="background-color: var(--surface); border-color: var(--border); color: var(--text);">
                <div class="w-full h-8 rounded-[4px] mb-2 border" style="background-color: var(--surface); border-color: var(--border);"></div>
                <div class="font-medium">--surface</div>
                <div class="text-[11px]" style="color: var(--text-muted);">Card/header surface</div>
            </div>
            <div class="p-3 rounded-[6px] border text-xs" style="background-color: var(--bg); border-color: var(--border); color: var(--text);">
                <div class="w-full h-8 rounded-[4px] mb-2 bg-[#F6821F]"></div>
                <div class="font-medium">--accent</div>
                <div class="text-[11px]" style="color: var(--text-muted);">#F6821F Brand</div>
            </div>
            <div class="p-3 rounded-[6px] border text-xs" style="background-color: var(--bg); border-color: var(--border); color: var(--text);">
                <div class="w-full h-8 rounded-[4px] mb-2" style="background-color: var(--border);"></div>
                <div class="font-medium">--border</div>
                <div class="text-[11px]" style="color: var(--text-muted);">Subtle divider</div>
            </div>
            <div class="p-3 rounded-[6px] border text-xs" style="background-color: var(--bg); border-color: var(--border); color: var(--text);">
                <div class="w-full h-8 rounded-[4px] mb-2 bg-[#16A34A]"></div>
                <div class="font-medium">--success</div>
                <div class="text-[11px]" style="color: var(--text-muted);">#16A34A Status</div>
            </div>
            <div class="p-3 rounded-[6px] border text-xs" style="background-color: var(--bg); border-color: var(--border); color: var(--text);">
                <div class="w-full h-8 rounded-[4px] mb-2 bg-[#DC2626]"></div>
                <div class="font-medium">--danger</div>
                <div class="text-[11px]" style="color: var(--text-muted);">#DC2626 Status</div>
            </div>
        </div>
    </div>

    <!-- Section 2: Buttons -->
    <div class="mt-10">
        <h2 class="text-base font-semibold mb-4" style="color: var(--text);">Buttons</h2>
        <div class="cf-panel p-6">
            <div class="flex items-center gap-3 flex-wrap">
                <button type="button" class="cf-btn cf-btn-primary">Primary action</button>
                <button type="button" class="cf-btn cf-btn-secondary">Secondary button</button>
                <button type="button" class="cf-btn cf-btn-ghost">Ghost button</button>
                <button type="button" class="cf-btn cf-btn-danger">Danger action</button>
                <button type="button" class="cf-btn cf-btn-primary cf-btn-sm">Small button</button>
                <button type="button" class="cf-btn cf-btn-secondary" disabled>Disabled state</button>
            </div>
        </div>
    </div>

    <!-- Section 3: Form Controls & Inputs -->
    <div class="mt-10">
        <h2 class="text-base font-semibold mb-4" style="color: var(--text);">Form Controls (Visible 1px Borders)</h2>
        <div class="cf-panel p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Standard text input</label>
                    <input type="text" placeholder="name@company.com" class="cf-input">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Select dropdown</label>
                    <select class="cf-select">
                        <option>Registration (.com)</option>
                        <option>Renewal (.net)</option>
                        <option>Transfer (.org)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Validation error</label>
                    <input type="text" value="invalid-input" class="cf-input" style="border-color: var(--danger);">
                    <p class="text-xs mt-1 font-medium" style="color: var(--danger);">Please enter a valid domain name.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Status Dots & Indicators -->
    <div class="mt-10">
        <h2 class="text-base font-semibold mb-4" style="color: var(--text);">Status Indicators (8px Dot + Label)</h2>
        <div class="cf-panel p-6">
            <div class="flex items-center gap-6 flex-wrap">
                <div class="cf-status-dot success">
                    <span>Operational</span>
                </div>
                <div class="cf-status-dot warning">
                    <span>Expiring soon</span>
                </div>
                <div class="cf-status-dot danger">
                    <span>Provisioning failed</span>
                </div>
                <div class="cf-status-dot info">
                    <span>Transfer in progress</span>
                </div>
                <div class="cf-status-dot neutral">
                    <span>Unregistered</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 5: Data Tables -->
    <div class="mt-10">
        <h2 class="text-base font-semibold mb-4" style="color: var(--text);">Table Component</h2>
        <div class="cf-table-wrapper">
            <table class="cf-table">
                <thead>
                    <tr>
                        <th>Domain name</th>
                        <th>Registrar</th>
                        <th>Status</th>
                        <th class="align-right">Wholesale rate</th>
                        <th class="align-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-medium">example-store.com</td>
                        <td>Spaceship</td>
                        <td><span class="cf-status-dot success">Active</span></td>
                        <td class="align-right"><x-price usd="10.49" /></td>
                        <td class="align-right"><button class="cf-btn cf-btn-ghost cf-btn-sm">Manage</button></td>
                    </tr>
                    <tr>
                        <td class="font-medium">app-registry.net</td>
                        <td>Namecheap</td>
                        <td><span class="cf-status-dot warning">Auto-renew pending</span></td>
                        <td class="align-right"><x-price usd="12.99" /></td>
                        <td class="align-right"><button class="cf-btn cf-btn-ghost cf-btn-sm">Manage</button></td>
                    </tr>
                    <tr>
                        <td class="font-medium">cloud-portal.io</td>
                        <td>Cosmotown</td>
                        <td><span class="cf-status-dot success">Active</span></td>
                        <td class="align-right"><x-price usd="32.50" /></td>
                        <td class="align-right"><button class="cf-btn cf-btn-ghost cf-btn-sm">Manage</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
