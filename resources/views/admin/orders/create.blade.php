@extends('layouts.admin', ['title' => 'Create New Order', 'header' => 'New Order'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Create Manual Order</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Add a new domain registration, renewal, or transfer order for a client</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.orders.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-6 shadow-xs">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider pb-3 border-b border-slate-100 dark:border-slate-800">
                1. Client Selection
            </h2>

            <div>
                <label for="user_id" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Assign to Client / Reseller <span class="text-red-500">*</span>
                </label>
                <select name="user_id" id="user_id" required class="cf-input w-full">
                    <option value="">-- Choose Client Account --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            #{{ $user->id }} — {{ $user->name }} ({{ $user->email }}) {{ $user->company_name ? "• {$user->company_name}" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <h2 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider pt-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                2. Domain Order Specifications
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="domain_name" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Domain Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="domain_name" id="domain_name" required
                           value="{{ old('domain_name') }}"
                           placeholder="e.g. example.com"
                           class="cf-input w-full font-mono text-sm">
                </div>

                <div>
                    <label for="item_type" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Order Action Type <span class="text-red-500">*</span>
                    </label>
                    <select name="item_type" id="item_type" required class="cf-input w-full">
                        <option value="domain_register" {{ old('item_type') == 'domain_register' ? 'selected' : '' }}>New Domain Registration</option>
                        <option value="domain_renew" {{ old('item_type') == 'domain_renew' ? 'selected' : '' }}>Domain Renewal</option>
                        <option value="domain_transfer" {{ old('item_type') == 'domain_transfer' ? 'selected' : '' }}>Incoming Domain Transfer</option>
                    </select>
                </div>

                <div>
                    <label for="years" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Registration Period (Years) <span class="text-red-500">*</span>
                    </label>
                    <select name="years" id="years" required class="cf-input w-full">
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ old('years', 1) == $i ? 'selected' : '' }}>{{ $i }} Year(s)</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label for="registrar_id" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Upstream Registrar Driver <span class="text-red-500">*</span>
                    </label>
                    <select name="registrar_id" id="registrar_id" required class="cf-input w-full">
                        @foreach($registrars as $reg)
                            <option value="{{ $reg->id }}" {{ old('registrar_id') == $reg->id ? 'selected' : '' }}>
                                {{ $reg->display_name }} ({{ ucfirst($reg->driver) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="total_amount" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Total Amount Charged (USD) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm">$</span>
                        <input type="number" step="0.01" min="0" name="total_amount" id="total_amount" required
                               value="{{ old('total_amount', '10.00') }}"
                               class="cf-input w-full pl-7 font-mono text-sm">
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Initial Order Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status" required class="cf-input w-full">
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending (Awaiting Accept / Payment)</option>
                        <option value="processing" {{ old('status') == 'processing' ? 'selected' : '' }}>Processing (In Queue)</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed (Already Provisioned)</option>
                    </select>
                </div>
            </div>

            <h2 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider pt-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                3. Automation & Billing Actions
            </h2>

            <div class="space-y-3 pt-1">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="create_invoice" value="1" checked
                           class="rounded border-slate-300 text-[#F6821F] focus:ring-[#F6821F]">
                    <span class="text-xs text-slate-700 dark:text-slate-300">
                        Generate official billing invoice for this order
                    </span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="mark_paid" value="1" checked
                           class="rounded border-slate-300 text-[#F6821F] focus:ring-[#F6821F]">
                    <span class="text-xs text-slate-700 dark:text-slate-300">
                        Mark invoice as <strong>PAID</strong> immediately (Manual Admin settlement)
                    </span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="provision_now" value="1"
                           class="rounded border-slate-300 text-[#F6821F] focus:ring-[#F6821F]">
                    <span class="text-xs text-slate-700 dark:text-slate-300">
                        ⚡ <strong>Provision Now:</strong> Dispatch live API registration to registrar immediately upon creation
                    </span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.orders.index') }}" class="cf-btn cf-btn-secondary text-xs">Cancel</a>
            <button type="submit" class="cf-btn cf-btn-primary text-xs inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Create Order
            </button>
        </div>
    </form>
</div>
@endsection
