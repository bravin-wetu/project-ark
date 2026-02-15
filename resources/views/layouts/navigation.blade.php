<nav x-data="{ open: false }" class="bg-white border-b border-smoke-200 sticky top-0 z-40 backdrop-blur-lg bg-white/95">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center space-x-8">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="w-9 h-9 bg-ink-900 rounded-xl flex items-center justify-center shadow-soft group-hover:shadow-medium transition-shadow duration-200">
                        <span class="text-white font-bold text-sm">P</span>
                    </div>
                    <span class="text-lg font-semibold text-ink-900 tracking-tight">Procure</span>
                </a>
                
                <!-- Main Nav Links -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('projects.index') }}" class="nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                        Projects
                    </a>
                    <a href="{{ route('department-budgets.index') }}" class="nav-item {{ request()->routeIs('department-budgets.*') ? 'active' : '' }}">
                        Budgets
                    </a>
                    <a href="{{ route('analytics.index') }}" class="nav-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                        Analytics
                    </a>
                </div>
            </div>

            <!-- Right Side Navigation -->
            <div class="hidden sm:flex sm:items-center sm:space-x-3">
                <!-- Search Button -->
                <button class="p-2.5 text-smoke-500 hover:text-ink-900 rounded-xl hover:bg-smoke-100 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                
                <!-- New Project Button -->
                <a href="{{ route('projects.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Project
                </a>

                <!-- Notifications -->
                <x-notification-bell />

                <!-- User Menu -->
                <x-ui.dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="flex items-center space-x-2 p-1.5 rounded-xl hover:bg-smoke-100 transition-all duration-200">
                            <div class="w-8 h-8 bg-gradient-to-br from-ink-800 to-ink-900 rounded-lg flex items-center justify-center text-sm font-medium text-white shadow-soft">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <svg class="w-4 h-4 text-smoke-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-smoke-100">
                            <p class="text-sm font-medium text-ink-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-smoke-500 truncate">{{ Auth::user()->email }}</p>
                        </div>

                        <div class="py-1">
                            <x-ui.dropdown-item :href="route('profile.edit')">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-smoke-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Profile
                                </span>
                            </x-ui.dropdown-item>
                            <x-ui.dropdown-item href="#">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-smoke-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Settings
                                </span>
                            </x-ui.dropdown-item>
                        </div>

                        <div class="border-t border-smoke-100 py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-ui.dropdown-item 
                                    :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                >
                                    <span class="flex items-center text-red-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Log Out
                                    </span>
                                </x-ui.dropdown-item>
                            </form>
                        </div>
                    </x-slot>
                </x-ui.dropdown>
            </div>

            <!-- Mobile Hamburger -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" class="p-2 rounded-xl text-smoke-500 hover:text-ink-900 hover:bg-smoke-100 transition-all duration-200">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="sm:hidden border-t border-smoke-100 bg-white"
        style="display: none;"
    >
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-xl text-ink-700 hover:bg-smoke-100 {{ request()->routeIs('dashboard') ? 'bg-smoke-100 font-medium' : '' }}">
                Dashboard
            </a>
            <a href="{{ route('projects.index') }}" class="block px-4 py-3 rounded-xl text-ink-700 hover:bg-smoke-100 {{ request()->routeIs('projects.*') ? 'bg-smoke-100 font-medium' : '' }}">
                Projects
            </a>
            <a href="{{ route('department-budgets.index') }}" class="block px-4 py-3 rounded-xl text-ink-700 hover:bg-smoke-100 {{ request()->routeIs('department-budgets.*') ? 'bg-smoke-100 font-medium' : '' }}">
                Budgets
            </a>
        </div>

        <div class="pt-4 pb-3 border-t border-smoke-100 px-4">
            <div class="flex items-center px-4 mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-ink-800 to-ink-900 rounded-xl flex items-center justify-center text-sm font-medium text-white">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-ink-900">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-smoke-500">{{ Auth::user()->email }}</p>
                </div>
            </div>

            <div class="space-y-1">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-3 rounded-xl text-ink-700 hover:bg-smoke-100">
                    Profile
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-3 rounded-xl text-red-600 hover:bg-red-50">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
