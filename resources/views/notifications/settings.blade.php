<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('notifications.index') }}" class="text-smoke-400 hover:text-ink-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="text-xl font-semibold text-ink-900">Notification Settings</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('notifications.settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Email Notifications -->
                <div class="card mb-6">
                    <div class="px-6 py-4 border-b border-smoke-200">
                        <h3 class="font-medium text-ink-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-smoke-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Email Notifications
                        </h3>
                        <p class="text-sm text-smoke-500 mt-1">Choose which events trigger email notifications.</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <h4 class="text-sm font-medium text-smoke-700 uppercase tracking-wider">Requisitions</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="email_requisition_submitted" value="1" 
                                       {{ $settings->email_requisition_submitted ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Requisition submitted for approval</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="email_requisition_approved" value="1"
                                       {{ $settings->email_requisition_approved ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Requisition approved</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="email_requisition_rejected" value="1"
                                       {{ $settings->email_requisition_rejected ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Requisition rejected</span>
                            </label>
                        </div>

                        <hr class="border-smoke-200">

                        <h4 class="text-sm font-medium text-smoke-700 uppercase tracking-wider">Purchase Orders</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="email_po_created" value="1"
                                       {{ $settings->email_po_created ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">PO created from RFQ</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="email_po_approved" value="1"
                                       {{ $settings->email_po_approved ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">PO approved</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="email_po_sent" value="1"
                                       {{ $settings->email_po_sent ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">PO sent to supplier</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="email_goods_received" value="1"
                                       {{ $settings->email_goods_received ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Goods received</span>
                            </label>
                        </div>

                        <hr class="border-smoke-200">

                        <h4 class="text-sm font-medium text-smoke-700 uppercase tracking-wider">Alerts</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="email_budget_threshold" value="1"
                                       {{ $settings->email_budget_threshold ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Budget threshold exceeded</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="email_stock_low" value="1"
                                       {{ $settings->email_stock_low ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Low stock alerts</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="email_asset_maintenance" value="1"
                                       {{ $settings->email_asset_maintenance ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Asset maintenance due</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- In-App Notifications -->
                <div class="card mb-6">
                    <div class="px-6 py-4 border-b border-smoke-200">
                        <h3 class="font-medium text-ink-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-smoke-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            In-App Notifications
                        </h3>
                        <p class="text-sm text-smoke-500 mt-1">These appear in the notification bell in the top navigation.</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <h4 class="text-sm font-medium text-smoke-700 uppercase tracking-wider">Requisitions</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="app_requisition_submitted" value="1"
                                       {{ $settings->app_requisition_submitted ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Requisition submitted for approval</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="app_requisition_approved" value="1"
                                       {{ $settings->app_requisition_approved ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Requisition approved</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="app_requisition_rejected" value="1"
                                       {{ $settings->app_requisition_rejected ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Requisition rejected</span>
                            </label>
                        </div>

                        <hr class="border-smoke-200">

                        <h4 class="text-sm font-medium text-smoke-700 uppercase tracking-wider">Purchase Orders</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="app_po_created" value="1"
                                       {{ $settings->app_po_created ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">PO created from RFQ</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="app_po_approved" value="1"
                                       {{ $settings->app_po_approved ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">PO approved</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="app_po_sent" value="1"
                                       {{ $settings->app_po_sent ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">PO sent to supplier</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="app_goods_received" value="1"
                                       {{ $settings->app_goods_received ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Goods received</span>
                            </label>
                        </div>

                        <hr class="border-smoke-200">

                        <h4 class="text-sm font-medium text-smoke-700 uppercase tracking-wider">Alerts</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="app_budget_threshold" value="1"
                                       {{ $settings->app_budget_threshold ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Budget threshold exceeded</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="app_stock_low" value="1"
                                       {{ $settings->app_stock_low ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Low stock alerts</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="app_asset_maintenance" value="1"
                                       {{ $settings->app_asset_maintenance ? 'checked' : '' }}
                                       class="form-checkbox rounded text-blue-600">
                                <span class="text-sm text-ink-900">Asset maintenance due</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Email Digest -->
                <div class="card mb-6">
                    <div class="px-6 py-4 border-b border-smoke-200">
                        <h3 class="font-medium text-ink-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-smoke-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Email Digest
                        </h3>
                        <p class="text-sm text-smoke-500 mt-1">Receive a summary of activity instead of individual emails.</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-ink-900 mb-2">Digest Frequency</label>
                                <select name="digest_frequency" class="form-select w-full">
                                    <option value="none" {{ $settings->digest_frequency === 'none' ? 'selected' : '' }}>No digest (individual emails)</option>
                                    <option value="daily" {{ $settings->digest_frequency === 'daily' ? 'selected' : '' }}>Daily digest</option>
                                    <option value="weekly" {{ $settings->digest_frequency === 'weekly' ? 'selected' : '' }}>Weekly digest</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink-900 mb-2">Delivery Time</label>
                                <input type="time" name="digest_time" 
                                       value="{{ $settings->digest_time ? \Carbon\Carbon::parse($settings->digest_time)->format('H:i') : '08:00' }}"
                                       class="form-input w-full">
                                <p class="text-xs text-smoke-500 mt-1">Time in your local timezone</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
