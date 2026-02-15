@extends('layouts.app')

@section('title', 'Notification Settings')
@section('page-title', 'Notification Settings')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('admin.settings.notifications.update') }}" class="p-6 space-y-6">
            @csrf

            <!-- Email Notifications -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Email Notifications</h3>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="hidden" name="email_notifications_enabled" value="0">
                        <input type="checkbox" name="email_notifications_enabled" value="1"
                               {{ ($settings['email_notifications_enabled'] ?? false) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-3 text-sm text-gray-700">Enable email notifications</span>
                    </label>
                </div>
            </div>

            <!-- Alert Types -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Alert Types</h3>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="hidden" name="low_stock_alerts_enabled" value="0">
                        <input type="checkbox" name="low_stock_alerts_enabled" value="1"
                               {{ ($settings['low_stock_alerts_enabled'] ?? false) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-3 text-sm text-gray-700">Low stock alerts</span>
                    </label>

                    <label class="flex items-center">
                        <input type="hidden" name="budget_alerts_enabled" value="0">
                        <input type="checkbox" name="budget_alerts_enabled" value="1"
                               {{ ($settings['budget_alerts_enabled'] ?? false) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-3 text-sm text-gray-700">Budget threshold alerts</span>
                    </label>

                    <label class="flex items-center">
                        <input type="hidden" name="approval_reminders_enabled" value="0">
                        <input type="checkbox" name="approval_reminders_enabled" value="1"
                               {{ ($settings['approval_reminders_enabled'] ?? false) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-3 text-sm text-gray-700">Pending approval reminders</span>
                    </label>
                </div>
            </div>

            <!-- Reminder Frequency -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Reminder Settings</h3>
                <div>
                    <label for="reminder_frequency_days" class="block text-sm font-medium text-gray-700">
                        Approval Reminder Frequency
                    </label>
                    <div class="mt-1 flex rounded-md shadow-sm max-w-xs">
                        <input type="number" name="reminder_frequency_days" id="reminder_frequency_days" 
                               min="1" max="30"
                               value="{{ old('reminder_frequency_days', $settings['reminder_frequency_days'] ?? 3) }}"
                               class="flex-1 rounded-l-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                            days
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Send reminders for pending approvals after this many days.</p>
                    @error('reminder_frequency_days')
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
