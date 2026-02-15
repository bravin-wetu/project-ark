@extends('layouts.app')

@section('title', 'Role Management')
@section('page-title', 'Role Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <p class="text-gray-600">Manage user roles and permissions</p>
        <a href="{{ route('admin.roles.create') }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            Create Role
        </a>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $role->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $role->slug }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($role->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($role->description)
                        <p class="mt-3 text-sm text-gray-600">{{ $role->description }}</p>
                    @endif

                    <div class="mt-4">
                        <span class="text-sm text-gray-500">{{ $role->users_count }} user(s) assigned</span>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-medium text-gray-500 uppercase mb-2">Permissions</p>
                        @if(is_array($role->permissions) && in_array('*', $role->permissions))
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                Full Access
                            </span>
                        @elseif(is_array($role->permissions) && count($role->permissions) > 0)
                            <span class="text-sm text-gray-600">
                                {{ count($role->permissions) }} permission(s)
                            </span>
                        @else
                            <span class="text-sm text-gray-400">No permissions</span>
                        @endif
                    </div>
                </div>

                <div class="px-6 py-3 bg-gray-50 flex justify-end gap-2">
                    <a href="{{ route('admin.roles.show', $role) }}" 
                       class="text-sm text-blue-600 hover:text-blue-800">View</a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('admin.roles.edit', $role) }}" 
                       class="text-sm text-indigo-600 hover:text-indigo-800">Edit</a>
                    <span class="text-gray-300">|</span>
                    <form method="POST" action="{{ route('admin.roles.clone', $role) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-green-600 hover:text-green-800">Clone</button>
                    </form>
                    @if(!in_array($role->slug, [\App\Models\Role::ADMIN, \App\Models\Role::STAFF]))
                        <span class="text-gray-300">|</span>
                        <form method="POST" action="{{ route('admin.roles.toggle-status', $role) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-sm {{ $role->is_active ? 'text-orange-600 hover:text-orange-800' : 'text-green-600 hover:text-green-800' }}">
                                {{ $role->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
