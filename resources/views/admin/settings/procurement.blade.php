@extends('layouts.app')

@section('title', 'Procurement Settings')
@section('page-title', 'Procurement Settings')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('admin.settings.procurement.update') }}" class="p-6 space-y-6">
            @csrf

            <!-- Document Prefixes -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Document Prefixes</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="req_prefix" class="block text-sm font-medium text-gray-700">Requisition Prefix</label>
                        <input type="text" name="req_prefix" id="req_prefix" 
                               value="{{ old('req_prefix', $settings['req_prefix'] ?? 'REQ-') }}"
                               maxlength="10"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('req_prefix')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="rfq_prefix" class="block text-sm font-medium text-gray-700">RFQ Prefix</label>
                        <input type="text" name="rfq_prefix" id="rfq_prefix" 
                               value="{{ old('rfq_prefix', $settings['rfq_prefix'] ?? 'RFQ-') }}"
                               maxlength="10"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('rfq_prefix')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="po_prefix" class="block text-sm font-medium text-gray-700">PO Prefix</label>
                        <input type="text" name="po_prefix" id="po_prefix" 
                               value="{{ old('po_prefix', $settings['po_prefix'] ?? 'PO-') }}"
                               maxlength="10"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('po_prefix')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">Prefixes added before document numbers (e.g., REQ-2024-0001)</p>
            </div>

            <!-- Stock Threshold -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Inventory Settings</h3>
                <div>
                    <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700">Low Stock Alert Threshold</label>
                    <div class="mt-1 flex rounded-md shadow-sm max-w-xs">
                        <input type="number" name="low_stock_threshold" id="low_stock_threshold" min="0"
                               value="{{ old('low_stock_threshold', $settings['low_stock_threshold'] ?? 10) }}"
                               class="flex-1 rounded-l-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                            units
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Alert when inventory falls below this quantity.</p>
                    @error('low_stock_threshold')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-6 border-t">
                <a href="{{ route('admin.settings.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
