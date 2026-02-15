@extends('layouts.app')

@section('title', 'Email Settings')
@section('page-title', 'Email Settings')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Email Configuration -->
    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('admin.settings.email.update') }}" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- From Name -->
                <div>
                    <label for="mail_from_name" class="block text-sm font-medium text-gray-700">From Name</label>
                    <input type="text" name="mail_from_name" id="mail_from_name" 
                           value="{{ old('mail_from_name', $settings['mail_from_name'] ?? 'WeTu Procurement') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('mail_from_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- From Address -->
                <div>
                    <label for="mail_from_address" class="block text-sm font-medium text-gray-700">From Email</label>
                    <input type="email" name="mail_from_address" id="mail_from_address" 
                           value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('mail_from_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">SMTP Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Host -->
                    <div>
                        <label for="mail_host" class="block text-sm font-medium text-gray-700">SMTP Host</label>
                        <input type="text" name="mail_host" id="mail_host" 
                               value="{{ old('mail_host', $settings['mail_host'] ?? '') }}"
                               placeholder="smtp.example.com"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('mail_host')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Port -->
                    <div>
                        <label for="mail_port" class="block text-sm font-medium text-gray-700">SMTP Port</label>
                        <input type="number" name="mail_port" id="mail_port" 
                               value="{{ old('mail_port', $settings['mail_port'] ?? 587) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('mail_port')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="mail_username" class="block text-sm font-medium text-gray-700">SMTP Username</label>
                        <input type="text" name="mail_username" id="mail_username" 
                               value="{{ old('mail_username', $settings['mail_username'] ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('mail_username')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="mail_password" class="block text-sm font-medium text-gray-700">SMTP Password</label>
                        <input type="password" name="mail_password" id="mail_password" 
                               placeholder="Leave blank to keep current"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('mail_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Encryption -->
                    <div>
                        <label for="mail_encryption" class="block text-sm font-medium text-gray-700">Encryption</label>
                        <select name="mail_encryption" id="mail_encryption"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">None</option>
                            <option value="tls" {{ ($settings['mail_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                        </select>
                        @error('mail_encryption')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
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

    <!-- Test Email -->
    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('admin.settings.email.test') }}" class="p-6">
            @csrf
            <h3 class="text-lg font-medium text-gray-900 mb-4">Test Email Configuration</h3>
            <div class="flex gap-4">
                <div class="flex-1">
                    <label for="test_email" class="sr-only">Test Email Address</label>
                    <input type="email" name="test_email" id="test_email" 
                           placeholder="Enter email address to send test"
                           value="{{ auth()->user()->email }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Send Test Email
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
