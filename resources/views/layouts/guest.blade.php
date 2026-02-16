<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; }
            .auth-gradient {
                background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
            }
            .glass-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
            }
            .input-modern {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .input-modern:focus {
                transform: translateY(-1px);
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }
            .btn-modern {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .btn-modern:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            }
            .fade-in {
                animation: fadeIn 0.6s ease-out forwards;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .pattern-grid {
                background-image: 
                    linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
                background-size: 50px 50px;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex">
            <!-- Left Panel - Branding -->
            <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
                <!-- Background Image -->
                <div class="absolute inset-0">
                    <img src="{{ asset('Rectangle-69.webp') }}" alt="Background" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-black/60"></div>
                </div>
                <!-- Decorative Elements -->
                <div class="absolute top-0 left-0 w-full h-full">
                    <div class="absolute top-20 left-20 w-72 h-72 bg-white/5 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-20 right-20 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
                </div>
                
                <div class="relative z-10 flex flex-col justify-between p-12 w-full">
                    <!-- Logo -->
                    <div>
                        <a href="/" class="inline-flex items-center space-x-3">
                            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                                <span class="text-black font-bold text-xl">W</span>
                            </div>
                            <span class="text-white text-xl font-semibold tracking-tight">WeTu</span>
                        </a>
                    </div>
                    
                    <!-- Center Content -->
                    <div class="max-w-md">
                        <h1 class="text-4xl font-bold text-white leading-tight mb-6">
                            Integrated Procurement & Budget Control
                        </h1>
                        <p class="text-gray-400 text-lg leading-relaxed">
                            A unified platform for managing donor-funded projects and departmental budgets with complete transparency and control.
                        </p>
                        
                        <!-- Features -->
                        <div class="mt-10 space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-300">Real-time budget tracking</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-300">Tiered approval workflows</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-300">Complete audit trail</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="text-gray-500 text-sm">
                        &copy; {{ date('Y') }} WeTu. All rights reserved.
                    </div>
                </div>
            </div>
            
            <!-- Right Panel - Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gray-50">
                <div class="w-full max-w-md fade-in">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden mb-8 text-center">
                        <a href="/" class="inline-flex items-center space-x-3">
                            <div class="w-10 h-10 bg-black rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-xl">W</span>
                            </div>
                            <span class="text-black text-xl font-semibold tracking-tight">WeTu</span>
                        </a>
                    </div>
                    
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
