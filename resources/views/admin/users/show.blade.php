@extends('layouts.app')

@section('title', 'User Details - ' . $user->name)
@section('page-title', 'User Details')

@section('content')
<div class="space-y-6">
    <!-- User Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center">
                <div class="h-16 w-16 flex-shrink-0">
                    <span class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-xl font-medium">
                        {{ substr($user->name, 0, 2) }}
                    </span>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-500">{{ $user->email }}</p>
                    @if($user->job_title)
                        <p class="text-sm text-gray-500">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex gap-2">
                <a href="{{ route('admin.users.edit', $user) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Edit User
                </a>
                @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 {{ $user->is_active ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-md">
                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @if($user->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </dd>
                    </div>
                    @if($user->employee_id)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Employee ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->employee_id }}</dd>
                        </div>
                    @endif
                    @if($user->phone)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->phone }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Department</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->department?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Hub/Location</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->hub?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $stats['last_login']?->format('M d, Y H:i') ?? 'Never' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Roles -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Roles</h3>
                @if($user->roles->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($user->roles as $role)
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm font-medium text-gray-900">{{ $role->name }}</span>
                                <a href="{{ route('admin.roles.show', $role) }}" class="text-blue-600 hover:text-blue-800 text-xs">
                                    View
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No roles assigned.</p>
                @endif
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Requisitions</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $stats['requisitions_count'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Purchase Orders</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $stats['purchase_orders_count'] }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Sessions -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Active Sessions</h3>
                    @if($sessions->isNotEmpty())
                        <form method="POST" action="{{ route('admin.users.invalidate-sessions', $user) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                Invalidate All
                            </button>
                        </form>
                    @endif
                </div>
                <div class="divide-y">
                    @forelse($sessions as $session)
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $session->device_icon }}"></path>
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $session->browser }} on {{ $session->platform }}
                                        @if($session->is_current)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 ml-2">Current</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $session->ip_address }} &bull; Last active {{ $session->last_activity_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-500">
                            No active sessions.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                </div>
                <div class="divide-y max-h-96 overflow-y-auto">
                    @forelse($recentActivity as $activity)
                        <div class="p-4">
                            <div class="flex items-start">
                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-{{ $activity->type_color }}-100">
                                    <span class="text-xs font-medium text-{{ $activity->type_color }}-800">
                                        {{ substr($activity->type_label, 0, 1) }}
                                    </span>
                                </span>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                                    <div class="mt-1 flex items-center text-xs text-gray-500">
                                        <span>{{ $activity->created_at->format('M d, Y H:i') }}</span>
                                        @if($activity->ip_address)
                                            <span class="mx-1">&bull;</span>
                                            <span>{{ $activity->ip_address }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-500">
                            No recent activity.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
