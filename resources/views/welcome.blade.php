<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Staff Portal</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="antialiased font-sans bg-gray-900 overflow-hidden">
    <!-- Background Decoration -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 -left-4 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute top-20 -right-4 w-72 h-72 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
    </div>

    <div class="relative z-10 min-h-screen flex flex-col items-center justify-center p-6 bg-gradient-to-br from-gray-950 via-gray-900 to-indigo-950">

        <div class="w-full max-w-md">
            <!-- Logo area -->
            <div class="flex flex-col items-center mb-8">
                <div class="w-20 h-20 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-2xl shadow-indigo-500/50 mb-4 transition-transform hover:scale-110 duration-500">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white tracking-tight text-center">Management System</h1>
                <p class="text-indigo-300 mt-2 font-medium">Enterprise Restaurant Portal</p>
            </div>

            <!-- Login Card -->
            <div class="glass rounded-3xl p-8 shadow-2xl text-center">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 tracking-tight">Staff Access Point</h2>

                <div class="space-y-4">
                    @auth
                        <div class="p-4 bg-indigo-50 rounded-2xl mb-6">
                            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Authenticated User</p>
                            <p class="text-lg font-bold text-indigo-700">{{ Auth::user()->name }}</p>
                        </div>

                        <a href="{{ route('dashboard') }}" class="flex items-center justify-center w-full py-4 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition duration-300 transform hover:-translate-y-1 shadow-xl shadow-indigo-500/30">
                            Go to Dashboard
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    @else
                        <p class="text-gray-500 mb-8 leading-relaxed">Please sign in with your employee credentials to manage restaurant operations.</p>

                        <a href="{{ route('login') }}" class="flex items-center justify-center w-full py-4 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition duration-300 transform hover:-translate-y-1 shadow-xl shadow-indigo-500/30">
                            Staff Login
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                        </a>
                    @endauth
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200/50">
                    <p class="text-xs text-gray-400 uppercase tracking-widest font-bold">Internal Use Only</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-12 text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} {{ config('app.name') }} Management. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
