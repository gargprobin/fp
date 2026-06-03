<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="last-notification-id" content="{{ \App\Models\Notification::where('user_id', auth()->id())->latest()->value('id') ?? 0 }}">
    @else
        <meta name="last-vacancy-id" content="{{ \App\Models\Vacancy::open()->latest()->value('id') ?? 0 }}">
    @endauth
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
                    <!-- Real-Time Premium Notification Drawer -->
                    @auth
                    <div x-data="{ 
                        open: false, 
                        unreadCount: {{ $unreadNotificationsCount }},
                        currentFilter: 'unread',
                        showSettingsModal: false,
                        settings: {{ json_encode(auth()->user()->getNotificationSettings()) }},
                        notifications: [
                            @foreach($notificationsList as $notif)
                            {
                                id: {{ $notif->id }},
                                is_read: {{ $notif->is_read ? 'true' : 'false' }},
                                type: {{ json_encode($notif->type) }},
                                title: {{ json_encode($notif->title) }},
                                message: {{ json_encode($notif->message) }},
                                created_at_human: {{ json_encode($notif->created_at->diffForHumans()) }},
                                date_group: {{ json_encode($notif->created_at->isToday() ? 'Today' : ($notif->created_at->isYesterday() ? 'Yesterday' : 'Older')) }},
                                read_url: {{ json_encode(route('notifications.read', $notif->id)) }},
                                toggle_url: {{ json_encode(route('notifications.toggle-read', $notif->id)) }}
                            },
                            @endforeach
                        ],
                        toggleRead(notif) {
                            fetch(notif.toggle_url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(res => {
                                if (res.ok) {
                                    notif.is_read = !notif.is_read;
                                    this.unreadCount = this.notifications.filter(n => !n.is_read).length;
                                }
                            });
                        },
                        markAllRead() {
                            fetch('{{ route('notifications.read-all') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(res => {
                                if (res.ok) {
                                    this.notifications.forEach(n => n.is_read = true);
                                    this.unreadCount = 0;
                                    $dispatch('mark-all-read');
                                }
                            });
                        },
                        saveSettings() {
                            fetch('{{ route('notifications.save-settings') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(this.settings)
                            })
                            .then(res => {
                                if (res.ok) {
                                    this.showSettingsModal = false;
                                }
                            });
                        },
                        hasVisibleInGroup(group) {
                            return this.notifications.some(n => 
                                n.date_group === group && 
                                (this.currentFilter === 'all' || 
                                 (this.currentFilter === 'unread' && !n.is_read) || 
                                 (this.currentFilter === 'alerts' && (n.type === 'alert' || n.type === 'warning')))
                            );
                        }
                    }" class="relative">
                        <!-- Bell Icon Trigger -->
                        <button @click="open = true" class="p-2 rounded-lg bg-[var(--theme-card)] hover:bg-theme-primary/10 border border-[var(--theme-border)] text-theme-text/80 flex items-center justify-center transition relative cursor-pointer active:scale-95 duration-200">
                            <span class="text-base">🔔</span>
                            <span x-show="unreadCount > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-600 text-white text-[10px] font-black rounded-full flex items-center justify-center animate-pulse" x-text="unreadCount" x-cloak>
                                {{ $unreadNotificationsCount }}
                            </span>
                        </button>
                        
                        <!-- Backdrop (frosted overlay) -->
                        <div x-show="open" 
                             x-transition:enter="transition-opacity ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition-opacity ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             @click="open = false" 
                             class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50" 
                             x-cloak></div>
                        
                        <!-- Slide-out Drawer Panel -->
                        <div x-show="open" 
                             x-transition:enter="transform transition ease-out duration-300"
                             x-transition:enter-start="translate-x-full"
                             x-transition:enter-end="translate-x-0"
                             x-transition:leave="transform transition ease-in duration-200"
                             x-transition:leave-start="translate-x-0"
                             x-transition:leave-end="translate-x-full"
                             class="fixed right-0 top-0 bottom-0 w-full sm:w-[400px] bg-[var(--theme-bg)] border-l border-[var(--theme-border)] shadow-2xl z-[60] flex flex-col text-sm overflow-hidden" 
                             x-cloak>
                             
                             <!-- Header -->
                             <div class="px-5 py-4 border-b border-[var(--theme-border)] flex items-center justify-between bg-[var(--theme-bg)]">
                                 <div>
                                     <h2 class="text-lg font-black text-theme-text flex items-center gap-2 tracking-tight">
                                         Notifications
                                         <span x-show="unreadCount > 0" class="px-2 py-0.5 text-xs font-extrabold rounded-full bg-theme-primary/10 text-theme-primary" x-text="unreadCount"></span>
                                     </h2>
                                 </div>
                                 <div class="flex items-center gap-3">
                                     <!-- Help button -->
                                     <a href="mailto:{{ $siteSettings->support_email ?? 'support@sourcing.com' }}" class="px-3 py-1.5 rounded-full border border-theme-primary/20 bg-theme-primary/5 text-theme-primary hover:bg-theme-primary/10 transition text-[10px] font-black flex items-center gap-1.5 cursor-pointer">
                                         <span>💬</span> Need help?
                                     </a>
                                     <!-- Close Button -->
                                     <button @click="open = false" class="w-8 h-8 rounded-full hover:bg-theme-text/10 text-theme-text/80 flex items-center justify-center transition cursor-pointer border border-[var(--theme-border)] bg-[var(--theme-card)] shadow-xs" title="Close panel">
                                         <span class="text-lg font-bold">&times;</span>
                                     </button>
                                 </div>
                             </div>

                             <!-- Tab Filters & Settings Row -->
                             <div class="px-5 py-3 border-b border-[var(--theme-border)] flex justify-between items-center bg-[var(--theme-card)] select-none">
                                 <div class="flex gap-2">
                                     <button @click="currentFilter = 'unread'" 
                                             class="px-3.5 py-1.5 rounded-full text-xs font-black transition flex items-center gap-1.5 cursor-pointer"
                                             :class="currentFilter === 'unread' ? 'bg-theme-primary text-white shadow-md shadow-theme-primary/20' : 'bg-[var(--theme-bg)] text-theme-text/75 hover:bg-theme-primary/10'">
                                         Unread
                                         <span class="text-[9px] px-1.5 py-0.5 bg-red-500 text-white rounded-full font-black leading-none" x-text="unreadCount" x-show="unreadCount > 0"></span>
                                     </button>
                                     <button @click="currentFilter = 'all'" 
                                             class="px-3.5 py-1.5 rounded-full text-xs font-black transition cursor-pointer"
                                             :class="currentFilter === 'all' ? 'bg-theme-primary text-white shadow-md shadow-theme-primary/20' : 'bg-[var(--theme-bg)] text-theme-text/75 hover:bg-theme-primary/10'">
                                         All
                                     </button>
                                     <button @click="currentFilter = 'alerts'" 
                                             class="px-3.5 py-1.5 rounded-full text-xs font-black transition cursor-pointer"
                                             :class="currentFilter === 'alerts' ? 'bg-theme-primary text-white shadow-md shadow-theme-primary/20' : 'bg-[var(--theme-bg)] text-theme-text/75 hover:bg-theme-primary/10'">
                                         Alerts
                                     </button>
                                 </div>
                                 <div class="flex items-center gap-2">
                                     <!-- Mark all read icon/button if unread notifications exist -->
                                     <button x-show="unreadCount > 0" @click="markAllRead()" class="p-1.5 rounded-lg hover:bg-theme-text/10 text-theme-text/60 hover:text-theme-primary transition cursor-pointer font-bold text-xs" title="Mark all read" x-cloak>
                                         ✓✓ Mark all read
                                     </button>
                                     <button @click="showSettingsModal = true" class="p-1.5 rounded-lg hover:bg-theme-text/10 text-theme-text/60 hover:text-theme-primary transition cursor-pointer" title="Notification Settings">
                                         ⚙️
                                     </button>
                                 </div>
                             </div>
                             
                             <!-- Scrollable Content Body -->
                             <div class="flex-grow overflow-y-auto p-4 space-y-4 bg-[var(--theme-bg)]/20">
                                 
                                 <!-- Group: Today -->
                                 <div x-show="hasVisibleInGroup('Today')" class="space-y-2">
                                     <span class="px-2 text-[10px] font-black text-theme-text/40 uppercase tracking-wider block">Today</span>
                                     <div class="space-y-2">
                                         <template x-for="notif in notifications.filter(n => n.date_group === 'Today')" :key="notif.id">
                                             <div x-show="currentFilter === 'all' || (currentFilter === 'unread' && !notif.is_read) || (currentFilter === 'alerts' && (notif.type === 'alert' || notif.type === 'warning'))"
                                                  class="relative group flex items-start justify-between p-3.5 rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-card)] hover:bg-[var(--theme-bg)]/60 transition-all duration-200 shadow-xs overflow-hidden">
                                                 
                                                 <!-- Corner unread indicator -->
                                                 <div x-show="!notif.is_read" class="absolute top-0 right-0 w-0 h-0 border-t-[8px] border-r-[8px] border-t-amber-500 border-r-amber-500"></div>
                                                 
                                                 <!-- Info Link -->
                                                 <a :href="notif.read_url" class="flex-grow flex items-start gap-3 text-left">
                                                     <!-- Dynamic Icon Based on Type -->
                                                     <div class="p-2 rounded-full flex items-center justify-center shrink-0 border" 
                                                          :class="{
                                                              'bg-emerald-500/10 text-emerald-500 border-emerald-500/20': notif.type === 'success',
                                                              'bg-rose-500/10 text-rose-500 border-rose-500/20': notif.type === 'warning' || notif.type === 'alert',
                                                              'bg-blue-500/10 text-blue-500 border-blue-500/20': notif.type !== 'success' && notif.type !== 'warning' && notif.type !== 'alert'
                                                          }">
                                                         <!-- Success SVGs -->
                                                         <template x-if="notif.type === 'success'">
                                                             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                         </template>
                                                         <!-- Warning/Alert SVGs -->
                                                         <template x-if="notif.type === 'warning' || notif.type === 'alert'">
                                                             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                         </template>
                                                         <!-- Default SVGs -->
                                                         <template x-if="notif.type !== 'success' && notif.type !== 'warning' && notif.type !== 'alert'">
                                                             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                         </template>
                                                     </div>
                                                     <div class="space-y-0.5 pr-2">
                                                         <div class="flex items-baseline justify-between gap-2">
                                                             <span class="font-extrabold text-theme-text text-xs" :class="!notif.is_read ? 'text-theme-primary' : ''" x-text="notif.title"></span>
                                                         </div>
                                                         <p class="text-theme-text/70 text-[10px] leading-normal" x-text="notif.message"></p>
                                                         <span class="text-[8px] text-theme-text/40 block mt-1" x-text="notif.created_at_human"></span>
                                                     </div>
                                                 </a>
                                                 
                                                 <!-- Toggle Dot -->
                                                 <div class="shrink-0 flex items-center justify-center pt-2">
                                                     <button type="button" 
                                                             @click.stop="toggleRead(notif)" 
                                                             class="w-3 h-3 rounded-full border-2 border-theme-primary transition-all duration-200 transform hover:scale-125 focus:outline-none cursor-pointer"
                                                             :class="!notif.is_read ? 'bg-theme-primary' : 'bg-transparent border-theme-text/30'"
                                                             :title="!notif.is_read ? 'Mark as read' : 'Mark as unread'">
                                                     </button>
                                                 </div>
                                             </div>
                                         </template>
                                     </div>
                                 </div>
                                 
                                 <!-- Group: Yesterday -->
                                 <div x-show="hasVisibleInGroup('Yesterday')" class="space-y-2">
                                     <span class="px-2 text-[10px] font-black text-theme-text/40 uppercase tracking-wider block">Yesterday</span>
                                     <div class="space-y-2">
                                         <template x-for="notif in notifications.filter(n => n.date_group === 'Yesterday')" :key="notif.id">
                                             <div x-show="currentFilter === 'all' || (currentFilter === 'unread' && !notif.is_read) || (currentFilter === 'alerts' && (notif.type === 'alert' || notif.type === 'warning'))"
                                                  class="relative group flex items-start justify-between p-3.5 rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-card)] hover:bg-[var(--theme-bg)]/60 transition-all duration-200 shadow-xs overflow-hidden">
                                                 
                                                 <!-- Corner unread indicator -->
                                                 <div x-show="!notif.is_read" class="absolute top-0 right-0 w-0 h-0 border-t-[8px] border-r-[8px] border-t-amber-500 border-r-amber-500"></div>
                                                 
                                                 <!-- Info Link -->
                                                 <a :href="notif.read_url" class="flex-grow flex items-start gap-3 text-left">
                                                     <!-- Dynamic Icon -->
                                                     <div class="p-2 rounded-full flex items-center justify-center shrink-0 border" 
                                                          :class="{
                                                              'bg-emerald-500/10 text-emerald-500 border-emerald-500/20': notif.type === 'success',
                                                              'bg-rose-500/10 text-rose-500 border-rose-500/20': notif.type === 'warning' || notif.type === 'alert',
                                                              'bg-blue-500/10 text-blue-500 border-blue-500/20': notif.type !== 'success' && notif.type !== 'warning' && notif.type !== 'alert'
                                                          }">
                                                         <template x-if="notif.type === 'success'">
                                                             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                         </template>
                                                         <template x-if="notif.type === 'warning' || notif.type === 'alert'">
                                                             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                         </template>
                                                         <template x-if="notif.type !== 'success' && notif.type !== 'warning' && notif.type !== 'alert'">
                                                             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                         </template>
                                                     </div>
                                                     <div class="space-y-0.5 pr-2">
                                                         <div class="flex items-baseline justify-between gap-2">
                                                             <span class="font-extrabold text-theme-text text-xs" :class="!notif.is_read ? 'text-theme-primary' : ''" x-text="notif.title"></span>
                                                         </div>
                                                         <p class="text-theme-text/70 text-[10px] leading-normal" x-text="notif.message"></p>
                                                         <span class="text-[8px] text-theme-text/40 block mt-1" x-text="notif.created_at_human"></span>
                                                     </div>
                                                 </a>
                                                 
                                                 <!-- Toggle Dot -->
                                                 <div class="shrink-0 flex items-center justify-center pt-2">
                                                     <button type="button" 
                                                             @click.stop="toggleRead(notif)" 
                                                             class="w-3 h-3 rounded-full border-2 border-theme-primary transition-all duration-200 transform hover:scale-125 focus:outline-none cursor-pointer"
                                                             :class="!notif.is_read ? 'bg-theme-primary' : 'bg-transparent border-theme-text/30'"
                                                             :title="!notif.is_read ? 'Mark as read' : 'Mark as unread'">
                                                     </button>
                                                 </div>
                                             </div>
                                         </template>
                                     </div>
                                 </div>
                                 
                                 <!-- Group: Older -->
                                 <div x-show="hasVisibleInGroup('Older')" class="space-y-2">
                                     <span class="px-2 text-[10px] font-black text-theme-text/40 uppercase tracking-wider block">Older</span>
                                     <div class="space-y-2">
                                         <template x-for="notif in notifications.filter(n => n.date_group === 'Older')" :key="notif.id">
                                             <div x-show="currentFilter === 'all' || (currentFilter === 'unread' && !notif.is_read) || (currentFilter === 'alerts' && (notif.type === 'alert' || notif.type === 'warning'))"
                                                  class="relative group flex items-start justify-between p-3.5 rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-card)] hover:bg-[var(--theme-bg)]/60 transition-all duration-200 shadow-xs overflow-hidden">
                                                 
                                                 <!-- Corner unread indicator -->
                                                 <div x-show="!notif.is_read" class="absolute top-0 right-0 w-0 h-0 border-t-[8px] border-r-[8px] border-t-amber-500 border-r-amber-500"></div>
                                                 
                                                 <!-- Info Link -->
                                                 <a :href="notif.read_url" class="flex-grow flex items-start gap-3 text-left">
                                                     <!-- Dynamic Icon -->
                                                     <div class="p-2 rounded-full flex items-center justify-center shrink-0 border" 
                                                          :class="{
                                                              'bg-emerald-500/10 text-emerald-500 border-emerald-500/20': notif.type === 'success',
                                                              'bg-rose-500/10 text-rose-500 border-rose-500/20': notif.type === 'warning' || notif.type === 'alert',
                                                              'bg-blue-500/10 text-blue-500 border-blue-500/20': notif.type !== 'success' && notif.type !== 'warning' && notif.type !== 'alert'
                                                          }">
                                                         <template x-if="notif.type === 'success'">
                                                             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                         </template>
                                                         <template x-if="notif.type === 'warning' || notif.type === 'alert'">
                                                             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                         </template>
                                                         <template x-if="notif.type !== 'success' && notif.type !== 'warning' && notif.type !== 'alert'">
                                                             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                         </template>
                                                     </div>
                                                     <div class="space-y-0.5 pr-2">
                                                         <div class="flex items-baseline justify-between gap-2">
                                                             <span class="font-extrabold text-theme-text text-xs" :class="!notif.is_read ? 'text-theme-primary' : ''" x-text="notif.title"></span>
                                                         </div>
                                                         <p class="text-theme-text/70 text-[10px] leading-normal" x-text="notif.message"></p>
                                                         <span class="text-[8px] text-theme-text/40 block mt-1" x-text="notif.created_at_human"></span>
                                                     </div>
                                                 </a>
                                                 
                                                 <!-- Toggle Dot -->
                                                 <div class="shrink-0 flex items-center justify-center pt-2">
                                                     <button type="button" 
                                                             @click.stop="toggleRead(notif)" 
                                                             class="w-3 h-3 rounded-full border-2 border-theme-primary transition-all duration-200 transform hover:scale-125 focus:outline-none cursor-pointer"
                                                             :class="!notif.is_read ? 'bg-theme-primary' : 'bg-transparent border-theme-text/30'"
                                                             :title="!notif.is_read ? 'Mark as read' : 'Mark as unread'">
                                                     </button>
                                                 </div>
                                             </div>
                                         </template>
                                     </div>
                                 </div>

                                 <!-- Empty State -->
                                 <div x-show="notifications.filter(n => currentFilter === 'all' || (currentFilter === 'unread' && !n.is_read) || (currentFilter === 'alerts' && (n.type === 'alert' || n.type === 'warning'))).length === 0" 
                                      class="text-center py-16 text-theme-text/40 space-y-3" x-cloak>
                                     <span class="text-4xl block leading-none">📭</span>
                                     <p class="font-bold text-xs uppercase tracking-wider">No notifications found</p>
                                 </div>
                                 
                             </div>
                        </div>

                         <!-- Settings Modal -->
                         <div x-show="showSettingsModal" 
                              class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
                              x-cloak>
                              
                              <!-- Modal Container -->
                              <div @click.away="showSettingsModal = false" 
                                   class="w-full max-w-md bg-[var(--theme-card)] border border-[var(--theme-border)] rounded-3xl p-6 shadow-2xl space-y-6 text-sm text-theme-text relative">
                                   
                                   <!-- Close Button -->
                                   <button @click="showSettingsModal = false" class="absolute top-4 right-4 w-8 h-8 rounded-full hover:bg-theme-text/10 flex items-center justify-center transition cursor-pointer border border-[var(--theme-border)] bg-[var(--theme-card)]" title="Close">
                                       <span class="text-lg font-bold">&times;</span>
                                   </button>

                                   <!-- Header -->
                                   <div class="space-y-1">
                                       <h3 class="text-xl font-extrabold tracking-tight">Notification Settings</h3>
                                       <p class="text-xs text-theme-text/50">Manage how you receive alerts and communications</p>
                                   </div>

                                   <!-- Body Options -->
                                   <div class="space-y-4">
                                       <!-- Option 1: In-App -->
                                       <label class="flex items-center justify-between p-3 rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg)] hover:bg-[var(--theme-bg)]/80 cursor-pointer select-none">
                                           <div class="space-y-0.5">
                                               <span class="font-bold block">In-App Notifications</span>
                                               <span class="text-[10px] text-theme-text/50 block">Show real-time alerts inside the portal</span>
                                           </div>
                                           <input type="checkbox" x-model="settings.in_app" class="w-4 h-4 rounded text-theme-primary focus:ring-theme-primary cursor-pointer" />
                                       </label>

                                       <!-- Option 2: WhatsApp -->
                                       <label class="flex items-center justify-between p-3 rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg)] hover:bg-[var(--theme-bg)]/80 cursor-pointer select-none">
                                           <div class="space-y-0.5">
                                               <span class="font-bold block">WhatsApp Notifications</span>
                                               <span class="text-[10px] text-theme-text/50 block">Send real-time alerts via WhatsApp templates</span>
                                           </div>
                                           <input type="checkbox" x-model="settings.whatsapp" class="w-4 h-4 rounded text-theme-primary focus:ring-theme-primary cursor-pointer" />
                                       </label>

                                       <!-- Option 3: Email -->
                                       <label class="flex items-center justify-between p-3 rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg)] hover:bg-[var(--theme-bg)]/80 cursor-pointer select-none">
                                           <div class="space-y-0.5">
                                               <span class="font-bold block">Email Notifications</span>
                                               <span class="text-[10px] text-theme-text/50 block">Send digest summary emails to your inbox</span>
                                           </div>
                                           <input type="checkbox" x-model="settings.email" class="w-4 h-4 rounded text-theme-primary focus:ring-theme-primary cursor-pointer" />
                                       </label>

                                       <!-- DND Mode Section -->
                                       <div class="p-3.5 rounded-2xl border border-[var(--theme-border)] bg-[var(--theme-bg)] space-y-3">
                                           <label class="flex items-center justify-between cursor-pointer select-none">
                                               <div class="space-y-0.5">
                                                   <span class="font-bold block">Do Not Disturb (DND)</span>
                                                   <span class="text-[10px] text-theme-text/50 block">Silence notifications during quiet hours</span>
                                               </div>
                                               <input type="checkbox" x-model="settings.dnd" class="w-4 h-4 rounded text-theme-primary focus:ring-theme-primary cursor-pointer" />
                                           </label>

                                           <!-- Quiet Hours Selector -->
                                           <div x-show="settings.dnd" class="grid grid-cols-2 gap-3 pt-2.5 border-t border-[var(--theme-border)]" x-cloak>
                                               <div class="space-y-1">
                                                   <span class="text-[10px] font-bold text-theme-text/50 uppercase">Quiet Hours Start</span>
                                                   <input type="time" x-model="settings.dnd_start" class="w-full p-2 bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-1 focus:ring-theme-primary text-xs font-semibold" />
                                               </div>
                                               <div class="space-y-1">
                                                   <span class="text-[10px] font-bold text-theme-text/50 uppercase">Quiet Hours End</span>
                                                   <input type="time" x-model="settings.dnd_end" class="w-full p-2 bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-1 focus:ring-theme-primary text-xs font-semibold" />
                                               </div>
                                           </div>
                                       </div>
                                   </div>

                                   <!-- Actions Footer -->
                                   <div class="flex gap-3 pt-2">
                                       <button @click="showSettingsModal = false" class="flex-grow py-2.5 px-4 text-xs font-extrabold bg-[var(--theme-bg)] hover:bg-theme-text/5 border border-[var(--theme-border)] rounded-2xl transition cursor-pointer text-center">
                                           Cancel
                                       </button>
                                       <button @click="saveSettings()" class="flex-grow py-2.5 px-4 text-xs font-extrabold bg-theme-primary hover:bg-theme-accent text-white rounded-2xl transition cursor-pointer shadow-md shadow-theme-primary/25 text-center active:scale-95 duration-150">
                                           Save Preferences
                                       </button>
                                   </div>

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
                @elseif(auth()->user()->isCandidate())
                    <a href="{{ route('faculty.dashboard') }}" class="block px-3 py-2 rounded-lg text-base font-bold text-theme-primary hover:bg-theme-primary/10 transition">🧑‍🏫 Faculty Dashboard</a>
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

    <!-- 🎯 Auto-Scroll to Target and Highlight Glow (for notifications redirection) -->
    <script>
        (function() {
            window.addEventListener('load', () => {
                if (window.location.hash && (window.location.hash.startsWith('#app-') || window.location.hash.startsWith('#user-'))) {
                    // Wait slightly for Alpine tabs to toggle classes and show the elements in the DOM
                    setTimeout(() => {
                        const targetEl = document.querySelector(window.location.hash);
                        if (targetEl) {
                            targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }, 200);
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

    <!-- Real-time Polling & Toast Notifications System -->
    <div x-data="realtimePollingSystem()" x-init="initPolling()" class="fixed top-6 right-6 z-50 space-y-3 max-w-sm pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div 
                x-show="toast.show" 
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="p-4 rounded-2xl text-white border shadow-2xl backdrop-blur-md flex items-center gap-3 pointer-events-auto transition-all bg-slate-900/90 border-white/10"
            >
                <span x-text="toast.icon" class="text-xl shrink-0"></span>
                <div>
                    <p x-text="toast.message" class="text-xs font-bold leading-relaxed"></p>
                </div>
            </div>
        </template>
    </div>

    <script>
        function realtimePollingSystem() {
            return {
                toasts: [],
                lastHash: sessionStorage.getItem('realtime_hash') || null,
                lastNotificationId: parseInt(sessionStorage.getItem('last_notification_id') || document.querySelector('meta[name="last-notification-id"]')?.getAttribute('content') || 0),
                lastVacancyId: parseInt(sessionStorage.getItem('last_vacancy_id') || document.querySelector('meta[name="last-vacancy-id"]')?.getAttribute('content') || 0),
                pollInterval: 10000, // 10 seconds polling interval
                initPolling() {
                    // Save initial values to session storage
                    sessionStorage.setItem('last_notification_id', this.lastNotificationId);
                    sessionStorage.setItem('last_vacancy_id', this.lastVacancyId);

                    // Start polling
                    setInterval(() => this.checkUpdates(), this.pollInterval);
                },
                checkUpdates() {
                    const url = `{{ url('/realtime/check') }}?last_notification_id=${this.lastNotificationId}&last_vacancy_id=${this.lastVacancyId}`;
                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            let changeDetected = false;
                            let showToast = false;
                            let toastMessage = '';
                            let toastIcon = '';

                            if (data.notification) {
                                changeDetected = true;
                                showToast = true;
                                toastMessage = `${data.notification.title}: ${data.notification.message}`;
                                toastIcon = '🔔';
                                
                                // Update session storage ID
                                this.lastNotificationId = data.notification.id;
                                sessionStorage.setItem('last_notification_id', this.lastNotificationId);
                            } else if (data.vacancy) {
                                changeDetected = true;
                                showToast = true;
                                toastMessage = `🆕 New Job: "${data.vacancy.title}" has been posted by "${data.vacancy.company}"!`;
                                toastIcon = '💼';

                                // Update session storage ID
                                this.lastVacancyId = data.vacancy.id;
                                sessionStorage.setItem('last_vacancy_id', this.lastVacancyId);
                            } else if (this.lastHash && this.lastHash !== data.hash) {
                                changeDetected = true;
                            }

                            // Keep the hash updated
                            if (data.hash) {
                                sessionStorage.setItem('realtime_hash', data.hash);
                                this.lastHash = data.hash;
                            }

                            if (changeDetected) {
                                if (showToast) {
                                    this.showNotification(toastMessage, toastIcon);
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 3000);
                                } else {
                                    window.location.reload();
                                }
                            }
                        })
                        .catch(err => {
                            console.warn('Realtime sync check failed', err);
                        });
                },
                showNotification(message, icon = '💡') {
                    const id = Date.now();
                    this.toasts.push({
                        id: id,
                        message: message,
                        icon: icon,
                        show: true
                    });
                    setTimeout(() => {
                        const index = this.toasts.findIndex(t => t.id === id);
                        if (index !== -1) {
                            this.toasts[index].show = false;
                        }
                    }, 4000);
                }
            }
        }
    </script>

</body>
</html>
