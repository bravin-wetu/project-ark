@extends('layouts.app')

@section('title', 'Role Details - ' . $role->name)
@section('page-title', 'Role Details')

@section('content')
<div class="space-y-6">
    <!-- Role Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $role->name }}</h2>
                    @if($role->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-1">{{ $role->slug }}</p>
                @if($role->description)
                    <p class="text-gray-600 mt-2">{{ $role->description }}</p>
                @endif
            </div>
            <div class="mt-4 md:mt-0 flex gap-2">
                <a href="{{ route('admin.roles.edit', $role) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Edit Role
                </a>
                <form method="POST" action="{{ route('admin.roles.clone', $role) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Clone
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Permissions -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Permissions</h3>
                </div>
                <div class="p-6">
                    @if(is_array($role->permissions) && in_array('*', $role->permissions))
                        <div class="bg-purple-50 border border-purple-200 rounded-md p-4">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="ml-2 text-sm font-medium text-purple-800">Full System Access</span>
                            </div>
                            <p class="mt-2 text-sm text-purple-700">This role has unrestricted access to all system features and functions.</p>
                        </div>
                    @elseif(is_array($role->permissions) && count($role->permissions) > 0)
                        <div class="space-y-4">
                            @foreach($allPermissions as $group => $groupPermissions)
                                @php
                                    $rolePerms = $role->permissions ?? [];
                                    $hasAnyInGroup = collect($groupPermissions)->keys()->intersect($rolePerms)->isNotEmpty();
                                @endphp
                                
                                @if($hasAnyInGroup)
                                    <div class="border rounded-lg p-4">
                                        <h4 class="font-medium text-gray-900 mb-3">{{ $group }}</h4>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($groupPermissions as $key => $label)
                                                @if(in_array($key, $rolePerms))
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                        {{ $label }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <p class="mt-2">No permissions assigned to this role.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Users -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Users Assigned</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $role->users_count }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Permissions Count</dt>
                        <dd class="text-sm font-medium text-gray-900">
                            @if(is_array($role->permissions) && in_array('*', $role->permissions))
                                <span class="text-purple-600">All</span>
                            @else
                                {{ is_array($role->permissions) ? count($role->permissions) : 0 }}
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Created</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $role->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Users with this Role</h3>
                    <span class="text-sm text-gray-500">({{ $role->users_count }} total)</span>
                </div>
                <div class="divide-y max-h-80 overflow-y-auto">
                    @forelse($users as $user)
                        <a href="{{ route('admin.users.show', $user) }}" class="block p-4 hover:bg-gray-50">
                            <div class="flex items-center">
                                <span class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-sm font-medium">
                                    {{ substr($user->name, 0, 2) }}
                                </span>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-4 text-center text-gray-500">
                            No users assigned to this role.
                        </div>
                    @endforelse
                </div>
                @if($role->users_count > 20)
                    <div class="px-6 py-3 bg-gray-50 text-center">
                        <a href="{{ route('admin.users.index', ['role_id' => $role->id]) }}" class="text-sm text-blue-600 hover:text-blue-800">
                            View all {{ $role->users_count }} users
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
