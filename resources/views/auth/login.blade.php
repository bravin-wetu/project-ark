<x-guest-layout>
    <div class="space-y-8">
        <!-- Header -->
        <div class="text-center lg:text-left">
            <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Welcome back</h2>
            <p class="mt-2 text-gray-500">Enter your credentials to access your account</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <!-- Email Address -->
            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email address
                </label>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    autocomplete="username"
                    class="input-modern w-full px-4 py-3.5 bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900"
                    placeholder="you@example.com"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">
                            Forgot password?
                        </a>
                    @endif
                </div>
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                    class="input-modern w-full px-4 py-3.5 bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900"
                    placeholder="••••••••"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    name="remember"
                    class="w-4 h-4 border-gray-300 rounded text-gray-900 focus:ring-gray-900 focus:ring-offset-0"
                />
                <label for="remember_me" class="ml-3 text-sm text-gray-600">
                    Keep me signed in
                </label>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="btn-modern w-full py-3.5 px-4 bg-gray-900 hover:bg-black text-white font-medium rounded-xl focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2"
            >
                Sign in
            </button>
        </form>

        <!-- Divider -->
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-gray-50 text-gray-500">New to WeTu?</span>
            </div>
        </div>

        <!-- Register Link -->
        @if (Route::has('register'))
            <div class="text-center">
                <a href="{{ route('register') }}" class="text-sm font-medium text-gray-900 hover:underline">
                    Create an account
                </a>
            </div>
        @endif
    </div>
</x-guest-layout>
