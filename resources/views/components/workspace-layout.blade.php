@props(['workspace', 'workspaceType' => 'projects'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Procure') }} - @yield('title', 'Workspace')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen flex flex-col">
            <!-- Top Navigation -->
            <nav class="bg-white border-b border-gray-200 flex-shrink-0">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-14">
                        <div class="flex items-center space-x-4">
                            <!-- Breadcrumb -->
                            <div class="flex items-center space-x-2 text-sm">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-gray-500">Recent</span>
                                <span class="text-gray-400">/</span>
                                <span class="font-medium text-black">{{ $workspaceType === 'projects' ? 'Project' : 'Department' }} Canvas</span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            @if($workspaceType === 'projects')
                            <a href="{{ route('projects.edit', $workspace) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                Edit project
                            </a>
                            @else
                            <a href="{{ route('department-budgets.edit', $workspace) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                Edit budget
                            </a>
                            @endif

                            <!-- Close -->
                            <a href="{{ route('dashboard') }}" class="p-2 text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="flex flex-1 overflow-hidden">
                <!-- Sidebar -->
                <aside class="w-56 bg-white border-r border-gray-200 flex-shrink-0 overflow-y-auto">
                    <nav class="p-4 space-y-1">
                        @php
                            $routePrefix = $workspaceType;
                            $workspaceId = $workspace->id;
                            $currentRoute = request()->route()->getName();
                        @endphp

                        <a href="{{ route($routePrefix . '.show', $workspaceId) }}"
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ str_ends_with($currentRoute, '.show') ? 'bg-gray-100 text-black' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3 {{ str_ends_with($currentRoute, '.show') ? 'text-black' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Overview
                        </a>

                        <a href="{{ route($routePrefix . '.requisitions.index', $workspaceId) }}"
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ str_contains($currentRoute, '.requisitions.') ? 'bg-gray-100 text-black' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3 {{ str_contains($currentRoute, '.requisitions.') ? 'text-black' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Requisitions
                        </a>

                        <a href="{{ route($routePrefix . '.rfqs.index', $workspaceId) }}"
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ str_contains($currentRoute, '.rfqs.') ? 'bg-gray-100 text-black' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3 {{ str_contains($currentRoute, '.rfqs.') ? 'text-black' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            RFQs
                        </a>

                        <a href="{{ route($routePrefix . '.quotes.index', $workspaceId) }}"
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ str_contains($currentRoute, '.quotes.') ? 'bg-gray-100 text-black' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3 {{ str_contains($currentRoute, '.quotes.') ? 'text-black' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Quote Analysis
                        </a>

                        <a href="{{ route($routePrefix . '.purchase-orders.index', $workspaceId) }}"
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ str_contains($currentRoute, '.purchase-orders.') ? 'bg-gray-100 text-black' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3 {{ str_contains($currentRoute, '.purchase-orders.') ? 'text-black' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Purchase Orders
                        </a>

                        <a href="{{ route($routePrefix . '.receipts.index', $workspaceId) }}"
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ str_contains($currentRoute, '.receipts.') ? 'bg-gray-100 text-black' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3 {{ str_contains($currentRoute, '.receipts.') ? 'text-black' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Receipts
                        </a>

                        <a href="{{ route($routePrefix . '.assets.index', $workspaceId) }}"
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ str_contains($currentRoute, '.assets.') ? 'bg-gray-100 text-black' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3 {{ str_contains($currentRoute, '.assets.') ? 'text-black' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Assets & Stock
                        </a>

                        <a href="{{ route($routePrefix . '.budget.index', $workspaceId) }}"
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ str_contains($currentRoute, '.budget.') ? 'bg-gray-100 text-black' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3 {{ str_contains($currentRoute, '.budget.') ? 'text-black' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Budget Tracker
                        </a>

                        <a href="{{ route($routePrefix . '.reports.index', $workspaceId) }}"
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ str_contains($currentRoute, '.reports.') ? 'bg-gray-100 text-black' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3 {{ str_contains($currentRoute, '.reports.') ? 'text-black' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Reports
                        </a>
                    </nav>
                </aside>

                <!-- Main Content -->
                <main class="flex-1 overflow-y-auto p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="fixed bottom-4 right-4 bg-black text-white px-4 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="fixed bottom-4 right-4 bg-red-600 text-white px-4 py-3 rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
        @endif
    </body>
</html>
