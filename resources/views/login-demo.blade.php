@extends('layouts.app')

@section('title', 'Demo Login - ' . ($siteSettings->platform_name ?? 'Sourcing Portal'))

@section('content')
<div class="max-w-md mx-auto my-6 sm:my-12 bg-[var(--theme-card)] rounded-2xl p-4 sm:p-8 shadow-xl border border-[var(--theme-border)] transition-all duration-300">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-extrabold bg-gradient-to-r from-theme-primary to-theme-accent bg-clip-text text-transparent mb-2">
            Welcome to {{ $siteSettings->platform_name ?? 'Sourcing Portal' }}
        </h1>
        <p class="text-sm text-theme-text/70">
            Select a role to instantly log in and experience the platform with pre-populated demo data, dynamic themes, and branding.
        </p>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-500/10 text-red-500 border border-red-500/20 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-4">
        
        <!-- Login as Candidate -->
        <a href="{{ route('login-quick', ['role' => 'candidate']) }}" class="group block p-5 rounded-xl bg-theme-primary/5 hover:bg-theme-primary/10 border border-theme-primary/20 hover:border-theme-primary/40 transition duration-300 transform hover:-translate-y-0.5">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-theme-primary/10 text-theme-primary rounded-lg group-hover:scale-110 transition duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-theme-text group-hover:text-theme-primary transition">Log in as Candidate (Faculty)</h3>
                    <p class="text-xs text-theme-text/60 mt-1">Explore job boards, set your theme preference, upload demo videos, and apply for teaching roles.</p>
                    <span class="inline-block mt-2 text-xs font-semibold text-theme-primary">amit.verma@iit.edu &rarr;</span>
                </div>
            </div>
        </a>

        <!-- Login as Institution -->
        <a href="{{ route('login-quick', ['role' => 'institution']) }}" class="group block p-5 rounded-xl bg-theme-accent/5 hover:bg-theme-accent/10 border border-theme-accent/20 hover:border-theme-accent/40 transition duration-300 transform hover:-translate-y-0.5">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-theme-accent/10 text-theme-accent rounded-lg group-hover:scale-110 transition duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-theme-text group-hover:text-theme-accent transition">Log in as Institution (Recruiter)</h3>
                    <p class="text-xs text-theme-text/60 mt-1">Manage institutional vacancies, review applicants, rate demo videos, and experience custom branding skins.</p>
                    <span class="inline-block mt-2 text-xs font-semibold text-theme-accent">recruiter@sourcing.com &rarr;</span>
                </div>
            </div>
        </a>

        <!-- Login as Super-Admin -->
        <a href="{{ route('login-quick', ['role' => 'admin']) }}" class="group block p-5 rounded-xl bg-red-500/5 hover:bg-red-500/10 border border-red-500/20 hover:border-red-500/40 transition duration-300 transform hover:-translate-y-0.5">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-red-500/10 text-red-500 rounded-lg group-hover:scale-110 transition duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-theme-text group-hover:text-red-500 transition">Log in as Platform Super-Admin</h3>
                    <p class="text-xs text-theme-text/60 mt-1">Manage global portal configurations, platform name, taglines, and settings dynamically from the Database.</p>
                    <span class="inline-block mt-2 text-xs font-semibold text-red-500">admin@sourcing.com &rarr;</span>
                </div>
            </div>
        </a>

    </div>
</div>
@endsection
