@extends('layouts.app')

@section('title', 'Currency Settings')
@section('page-title', 'Currency Settings')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('admin.settings.currency.update') }}" class="p-6 space-y-6">
            @csrf

            <!-- Default Currency -->
            <div>
                <label for="default_currency" class="block text-sm font-medium text-gray-700">Default Currency</label>
                <select name="default_currency" id="default_currency"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach($currencies as $code => $name)
                        <option value="{{ $code }}" {{ ($settings['default_currency'] ?? 'UGX') === $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Primary currency used for all transactions.</p>
                @error('default_currency')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Exchange Rate USD -->
            <div>
                <label for="exchange_rate_usd" class="block text-sm font-medium text-gray-700">USD Exchange Rate</label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                        1 USD =
                    </span>
                    <input type="number" name="exchange_rate_usd" id="exchange_rate_usd" step="0.01" min="0"
                           value="{{ old('exchange_rate_usd', $settings['exchange_rate_usd'] ?? 3700) }}"
                           class="flex-1 rounded-none rounded-r-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <p class="mt-1 text-xs text-gray-500">Current USD to local currency exchange rate.</p>
                @error('exchange_rate_usd')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
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
