<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteSettings->platform_name ?? 'Sourcing Portal')</title>
    
    <!-- Google Fonts for Premium Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (via Play CDN for rapid prototyping and rendering) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js for highly interactive UI controls -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Configure Tailwind for dynamic theme class support -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        theme: {
                            primary: 'var(--theme-primary)',
                            accent: 'var(--theme-accent)',
                            bg: 'var(--theme-bg)',
                            text: 'var(--theme-text)',
                            card: 'var(--theme-card)',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Inject Theme Stylesheets using CSS Custom Properties -->
    <style>
        /* Base Theme Tokens */
        :root {
            /* Theme Fallback (Royal Blue) */
            --theme-primary: #2563eb;
            --theme-accent: #3b82f6;
            
            /* Custom Colors for Dynamic User Theme preferences */
            --theme-bg: #ffffff;
            --theme-text: #1f2937;
            --theme-card: #f9fafb;
            --theme-border: #e5e7eb;
        }

        /* 1. Dark Slate Theme */
        .theme-dark {
            --theme-bg: #0f172a;
            --theme-text: #f8fafc;
            --theme-card: #1e293b;
            --theme-border: #334155;
            --theme-primary: #38bdf8;
            --theme-accent: #0284c7;
        }

        /* 2. Sepia Gold Theme */
        .theme-sepia {
            --theme-bg: #f5efe6;
            --theme-text: #433422;
            --theme-card: #eae2b7;
            --theme-border: #d6ccb1;
            --theme-primary: #b45309;
            --theme-accent: #d97706;
        }

        /* 3. Academic Blue Theme */
        .theme-academic_blue {
            --theme-bg: #f5f7ff;
            --theme-text: #0f2d59;
            --theme-card: #ffffff;
            --theme-border: #c7d2fe;
            --theme-primary: #3b82f6;
            --theme-accent: #2563eb;
        }

        /* 4. Forest Emerald Theme */
        .theme-forest_emerald {
            --theme-bg: #f4fbf7;
            --theme-text: #0f172a;
            --theme-card: #ffffff;
            --theme-border: #d1fae5;
            --theme-primary: #059669;
            --theme-accent: #047857;
        }

        /* 5. Vintage Plum Theme */
        .theme-vintage_plum {
            --theme-bg: #faf5ff;
            --theme-text: #1e1b4b;
            --theme-card: #ffffff;
            --theme-border: #e9d5ff;
            --theme-primary: #8b5cf6;
            --theme-accent: #7c3aed;
        }

        /* 6. Sunset Amber Theme (Fiery) */
        .theme-sunset_amber {
            --theme-bg: #fffaf5;
            --theme-text: #0f172a;
            --theme-card: #ffffff;
            --theme-border: #fed7aa;
            --theme-primary: #f97316;
            --theme-accent: #ea580c;
        }

        /* 7. Ocean Breeze Theme */
        .theme-ocean_breeze {
            --theme-bg: #f0f9ff;
            --theme-text: #0f172a;
            --theme-card: #ffffff;
            --theme-border: #bae6fd;
            --theme-primary: #0284c7;
            --theme-accent: #0369a1;
        }

        /* 8. Crimson Ruby Theme */
        .theme-crimson_ruby {
            --theme-bg: #fef2f2;
            --theme-text: #7f1d1d;
            --theme-card: #ffffff;
            --theme-border: #fecaca;
            --theme-primary: #b91c1c;
            --theme-accent: #ef4444;
        }

        /* 9. Nord Minimal Theme */
        .theme-nord_minimal {
            --theme-bg: #e5e9f0;
            --theme-text: #2e3440;
            --theme-card: #eceff4;
            --theme-border: #d8dee9;
            --theme-primary: #5e81ac;
            --theme-accent: #81a1c1;
        }

        /* 10. Unacademy Mint Theme */
        .theme-unacademy_mint {
            --theme-bg: #f5fdf9;
            --theme-text: #191d23;
            --theme-card: #ffffff;
            --theme-border: #a7f3d0;
            --theme-primary: #00c37b;
            --theme-accent: #00ab6c;
        }

        /* Institutional custom branding override */
        @if(isset($activeInstitution))
        :root {
            --theme-primary: {{ $activeInstitution->primary_color }};
            --theme-accent: {{ $activeInstitution->accent_color }};
        }
        @endif

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--theme-bg);
            color: var(--theme-text);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        /* Glassmorphism utility card */
        .glass-card {
            background: rgba(var(--theme-card), 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid var(--theme-border);
        }

        /* High-quality highlight fade animation for targeted notification items */
        :target {
            animation: highlight-fade 3s ease-in-out;
            border-color: var(--theme-primary) !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15) !important;
        }

        @keyframes highlight-fade {
            0% {
                background-color: rgba(37, 99, 235, 0.08);
            }
            100% {
                background-color: transparent;
            }
        }
    </style>
</head>
<body class="theme-{{ $themePreference ?? 'light' }} min-h-screen flex flex-col antialiased">

    @if(session()->has('impersonate_admin_id'))
        <div class="bg-amber-500 text-white px-4 py-2.5 text-center text-sm font-bold flex justify-center items-center gap-2 relative z-50 shadow-md">
            <span>👤 You are currently logged in as: <strong>{{ auth()->user()->name }}</strong> (Impersonating Mode)</span>
            <form action="{{ route('admin.impersonate-leave') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="ml-2 bg-white text-amber-600 px-3.5 py-1 rounded-full text-xs font-black hover:bg-amber-50 transition-all duration-200 active:scale-95 cursor-pointer shadow-sm">
                    Return to Admin Panel ↩
                </button>
            </form>
        </div>
    @endif

    <!-- Top Navigation Header -->
    <header x-data="{ mobileMenuOpen: false }" class="shadow-sm border-b border-[var(--theme-border)] bg-[var(--theme-card)] sticky top-0 z-40 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-theme-primary text-white rounded-lg shadow-md flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <span class="text-xl font-bold bg-gradient-to-r from-theme-primary to-theme-accent bg-clip-text text-transparent">
                        {{ $siteSettings->platform_name ?? 'Sourcing Portal' }}
                    </span>
                    @if(isset($activeInstitution))
                        @auth
                            @if(auth()->user()->isAdmin() || (auth()->user()->isInstitution() && auth()->user()->id == $activeInstitution->user_id))
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-theme-primary/10 text-theme-primary border border-theme-primary/20">
                                    Branded: {{ $activeInstitution->company_name }}
                                </span>
                            @endif
                        @endauth
                    @endif
                </div>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden md:flex space-x-8 items-center text-sm">
                    <a href="{{ route('home') }}" class="text-theme-text/80 hover:text-theme-primary font-medium transition">Explore Jobs</a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.settings') }}" class="text-red-500 font-bold hover:text-red-600 transition flex items-center gap-1">⚙️ Admin Panel</a>
                        @elseif(auth()->user()->isInstitution())
                            <a href="{{ route('recruiter.dashboard') }}" class="text-theme-primary font-bold hover:text-theme-accent transition flex items-center gap-1">🏢 Recruiter Dashboard</a>
                        @elseif(auth()->user()->isCandidate())
                            <a href="{{ route('faculty.dashboard') }}" class="text-theme-primary font-bold hover:text-theme-accent transition flex items-center gap-1">🧑‍🏫 Faculty Dashboard</a>
                        @endif
                    @endauth
                </nav>

                <!-- Action Controls: Theme Switcher, Desktop Profile & Mobile hamburger menu -->
                <div class="flex items-center gap-2 md:gap-4">
                    
                    <!-- Real-Time Notification Center Dropdown (FB style, same to same) -->
                    @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="p-2 rounded-lg bg-[var(--theme-card)] hover:bg-theme-primary/10 border border-[var(--theme-border)] text-theme-text/80 flex items-center justify-center transition relative">
                            <span class="text-base">🔔</span>
                            @if($unreadNotificationsCount > 0)
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-600 text-white text-[10px] font-black rounded-full flex items-center justify-center animate-pulse">
                                    {{ $unreadNotificationsCount }}
                                </span>
                            @endif
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 rounded-2xl shadow-xl bg-[var(--theme-card)] border border-[var(--theme-border)] py-2 z-50 text-xs overflow-hidden" x-cloak>
                            <div class="flex justify-between items-center px-4 py-2 border-b border-[var(--theme-border)] font-bold text-theme-text bg-[var(--theme-card)]">
                                <span>Notifications</span>
                                @if($unreadNotificationsCount > 0)
                                    <form action="{{ route('notifications.read-all') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[10px] text-theme-primary hover:underline font-black uppercase tracking-wider">
                                            Mark all read
                                        </button>
                                    </form>
                                @endif
                            </div>
                            
                            <div class="max-h-72 overflow-y-auto divide-y divide-[var(--theme-border)]">
                                @forelse($notificationsList as $notif)
                                    <div class="relative group flex items-center justify-between hover:bg-[var(--theme-bg)] transition-colors">
                                        <!-- Main Notification Link -->
                                        <a href="{{ route('notifications.read', $notif->id) }}" class="flex-grow px-4 py-3 text-left space-y-1 {{ !$notif->is_read ? 'bg-theme-primary/5 font-semibold' : '' }}">
                                            <div class="flex justify-between items-start gap-2">
                                                <span class="font-bold text-theme-text flex items-center gap-1">
                                                    @if($notif->type === 'success') 🟢 @elseif($notif->type === 'warning') 🟡 @elseif($notif->type === 'alert') 🔴 @else 🔵 @endif
                                                    {{ $notif->title }}
                                                </span>
                                                <span class="text-[9px] text-theme-text/40 shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-theme-text/70 leading-normal text-[11px]">{{ $notif->message }}</p>
                                        </a>

                                        <!-- FB-Style Toggle Dot Button -->
                                        <div class="pr-4 py-3 shrink-0 flex items-center justify-center bg-transparent">
                                            <form action="{{ route('notifications.toggle-read', $notif->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" @click.stop="" class="w-3 h-3 rounded-full border-2 border-theme-primary transition-all duration-200 transform hover:scale-125 focus:outline-none cursor-pointer {{ !$notif->is_read ? 'bg-theme-primary' : 'bg-transparent border-theme-text/30' }}" title="{{ !$notif->is_read ? 'Mark as read' : 'Mark as unread' }}">
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8 text-theme-text/50">
                                        <span class="text-2xl block mb-1">📭</span>
                                        No new notifications.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @endauth

                    <!-- Dynamic Theme Selector -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="p-2 rounded-lg bg-theme-bg hover:bg-theme-primary/10 border border-[var(--theme-border)] text-theme-text/80 flex items-center gap-2 transition text-sm">
                            <span class="capitalize font-semibold text-xs md:text-sm">{{ str_replace('_', ' ', $themePreference ?? 'Light') }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-52 rounded-lg shadow-xl bg-[var(--theme-card)] border border-[var(--theme-border)] py-1 z-50 text-sm max-h-72 overflow-y-auto" x-cloak>
                            <a href="{{ route('set-theme', ['theme' => 'light']) }}" class="block px-4 py-2 hover:bg-theme-primary/15 transition text-theme-text">☀️ Light Mode</a>
                            <a href="{{ route('set-theme', ['theme' => 'dark']) }}" class="block px-4 py-2 hover:bg-theme-primary/15 transition text-theme-text">🌙 Dark Slate</a>
                            <a href="{{ route('set-theme', ['theme' => 'sepia']) }}" class="block px-4 py-2 hover:bg-theme-primary/15 transition text-theme-text">☕ Sepia Gold</a>
                            <a href="{{ route('set-theme', ['theme' => 'academic_blue']) }}" class="block px-4 py-2 hover:bg-theme-primary/15 transition text-theme-text">🎓 Academic Blue</a>
                            <a href="{{ route('set-theme', ['theme' => 'forest_emerald']) }}" class="block px-4 py-2 hover:bg-theme-primary/15 transition text-theme-text">🌲 Forest Emerald</a>
                            <a href="{{ route('set-theme', ['theme' => 'vintage_plum']) }}" class="block px-4 py-2 hover:bg-theme-primary/15 transition text-theme-text">🍇 Vintage Plum</a>
                            <a href="{{ route('set-theme', ['theme' => 'sunset_amber']) }}" class="block px-4 py-2 hover:bg-theme-primary/15 transition text-theme-text">🍊 Sunset Amber</a>
                            <a href="{{ route('set-theme', ['theme' => 'ocean_breeze']) }}" class="block px-4 py-2 hover:bg-theme-primary/15 transition text-theme-text">🌊 Ocean Breeze</a>
                            <a href="{{ route('set-theme', ['theme' => 'crimson_ruby']) }}" class="block px-4 py-2 hover:bg-theme-primary/15 transition text-theme-text">🌹 Crimson Ruby</a>
                            <a href="{{ route('set-theme', ['theme' => 'nord_minimal']) }}" class="block px-4 py-2 hover:bg-theme-primary/15 transition text-theme-text">⚖️ Nord Minimal</a>
                        </div>
                    </div>

                    <!-- User Actions (Desktop Only) -->
                    <div class="hidden md:flex items-center gap-3">
                        @auth
                            <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                            <a href="{{ route('logout') }}" class="px-4 py-2 text-sm rounded-lg text-white bg-red-600 hover:bg-red-700 font-semibold transition">Logout</a>
                        @else
                            <a href="{{ route('login-demo') }}" class="px-4 py-2 text-sm rounded-lg text-theme-text hover:bg-theme-primary/10 border border-[var(--theme-border)] font-semibold transition">
                                Sign In
                            </a>
                            <a href="{{ route('signup') }}" class="px-4 py-2 text-sm rounded-lg text-white bg-theme-primary hover:bg-theme-accent font-semibold transition shadow-md">
                                Sign Up
                            </a>
                        @endauth
                    </div>

                    <!-- Hamburger Mobile Toggle (Mobile Only) -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg border border-[var(--theme-border)] text-theme-text/80 hover:bg-theme-primary/10 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!mobileMenuOpen">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="mobileMenuOpen" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu Dropdown Panel -->
        <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" class="md:hidden border-t border-[var(--theme-border)] bg-[var(--theme-card)] px-4 py-4 space-y-3 shadow-inner" x-cloak>
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-theme-text/80 hover:bg-theme-primary/10 hover:text-theme-primary transition">Explore Jobs</a>
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.settings') }}" class="block px-3 py-2 rounded-lg text-base font-bold text-red-500 hover:bg-red-500/10 transition">⚙️ Admin Panel</a>
                @elseif(auth()->user()->isInstitution())
                    <a href="{{ route('recruiter.dashboard') }}" class="block px-3 py-2 rounded-lg text-base font-bold text-theme-primary hover:bg-theme-primary/10 transition">🏢 Recruiter Dashboard</a>
                @endif
                <div class="border-t border-[var(--theme-border)] pt-3 flex items-center justify-between px-3">
                    <span class="text-sm font-semibold text-theme-text/70">{{ auth()->user()->name }}</span>
                    <a href="{{ route('logout') }}" class="px-3 py-1.5 text-xs rounded-lg text-white bg-red-600 hover:bg-red-700 font-semibold transition">Logout</a>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('login-demo') }}" class="block text-center px-4 py-2.5 text-sm font-bold text-theme-text hover:bg-theme-primary/10 border border-[var(--theme-border)] rounded-lg transition">
                        Sign In
                    </a>
                    <a href="{{ route('signup') }}" class="block text-center px-4 py-2.5 text-sm font-bold text-white bg-theme-primary hover:bg-theme-accent rounded-lg shadow-md transition">
                        Sign Up
                    </a>
                </div>
            @endauth
        </div>
    </header>

            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-[var(--theme-border)] bg-[var(--theme-card)] text-theme-text/60 py-6 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
            <p>&copy; {{ date('Y') }} {{ $siteSettings->platform_name ?? 'Faculty Placement Service' }}. All rights reserved. Powered by Laravel 12.</p>
        </div>
    </footer>

    <!-- 🎨 Portal Dynamic Theme Engine Slide-out Customizer Widget -->
    <div x-data="{ 
        open: false, 
        primary: '{{ isset($activeInstitution) ? ($activeInstitution->branding_settings['primary_color'] ?? '#059669') : '#059669' }}', 
        accent: '{{ isset($activeInstitution) ? ($activeInstitution->branding_settings['accent_color'] ?? '#047857') : '#047857' }}',
        background: '#f4fbf7',
        text: '#0f172a',
        clearBodyTheme() {
            document.body.className = document.body.className.replace(/\btheme-\S+/g, '');
        }
    }">
        <!-- Floating Gear Button (Left side) -->
        <div class="fixed bottom-6 left-6 z-50">
            <button @click="open = true" class="p-4 bg-slate-900 text-white rounded-full shadow-2xl hover:scale-110 active:scale-95 transition transform duration-300 flex items-center justify-center border border-white/10 animate-bounce" title="Open Portal Theme Engine Demo Customizer">
                <svg class="w-6 h-6 animate-[spin_8s_linear_infinite] text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </button>
        </div>

        <!-- Slide-out Sidebar Panel (Same to Same c:\jobs) -->
        <div x-show="open" class="fixed inset-0 z-50 flex justify-end" x-cloak>
            <!-- Backdrop -->
            <div @click="open = false" class="absolute inset-0 bg-black/10 transition-opacity duration-300"></div>
            
            <!-- Panel Container -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-300 transform" 
                 x-transition:enter-start="translate-x-full" 
                 x-transition:enter-end="translate-x-0" 
                 x-transition:leave="transition ease-in duration-200 transform" 
                 x-transition:leave-start="translate-x-0" 
                 x-transition:leave-end="translate-x-full" 
                 class="relative w-80 max-w-full bg-white h-full shadow-2xl flex flex-col p-6 border-l border-gray-100 z-10 overflow-y-auto text-slate-800">
                
                <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-6">
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-lg">Portal Theme Engine</h4>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mt-0.5">Live Demo Preview</p>
                    </div>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold p-1">&times;</button>
                </div>

                <p class="text-xs text-gray-500 mb-4 leading-relaxed bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                    💡 <strong>Live Theme Selector:</strong> Click any preset below to custom-brand your recruitment portal!
                </p>

                <!-- Reset Button at the Top -->
                <button @click="primary='#059669'; accent='#047857'; background='#f4fbf7'; text='#0f172a'; clearBodyTheme();
                        document.documentElement.style.setProperty('--theme-primary', '#059669'); 
                        document.documentElement.style.setProperty('--theme-accent', '#047857');
                        document.documentElement.style.setProperty('--theme-bg', '#f4fbf7');
                        document.documentElement.style.setProperty('--theme-text', '#0f172a');
                        document.documentElement.style.setProperty('--theme-card', '#ffffff');
                        document.documentElement.style.setProperty('--theme-border', '#d1fae5')"
                        class="w-full mb-6 py-2.5 px-4 text-xs font-black text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-all shadow-md flex items-center justify-center gap-1.5 cursor-pointer">
                    🔄 Reset Theme to Defaults
                </button>

                <!-- Presets List (Same to Same c:\jobs) -->
                <div class="space-y-4 flex-grow">
                    <h5 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-3">Select Preset Theme</h5>
                    
                    <!-- Emerald -->
                    <button @click="primary='#059669'; accent='#047857'; background='#f4fbf7'; text='#0f172a'; clearBodyTheme();
                            document.documentElement.style.setProperty('--theme-primary', primary);
                            document.documentElement.style.setProperty('--theme-accent', accent);
                            document.documentElement.style.setProperty('--theme-bg', background);
                            document.documentElement.style.setProperty('--theme-text', text);
                            document.documentElement.style.setProperty('--theme-card', '#ffffff');
                            document.documentElement.style.setProperty('--theme-border', '#d1fae5')" 
                            class="w-full flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition text-left">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Emerald Mint (Default)</span>
                            <span class="text-[10px] text-gray-400 capitalize mt-0.5">Preset: emerald</span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <span class="w-3.5 h-3.5 rounded-full bg-[#059669] border border-white shadow-sm"></span>
                            <span class="w-3.5 h-3.5 rounded-full bg-[#1e293b] border border-white shadow-sm"></span>
                        </div>
                    </button>

                    <!-- Ocean -->
                    <button @click="primary='#0284c7'; accent='#0369a1'; background='#f0f9ff'; text='#0f172a'; clearBodyTheme();
                            document.documentElement.style.setProperty('--theme-primary', primary);
                            document.documentElement.style.setProperty('--theme-accent', accent);
                            document.documentElement.style.setProperty('--theme-bg', background);
                            document.documentElement.style.setProperty('--theme-text', text);
                            document.documentElement.style.setProperty('--theme-card', '#ffffff');
                            document.documentElement.style.setProperty('--theme-border', '#bae6fd')" 
                            class="w-full flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition text-left">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Ocean Breeze</span>
                            <span class="text-[10px] text-gray-400 capitalize mt-0.5">Preset: ocean</span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <span class="w-3.5 h-3.5 rounded-full bg-[#0284c7] border border-white shadow-sm"></span>
                            <span class="w-3.5 h-3.5 rounded-full bg-[#0f172a] border border-white shadow-sm"></span>
                        </div>
                    </button>

                    <!-- Royal -->
                    <button @click="primary='#3b82f6'; accent='#2563eb'; background='#f5f7ff'; text='#0f172a'; clearBodyTheme();
                            document.documentElement.style.setProperty('--theme-primary', primary);
                            document.documentElement.style.setProperty('--theme-accent', accent);
                            document.documentElement.style.setProperty('--theme-bg', background);
                            document.documentElement.style.setProperty('--theme-text', text);
                            document.documentElement.style.setProperty('--theme-card', '#ffffff');
                            document.documentElement.style.setProperty('--theme-border', '#c7d2fe')" 
                            class="w-full flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition text-left">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Royal Velvet</span>
                            <span class="text-[10px] text-gray-400 capitalize mt-0.5">Preset: royal</span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <span class="w-3.5 h-3.5 rounded-full bg-[#3b82f6] border border-white shadow-sm"></span>
                            <span class="w-3.5 h-3.5 rounded-full bg-[#1e1b4b] border border-white shadow-sm"></span>
                        </div>
                    </button>

                    <!-- Purple -->
                    <button @click="primary='#8b5cf6'; accent='#7c3aed'; background='#faf5ff'; text='#0f172a'; clearBodyTheme();
                            document.documentElement.style.setProperty('--theme-primary', primary);
                            document.documentElement.style.setProperty('--theme-accent', accent);
                            document.documentElement.style.setProperty('--theme-bg', background);
                            document.documentElement.style.setProperty('--theme-text', text);
                            document.documentElement.style.setProperty('--theme-card', '#ffffff');
                            document.documentElement.style.setProperty('--theme-border', '#e9d5ff')" 
                            class="w-full flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition text-left">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Purple Haze</span>
                            <span class="text-[10px] text-gray-400 capitalize mt-0.5">Preset: purple</span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <span class="w-3.5 h-3.5 rounded-full bg-[#8b5cf6] border border-white shadow-sm"></span>
                            <span class="w-3.5 h-3.5 rounded-full bg-[#1e1b4b] border border-white shadow-sm"></span>
                        </div>
                    </button>

                    <!-- Unacademy -->
                    <button @click="primary='#00c37b'; accent='#00ab6c'; background='#f5fdf9'; text='#191d23'; clearBodyTheme();
                            document.documentElement.style.setProperty('--theme-primary', primary);
                            document.documentElement.style.setProperty('--theme-accent', accent);
                            document.documentElement.style.setProperty('--theme-bg', background);
                            document.documentElement.style.setProperty('--theme-text', text);
                            document.documentElement.style.setProperty('--theme-card', '#ffffff');
                            document.documentElement.style.setProperty('--theme-border', '#a7f3d0')" 
                            class="w-full flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition text-left">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Unacademy Mint</span>
                            <span class="text-[10px] text-gray-400 capitalize mt-0.5">Preset: unacademy</span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <span class="w-3.5 h-3.5 rounded-full bg-[#00c37b] border border-white shadow-sm"></span>
                            <span class="w-3.5 h-3.5 rounded-full bg-[#191d23] border border-white shadow-sm"></span>
                        </div>
                    </button>

                    <!-- Fiery -->
                    <button @click="primary='#f97316'; accent='#ea580c'; background='#fffaf5'; text='#0f172a'; clearBodyTheme();
                            document.documentElement.style.setProperty('--theme-primary', primary);
                            document.documentElement.style.setProperty('--theme-accent', accent);
                            document.documentElement.style.setProperty('--theme-bg', background);
                            document.documentElement.style.setProperty('--theme-text', text);
                            document.documentElement.style.setProperty('--theme-card', '#ffffff');
                            document.documentElement.style.setProperty('--theme-border', '#fed7aa')" 
                            class="w-full flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition text-left">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Fiery Orange</span>
                            <span class="text-[10px] text-gray-400 capitalize mt-0.5">Preset: fiery</span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <span class="w-3.5 h-3.5 rounded-full bg-[#f97316] border border-white shadow-sm"></span>
                            <span class="w-3.5 h-3.5 rounded-full bg-[#1e293b] border border-white shadow-sm"></span>
                        </div>
                    </button>

                </div>

                <!-- Custom Color Pickers (Same to Same c:\jobs) -->
                <div class="border-t border-gray-150 pt-4 mt-4 space-y-3">
                    <h5 class="text-[10px] font-black text-slate-900 uppercase tracking-widest mb-2">Custom Color Palette</h5>
                    
                    <div class="grid grid-cols-1 gap-2.5 text-xs font-semibold text-gray-600">
                        <!-- Primary Brand Color Picker -->
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                            <span class="flex items-center gap-1.5">🟢 Brand Primary</span>
                            <input type="color" x-model="primary" @input="clearBodyTheme(); document.documentElement.style.setProperty('--theme-primary', primary)" class="w-8 h-8 rounded-lg cursor-pointer border-none bg-transparent" />
                        </div>

                        <!-- Page Background Color Picker -->
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                            <span class="flex items-center gap-1.5">⚪ Page Background</span>
                            <input type="color" x-model="background" @input="clearBodyTheme(); document.documentElement.style.setProperty('--theme-bg', background)" class="w-8 h-8 rounded-lg cursor-pointer border-none bg-transparent" />
                        </div>

                        <!-- Text Color Picker -->
                        <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                            <span class="flex items-center gap-1.5">🔵 Text & Accent</span>
                            <input type="color" x-model="text" @input="clearBodyTheme(); document.documentElement.style.setProperty('--theme-text', text)" class="w-8 h-8 rounded-lg cursor-pointer border-none bg-transparent" />
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="border-t border-gray-100 pt-4 mt-4 space-y-3 text-center">
                    <p class="text-[10px] text-gray-400 leading-normal">
                        Changes persist dynamically in the session. For production, simply change environment variables or DB configurations.
                    </p>
                </div>

            </div>
        </div>
    </div>

    <!-- 🧠 Smart Universal Scroll Position Restoration System -->
    <script>
        (function() {
            const path = window.location.pathname;
            const scrollKey = 'scroll_pos_' + path;
            
            // Save scroll position
            window.addEventListener('scroll', () => {
                sessionStorage.setItem(scrollKey, window.scrollY);
            });

            // Restore scroll position under certain conditions (reload, same-page form submit/action, etc.)
            window.addEventListener('DOMContentLoaded', () => {
                const isSamePage = document.referrer && document.referrer.includes(path);
                const isReload = window.performance && window.performance.navigation && window.performance.navigation.type === 1;
                const isModernReload = window.performance && window.performance.getEntriesByType && 
                                     window.performance.getEntriesByType('navigation')[0] && 
                                     window.performance.getEntriesByType('navigation')[0].type === 'reload';
                
                if (isSamePage || isReload || isModernReload) {
                    const scrollPos = sessionStorage.getItem(scrollKey);
                    if (scrollPos) {
                        setTimeout(() => {
                            window.scrollTo({
                                top: parseInt(scrollPos, 10),
                                behavior: 'instant'
                            });
                        }, 50);
                    }
                } else {
                    // Fresh landing, clear scroll pos to start at top
                    sessionStorage.removeItem(scrollKey);
                }
            });
        })();
    </script>

    <!-- 🛡️ Smart Silent CSRF Token Auto-Refresh System -->
    <script>
        (function() {
            document.addEventListener('submit', function(e) {
                const form = e.target;
                const csrfInput = form.querySelector('input[name="_token"]');
                
                // Only intercept POST forms that contain a CSRF token
                if (form.method.toLowerCase() === 'post' && csrfInput) {
                    e.preventDefault(); // Stop initial submit to check token
                    
                    fetch("{{ url('/refresh-csrf') }}")
                        .then(response => response.json())
                        .then(data => {
                            if (data.token) {
                                // Update all CSRF inputs on the page with the fresh token
                                document.querySelectorAll('input[name="_token"]').forEach(input => {
                                    input.value = data.token;
                                });
                                
                                // Update meta tag if present
                                const metaToken = document.querySelector('meta[name="csrf-token"]');
                                if (metaToken) {
                                    metaToken.setAttribute('content', data.token);
                                }
                                
                                // Silent success! Directly submit form
                                HTMLFormElement.prototype.submit.call(form);
                            } else {
                                HTMLFormElement.prototype.submit.call(form);
                            }
                        })
                        .catch(() => {
                            // Fallback to direct submit if error
                            HTMLFormElement.prototype.submit.call(form);
                        });
                }
            });
        })();
    </script>

</body>
</html>
