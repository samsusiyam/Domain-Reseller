@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    items: [{ description: 'Custom Service / Domain Charge', quantity: 1, unit_price: 15.00 }]
}">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.invoices.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Create Custom Invoice</h1>
            <p class="text-xs text-slate-500 mt-0.5">Issue a manual invoice with custom line items to a client or reseller.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('admin.invoices.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Select Customer</label>
                    <select name="user_id" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-semibold">
                        <option value="">Select Client or Reseller...</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Due Date</label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('due_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Line Items Dynamic Grid -->
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Invoice Line Items</h4>
                    <button type="button" @click="items.push({ description: '', quantity: 1, unit_price: 10.00 })" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        + Add Another Item
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="flex flex-col sm:flex-row items-center gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200/80">
                            <div class="flex-1 w-full">
                                <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" placeholder="Item Description" required
                                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
                            </div>
                            <div class="w-full sm:w-24">
                                <input type="number" min="1" :name="'items[' + index + '][quantity]'" x-model="item.quantity" placeholder="Qty" required
                                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg text-center">
                            </div>
                            <div class="w-full sm:w-32">
                                <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" placeholder="Price (USD)" required
                                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg text-right">
                            </div>
                            <div>
                                <button type="button" @click="if(items.length > 1) items.splice(index, 1)" class="p-2 text-rose-500 hover:text-rose-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Invoice Notes (Visible to Customer)</label>
                <textarea name="notes" rows="2" placeholder="Bank instructions, terms or custom scope of work..."
                    class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.invoices.index') }}" class="px-5 py-2.5 border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition text-sm">
                    Generate & Send Invoice
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
