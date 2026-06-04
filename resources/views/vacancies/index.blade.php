@extends('layouts.app')

@section('title', 'Academic & Coaching Vacancies - ' . ($siteSettings->platform_name ?? 'Sourcing Portal'))

@section('content')
@php
    $appliedVacancyIds = [];
    if (auth()->check() && auth()->user()->isCandidate()) {
        $appliedVacancyIds = auth()->user()->applications->pluck('vacancy_id')->toArray();
    }
@endphp
<div class="space-y-8">
    
    <!-- Hero / Job Search Header -->
    <div class="p-8 rounded-2xl bg-gradient-to-br from-theme-primary/10 via-theme-accent/5 to-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm relative overflow-hidden transition-all duration-300">
        <div class="relative z-10 max-w-3xl">
            <h1 class="text-4xl font-extrabold tracking-tight mb-2">
                Find Your Next Faculty Placement on {{ $siteSettings->platform_name ?? 'Sourcing Portal' }}
            </h1>
            <p class="text-theme-text/70 text-lg mb-6">
                {{ $siteSettings->platform_tagline ?? "Connect with India's top coaching institutes, schools, colleges, and research universities." }}
            </p>

            <!-- Search Filter Form -->
            <form action="{{ route('vacancies.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 bg-[var(--theme-bg)] p-3 rounded-xl border border-[var(--theme-border)] shadow-md">
                <div class="flex-grow">
                    <input type="text" name="subject" value="{{ request('subject') }}" placeholder="Search by Subject (e.g., Physics, Computer Science)" class="w-full px-4 py-3 bg-[var(--theme-card)] border border-[var(--theme-border)] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" />
                </div>
                
                <div class="w-full md:w-48">
                    <input type="text" name="location" value="{{ request('location') }}" placeholder="Location (e.g., Kota)" class="w-full px-4 py-3 bg-[var(--theme-card)] border border-[var(--theme-border)] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" />
                </div>

                <div class="w-full md:w-48">
                    <select name="type" class="w-full px-4 py-3 bg-[var(--theme-card)] border border-[var(--theme-border)] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text/80">
                        <option value="">All Institutions</option>
                        <option value="coaching_center" {{ request('type') == 'coaching_center' ? 'selected' : '' }}>Coaching Centers</option>
                        <option value="university" {{ request('type') == 'university' ? 'selected' : '' }}>Universities</option>
                    </select>
                </div>

                <button type="submit" class="w-full md:w-auto px-6 py-3 bg-theme-primary hover:bg-theme-accent text-white font-semibold rounded-lg text-sm shadow-md transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Search
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-green-500/10 text-green-500 border border-green-500/20 font-semibold shadow-sm transition">
            {{ session('success') }}
        </div>
    @endif

    <!-- Vacancies List Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Vacancies List -->
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-2xl font-bold">Latest Job Openings ({{ $vacancies->total() }})</h2>
            
            @forelse($vacancies as $vacancy)
                @php
                    $inst = $vacancy->institution ?? (object) [
                        'company_name' => 'Confidential Partner Brand',
                        'primary_color' => '#2563eb',
                        'accent_color' => '#3b82f6',
                        'type' => 'institution',
                        'user_id' => 0
                    ];
                @endphp
                <div class="p-6 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 relative group">
                    
                    <!-- Decorative custom branding border to show institutional color identity -->
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-l-2xl" style="background-color: {{ $inst->primary_color }}"></div>
                    
                    <div class="flex justify-between items-start mb-3 pl-3">
                        <div>
                            <h3 class="text-xl font-bold text-theme-text group-hover:text-theme-primary transition">
                                <a href="{{ route('vacancies.show', $vacancy->id) }}">{{ $vacancy->title }}</a>
                            </h3>
                            <div class="flex items-center gap-2 text-sm text-theme-text/60 mt-1">
                                @php
                                    $showRealName = false;
                                    if (auth()->check()) {
                                        if (auth()->user()->isAdmin() || (auth()->user()->isInstitution() && auth()->user()->id == $inst->user_id)) {
                                            $showRealName = true;
                                        } elseif (auth()->user()->isCandidate()) {
                                            $userApp = \App\Models\Application::where('vacancy_id', $vacancy->id)
                                                ->where('candidate_id', auth()->id())
                                                ->first();
                                            if ($userApp && in_array($userApp->status, ['unlocked', 'written_test', 'interview', 'demo', 'appointed'])) {
                                                $showRealName = true;
                                            }
                                        }
                                    }
                                @endphp
                                <span class="font-semibold text-theme-text/80">
                                    {{ $showRealName ? $inst->company_name : 'Confidential Partner Brand' }}
                                </span>
                                <span>&bull;</span>
                                <span>{{ $vacancy->location }}</span>
                            </div>
                        </div>

                        <span class="px-3 py-1 text-xs font-semibold rounded-full text-white" style="background-color: {{ $inst->primary_color }}">
                            {{ ucfirst(str_replace('_', ' ', $inst->type)) }}
                        </span>
                    </div>

                    <p class="text-sm text-theme-text/75 pl-3 line-clamp-2">
                        {{ $vacancy->description }}
                    </p>

                    <!-- Vacancy Footer Metrics -->
                    <div class="mt-6 flex flex-wrap gap-4 items-center justify-between text-xs text-theme-text/60 pl-3 pt-4 border-t border-[var(--theme-border)]">
                        <div class="flex gap-4">
                            <span class="flex items-center gap-1">
                                📚 Subject: <strong class="text-theme-text/80">{{ $vacancy->subject }}</strong>
                            </span>
                            <span class="flex items-center gap-1">
                                🎓 Target Class: <strong class="text-theme-text/80">{{ $vacancy->target_class }}</strong>
                            </span>
                            <span class="flex items-center gap-1">
                                💼 Experience: <strong class="text-theme-text/80">{{ $vacancy->experience_required }}</strong>
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            @if(in_array($vacancy->id, $appliedVacancyIds))
                                <span class="px-3 py-1.5 text-xs font-black text-green-500 bg-green-500/10 border border-green-500/20 rounded-lg">
                                    ✓ Applied
                                </span>
                            @endif

                            @if($vacancy->salary_max)
                                <span class="font-bold text-theme-text/90 text-sm">
                                    ₹{{ number_format($vacancy->salary_min/100000, 1) }}L - ₹{{ number_format($vacancy->salary_max/100000, 1) }}L / yr
                                </span>
                            @endif

                            <a href="{{ route('vacancies.show', $vacancy->id) }}" class="px-4 py-2 font-bold rounded-lg text-white transition text-xs shadow" style="background-color: {{ $inst->primary_color }}">
                                View Details
                            </a>
                        </div>
                    </div>

                </div>
            @empty
                <div class="p-12 text-center border border-dashed border-[var(--theme-border)] rounded-2xl">
                    <p class="text-theme-text/60 text-lg">No open vacancies found matching your filters.</p>
                </div>
            @endforelse

            <div class="mt-6">
                {{ $vacancies->links() }}
            </div>
        </div>

        <!-- Right 1 Col: Quick Tour Sidebar & Interactive Panel -->
        <div class="space-y-6">
            
            <!-- Quick Demo Controller Panel -->
            <div class="p-6 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm">
                <h3 class="text-lg font-bold mb-4">🌟 Premium Demo Control</h3>
                <div class="space-y-4 text-sm">
                    @auth
                        <div class="p-4 rounded-xl bg-theme-primary/5 border border-theme-primary/10">
                            <p class="font-semibold">Logged in as:</p>
                            <p class="text-base text-theme-primary font-bold">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-theme-text/60">Role: {{ ucfirst(auth()->user()->role) }}</p>
                        </div>
                        
                        <div class="pt-2 border-t border-[var(--theme-border)]">
                            <p class="font-semibold text-xs text-theme-text/50 mb-2">Switch Theme Instantly:</p>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <a href="{{ route('set-theme', ['theme' => 'light']) }}" class="p-2 border border-[var(--theme-border)] rounded-lg text-center font-bold hover:bg-theme-primary/10 transition">☀️ Light</a>
                                <a href="{{ route('set-theme', ['theme' => 'dark']) }}" class="p-2 border border-[var(--theme-border)] rounded-lg text-center font-bold hover:bg-theme-primary/10 transition">🌙 Dark</a>
                                <a href="{{ route('set-theme', ['theme' => 'sepia']) }}" class="p-2 border border-[var(--theme-border)] rounded-lg text-center font-bold hover:bg-theme-primary/10 transition">☕ Sepia</a>
                                <a href="{{ route('set-theme', ['theme' => 'academic_blue']) }}" class="p-2 border border-[var(--theme-border)] rounded-lg text-center font-bold hover:bg-theme-primary/10 transition">🎓 Academic</a>
                            </div>
                        </div>
                    @else
                        <p class="text-theme-text/70 mb-4">
                            You are browsing as a Guest. Log in to apply to vacancies and see matching credentials.
                        </p>
                        <a href="{{ route('login-demo') }}" class="block w-full py-3 text-center bg-theme-primary hover:bg-theme-accent text-white font-bold rounded-xl shadow transition">
                            Open Quick Login
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Faculty Features Card -->
            <div class="p-6 rounded-2xl bg-gradient-to-br from-theme-primary/5 to-theme-accent/5 border border-[var(--theme-border)]">
                <h4 class="font-bold text-theme-text mb-2">💼 Why top educators trust us?</h4>
                <ul class="space-y-2 text-xs text-theme-text/75 mt-3">
                    <li class="flex gap-2">🔥 <span>IIT-JEE/NEET Micro-subject targeting</span></li>
                    <li class="flex gap-2">📹 <span>Direct Teaching Demo Ratings panel</span></li>
                    <li class="flex gap-2">📜 <span>Automated & secure peer reference system</span></li>
                    <li class="flex gap-2">🔬 <span>Google Scholar and h-index auto-fetch</span></li>
                </ul>
            </div>

        </div>

    </div>

</div>
@endsection
