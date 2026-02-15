@extends('layouts.app')

@section('title', 'Fiscal Year Settings')
@section('page-title', 'Fiscal Year Settings')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('admin.settings.fiscal.update') }}" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Fiscal Year Start -->
                <div>
                    <label for="fiscal_year_start" class="block text-sm font-medium text-gray-700">Fiscal Year Start</label>
                    <input type="text" name="fiscal_year_start" id="fiscal_year_start" 
                           value="{{ old('fiscal_year_start', $settings['fiscal_year_start'] ?? '01-01') }}"
                           placeholder="MM-DD"
                           pattern="\d{2}-\d{2}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Format: MM-DD (e.g., 01-01 for January 1st)</p>
                    @error('fiscal_year_start')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fiscal Year End -->
                <div>
                    <label for="fiscal_year_end" class="block text-sm font-medium text-gray-700">Fiscal Year End</label>
                    <input type="text" name="fiscal_year_end" id="fiscal_year_end" 
                           value="{{ old('fiscal_year_end', $settings['fiscal_year_end'] ?? '12-31') }}"
                           placeholder="MM-DD"
                           pattern="\d{2}-\d{2}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Format: MM-DD (e.g., 12-31 for December 31st)</p>
                    @error('fiscal_year_end')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <p class="text-sm text-blue-800">
                    <strong>Note:</strong> Changing the fiscal year settings will affect budget periods and financial reports.
                    Existing data will not be automatically adjusted.
                </p>
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
