@extends('layouts.app')

@section('title', 'General Settings')
@section('page-title', 'General Settings')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('admin.settings.general.update') }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <!-- Company Name -->
            <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700">Organization Name</label>
                <input type="text" name="company_name" id="company_name" 
                       value="{{ old('company_name', $settings['company_name'] ?? 'WeTu Organization') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-500">This name will be displayed across the system.</p>
                @error('company_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Company Logo -->
            <div>
                <label for="company_logo" class="block text-sm font-medium text-gray-700">Organization Logo</label>
                @if(isset($settings['company_logo']) && $settings['company_logo'])
                    <div class="mt-2 mb-3">
                        <img src="{{ Storage::url($settings['company_logo']) }}" alt="Current Logo" class="h-16 object-contain">
                        <p class="text-xs text-gray-500 mt-1">Current logo</p>
                    </div>
                @endif
                <input type="file" name="company_logo" id="company_logo" accept="image/*"
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-1 text-xs text-gray-500">Recommended size: 200x50 pixels. Max file size: 2MB.</p>
                @error('company_logo')
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
