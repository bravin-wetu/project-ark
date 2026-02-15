@section('title', 'All Projects')

<x-app-layout>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-black">Projects</h1>
            <p class="text-gray-500">Manage donor-funded project workspaces</p>
        </div>
        <a href="{{ route('projects.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-black text-white text-sm font-medium rounded-md hover:bg-gray-800 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Project
        </a>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex items-center space-x-4">
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-500">Status:</span>
            <a href="{{ route('projects.index') }}" 
               class="px-3 py-1 text-sm rounded-full {{ !request('status') ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                All
            </a>
            <a href="{{ route('projects.index', ['status' => 'active']) }}" 
               class="px-3 py-1 text-sm rounded-full {{ request('status') === 'active' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Active
            </a>
            <a href="{{ route('projects.index', ['status' => 'draft']) }}" 
               class="px-3 py-1 text-sm rounded-full {{ request('status') === 'draft' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Draft
            </a>
            <a href="{{ route('projects.index', ['status' => 'closed']) }}" 
               class="px-3 py-1 text-sm rounded-full {{ request('status') === 'closed' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Closed
            </a>
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($projects as $project)
        <a href="{{ route('projects.show', $project) }}" 
           class="block bg-white rounded-lg border border-gray-200 p-5 hover:border-gray-300 hover:shadow-sm transition">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="font-semibold text-black">{{ $project->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $project->code }}</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $project->status === 'active' ? 'bg-black text-white' : '' }}
                    {{ $project->status === 'draft' ? 'bg-gray-100 text-gray-700' : '' }}
                    {{ $project->status === 'closed' ? 'bg-gray-200 text-gray-600' : '' }}
                    {{ $project->status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ ucfirst($project->status) }}
                </span>
            </div>

            <div class="mt-3 flex items-center text-sm text-gray-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                {{ $project->donor->name ?? 'No Donor' }}
            </div>

            <div class="mt-4">
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-gray-500">Budget Utilization</span>
                    <span class="font-medium text-black">{{ $project->utilization }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-black rounded-full h-2 transition-all" style="width: {{ min($project->utilization, 100) }}%"></div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
                <span class="text-gray-600">
                    {{ $project->currency }} {{ number_format($project->spent, 0) }} / {{ number_format($project->allocated, 0) }}
                </span>
                <span class="text-gray-500">
                    {{ $project->budgetLines->count() }} budget lines
                </span>
            </div>
        </a>
        @empty
        <div class="col-span-3 bg-white rounded-lg border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No projects yet</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new donor-funded project.</p>
            <div class="mt-6">
                <a href="{{ route('projects.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-black text-white text-sm font-medium rounded-md hover:bg-gray-800 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Project
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($projects->hasPages())
    <div class="mt-6">
        {{ $projects->links() }}
    </div>
    @endif
</x-app-layout>
