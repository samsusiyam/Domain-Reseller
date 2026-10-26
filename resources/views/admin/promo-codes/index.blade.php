@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Promotional Discount Coupons</h1>
            <p class="text-xs text-slate-500 mt-1">Create discount codes for retail checkout campaigns and TLD registry promotions.</p>
        </div>
        <div>
            <a href="{{ route('admin.promo-codes.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition text-xs">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create Promo Code
            </a>
        </div>
    </div>

    <!-- Promo Codes Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($promoCodes->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-sm">
                No promotional codes created yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Coupon Code</th>
                            <th class="px-6 py-3.5">Discount</th>
                            <th class="px-6 py-3.5 text-center">Uses</th>
                            <th class="px-6 py-3.5">Valid Window</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($promoCodes as $promo)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4">
                                    <span class="font-mono font-black text-slate-900 text-base block">{{ $promo->code }}</span>
                                    <span class="text-xs text-slate-400">{{ $promo->description ?: 'No description' }}</span>
                                </td>
                                <td class="px-6 py-4 font-bold text-emerald-600">
                                    {{ $promo->discount_type === 'percentage' ? $promo->discount_value . '%' : '$' . number_format($promo->discount_value, 2) }}
                                    <span class="text-xs text-slate-400 uppercase font-normal font-sans">({{ $promo->discount_type }})</span>
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs">
                                    <strong>{{ $promo->orders_count ?? $promo->uses_count ?? 0 }}</strong>
                                    <span class="text-slate-400">/ {{ $promo->max_uses ?: '∞' }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    {{ $promo->valid_from ? $promo->valid_from->format('M d, Y') : 'Anytime' }} &rarr;
                                    {{ $promo->valid_to ? $promo->valid_to->format('M d, Y') : 'Ongoing' }}
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.promo-codes.edit', $promo->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold">Edit</a>
                                    <form action="{{ route('admin.promo-codes.destroy', $promo->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this promo code?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-semibold">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($promoCodes->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $promoCodes->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
