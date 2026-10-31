@extends('layouts.guest', ['title' => 'Refund & Cancellation Policy - HostNibo Domain Hub'])

@section('content')
<div class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <div class="mb-8">
        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-[4px] text-xs font-medium bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] mb-3">
            Merchant Billing Terms
        </div>
        <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight" style="color: var(--text);">Refund & Cancellation Policy</h1>
        <p class="text-xs mt-1 font-mono" style="color: var(--text-muted);">Last Updated: October 2026 • HostNibo Domain Hub Policy</p>
    </div>

    <div class="cf-panel p-6 sm:p-10 space-y-8 text-sm leading-relaxed" style="color: var(--text);">
        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">1. Domain Registrations and Renewals (Non-Refundable)</h2>
            <p style="color: var(--text-muted);">
                Under global ICANN guidelines and registry operator agreements, fees paid for successful domain registrations, renewals, and incoming domain transfers are non-refundable once provisioned at the central registry. Once a domain is allocated to an account, the registry levy is irreversible.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">2. Failed Provisioning & Automatic Balance Credit</h2>
            <p style="color: var(--text-muted);">
                If an order is placed and upstream registry allocation fails due to domain unavailability, registry downtime, or invalid registry contact constraints, the full paid amount is immediately credited back to the customer's HostNibo account wallet balance or refunded via the original payment channel upon written request.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">3. Wallet Balance Refunds</h2>
            <p style="color: var(--text-muted);">
                Unused account wallet funds deposited via bKash, Bank Transfer, or Stripe may be refunded back to the originating funding source within 14 days of deposit upon submitting a billing ticket, subject to applicable merchant gateway transaction processing charges.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold mb-2" style="color: var(--text);">4. Refund Processing Window</h2>
            <p style="color: var(--text-muted);">
                Approved refunds to local mobile payment services (bKash) or bank transfers are processed within 2 to 5 business days. International card refunds via Stripe reflect according to your issuing cardholder bank (typically 5 to 10 working days).
            </p>
        </section>
    </div>
</div>
@endsection
