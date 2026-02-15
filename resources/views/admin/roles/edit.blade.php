@extends('layouts.app')

@section('title', 'Edit Role - ' . $role->name)
@section('page-title', 'Edit Role')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            @if($role->slug === \App\Models\Role::ADMIN)
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">System Role</h3>
                            <p class="mt-1 text-sm text-yellow-700">This is the administrator role. The slug and permissions cannot be changed.</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Role Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug *</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $role->slug) }}" required
                           pattern="[a-z0-9-]+"
                           {{ $role->slug === \App\Models\Role::ADMIN ? 'readonly' : '' }}
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 {{ $role->slug === \App\Models\Role::ADMIN ? 'bg-gray-100' : '' }}">
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="2"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $role->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                           {{ $role->slug === \App\Models\Role::ADMIN ? 'disabled checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-600">Active</span>
                </label>
            </div>

            <!-- Permissions -->
            @if($role->slug !== \App\Models\Role::ADMIN)
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
                    <p class="text-sm text-gray-500 mb-4">Select the permissions for this role.</p>

                    @php
                        $rolePermissions = is_array($role->permissions) ? $role->permissions : [];
                    @endphp

                    <div class="space-y-6">
                        @foreach($permissions as $group => $groupPermissions)
                            <div class="border rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-medium text-gray-900">{{ $group }}</h4>
                                    <button type="button" 
                                            onclick="toggleGroup('{{ Str::slug($group) }}')"
                                            class="text-sm text-blue-600 hover:text-blue-800">
                                        Toggle All
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    @foreach($groupPermissions as $key => $label)
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                                   class="permission-{{ Str::slug($group) }} rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                   {{ in_array($key, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-600">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
                    <div class="bg-purple-50 border border-purple-200 rounded-md p-4">
                        <p class="text-sm text-purple-800">
                            <span class="font-medium">Full Access</span> - The administrator role has access to all system features.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex justify-between gap-3 pt-6 border-t">
                <div>
                    @if(!in_array($role->slug, [\App\Models\Role::ADMIN, \App\Models\Role::STAFF]) && $role->users()->count() === 0)
                        <button type="button" onclick="document.getElementById('delete-form').submit()"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Delete Role
                        </button>
                    @endif
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.roles.show', $role) }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Update Role
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if(!in_array($role->slug, [\App\Models\Role::ADMIN, \App\Models\Role::STAFF]))
    <form id="delete-form" method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endif

@push('scripts')
<script>
    function toggleGroup(group) {
        const checkboxes = document.querySelectorAll('.permission-' + group);
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    }
</script>
@endpush
@endsection
