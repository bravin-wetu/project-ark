@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Logins Today</p>
            <p class="text-2xl font-semibold text-green-600">{{ number_format($stats['logins_today']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Failed Logins Today</p>
            <p class="text-2xl font-semibold text-red-600">{{ number_format($stats['failed_logins_today']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Activities Today</p>
            <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['activities_today']) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search description..."
                   class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

            <select name="user_id" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>

            <select name="type" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Types</option>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <input type="date" name="start_date" value="{{ request('start_date') }}" 
                   class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            
            <input type="date" name="end_date" value="{{ request('end_date') }}" 
                   class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                Filter
            </button>

            @if(request()->hasAny(['search', 'user_id', 'type', 'start_date', 'end_date']))
                <a href="{{ route('admin.logs.activity') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Clear
                </a>
            @endif

            <a href="{{ route('admin.logs.activity.export', request()->all()) }}" 
               class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 ml-auto">
                Export CSV
            </a>
        </form>
    </div>

    <!-- Logs List -->
    <div class="bg-white rounded-lg shadow">
        <div class="divide-y">
            @forelse($logs as $log)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-start">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-{{ $log->type_color }}-100">
                            <span class="text-sm font-medium text-{{ $log->type_color }}-800">
                                {{ substr($log->type_label, 0, 1) }}
                            </span>
                        </span>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">{{ $log->description }}</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $log->type_color }}-100 text-{{ $log->type_color }}-800">
                                    {{ $log->type_label }}
                                </span>
                            </div>
                            <div class="mt-1 flex items-center text-xs text-gray-500 gap-3">
                                <span>{{ $log->user?->name ?? 'System' }}</span>
                                <span>&bull;</span>
                                <span>{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                                @if($log->ip_address)
                                    <span>&bull;</span>
                                    <span>{{ $log->ip_address }}</span>
                                @endif
                            </div>
                            @if($log->properties)
                                <div class="mt-2">
                                    <a href="{{ route('admin.logs.activity.show', $log) }}" class="text-xs text-blue-600 hover:text-blue-800">
                                        View Details
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    No activity logs found.
                </div>
            @endforelse
        </div>

        @if($logs->hasPages())
            <div class="px-4 py-3 border-t">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
