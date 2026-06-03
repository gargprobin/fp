@extends('layouts.app')

@section('title', ($siteSettings->platform_name ?? 'EduStaff Hub') . ' - ' . ($siteSettings->platform_tagline ?? 'Premium Faculty Recruitment Ecosystem'))

@section('content')
@php
    $appliedVacancyIds = [];
    if (auth()->check() && auth()->user()->isCandidate()) {
        $appliedVacancyIds = auth()->user()->applications->pluck('vacancy_id')->toArray();
    }
@endphp
<div x-data="{ 
    // State management for local filter matching c:\jobs
    searchSubject: '{{ request('subject', '') }}',
    searchLocation: '{{ request('location', '') }}',
    selectedCategory: '{{ request('category', 'all') }}',
    activeVideo: null,
    applyVacancy: null,
    
    // Check if job matches filters
    matchesFilters(jobSubject, jobTitle, jobDesc, jobLoc) {
        let matchSubject = true;
        let matchLocation = true;
        let matchCategory = true;
        
        if (this.searchSubject) {
            const query = this.searchSubject.toLowerCase();
            matchSubject = jobSubject.toLowerCase().includes(query) || 
                           jobTitle.toLowerCase().includes(query) || 
                           jobDesc.toLowerCase().includes(query);
        }
        
        if (this.searchLocation) {
            const query = this.searchLocation.toLowerCase();
            matchLocation = jobLoc.toLowerCase().includes(query);
        }
        
        if (this.selectedCategory !== 'all') {
            const cat = this.selectedCategory.toLowerCase();
            matchCategory = jobSubject.toLowerCase().includes(cat) || 
                            jobTitle.toLowerCase().includes(cat) || 
                            jobDesc.toLowerCase().includes(cat);
        }
        
        return matchSubject && matchLocation && matchCategory;
    },
    
    // Clear all filters
    clearFilters() {
        this.searchSubject = '';
        this.searchLocation = '';
        this.selectedCategory = 'all';
    }
}" class="relative min-h-screen flex flex-col font-sans selection:bg-theme-primary/20 selection:text-theme-primary -mt-8">

    <!-- ----------------- HERO SECTION (Same to Same c:\jobs) ----------------- -->
    <section class="relative overflow-hidden py-16 md:py-24 flex flex-col items-center text-center">
        <!-- Decorative Gradients -->
        <div class="absolute top-0 left-1/4 -translate-y-1/2 w-72 h-72 bg-theme-primary/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 translate-y-1/2 w-80 h-80 bg-theme-accent/5 rounded-full blur-3xl"></div>

        <div class="relative z-10 max-w-4xl mx-auto flex flex-col items-center px-4">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-theme-primary/10 text-theme-primary text-xs font-bold uppercase tracking-wider mb-6">
                ✨ #1 Trusted Faculty Recruitment Partner
            </div>

            <h1 class="text-4xl md:text-6xl font-black text-theme-text tracking-tight leading-tight md:leading-none mb-6">
                {{ $siteSettings->platform_name ?? 'EduStaff Hub' }} 
                <span class="text-theme-primary block md:inline">Coaching Institutes</span> 
                {{ $siteSettings->platform_tagline ?? 'Premium Faculty Recruitment Partner' }}
            </h1>

            <p class="text-lg md:text-xl text-theme-text/75 font-medium max-w-2xl mb-10 leading-relaxed">
                Access 100% verified educators for IIT-JEE, NEET, Foundation & School levels. Complete with expert-screened video demo lectures and 48-hour hiring support.
            </p>

            <!-- Fully Interactive Search Bar (Submits to route for SSR + matches locally via Alpine) -->
            <form action="./" method="GET" class="w-full max-w-2xl bg-[var(--theme-card)] p-3 rounded-2xl md:rounded-full shadow-xl border border-[var(--theme-border)] flex flex-col md:flex-row gap-2 items-center mb-12">
                <div class="w-full flex items-center px-4 gap-2">
                    <svg class="w-5 h-5 text-theme-text/40 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input 
                        type="text" 
                        name="subject"
                        x-model="searchSubject"
                        placeholder="Search Subject (e.g. Physics, Chemistry, CS)" 
                        class="w-full py-2 bg-transparent text-theme-text focus:outline-none text-sm font-semibold"
                    />
                </div>
                <div class="w-full flex items-center px-4 gap-2 border-t md:border-t-0 md:border-l border-[var(--theme-border)]">
                    <svg class="w-5 h-5 text-theme-text/40 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <input 
                        type="text" 
                        name="location"
                        x-model="searchLocation"
                        placeholder="Location (e.g. Kota, Bangalore)" 
                        class="w-full py-2 bg-transparent text-theme-text focus:outline-none text-sm font-semibold"
                    />
                </div>
                <div class="w-full flex items-center px-4 gap-2 border-t md:border-t-0 md:border-l border-[var(--theme-border)]">
                    <svg class="w-5 h-5 text-theme-text/40 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                    <select 
                        name="sort" 
                        class="w-full py-2 bg-transparent text-theme-text focus:outline-none text-sm font-semibold cursor-pointer"
                        onchange="this.form.submit()"
                    >
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Latest Postings</option>
                        <option value="salary_high" {{ request('sort') === 'salary_high' ? 'selected' : '' }}>Highest Budget</option>
                        <option value="deadline_soon" {{ request('sort') === 'deadline_soon' ? 'selected' : '' }}>Deadline: Soonest</option>
                    </select>
                </div>
                <button 
                    type="submit"
                    class="w-full md:w-auto px-8 py-3 text-sm font-bold text-white bg-theme-primary hover:bg-theme-accent rounded-xl md:rounded-full transition-all shadow-md shadow-theme-primary/10 cursor-pointer shrink-0"
                >
                    Search
                </button>
            </form>
        </div>
    </section>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto w-full px-4 mb-6">
            <div class="p-4 rounded-xl bg-green-500/10 text-green-500 border border-green-500/20 font-semibold shadow-sm text-center">
                🎉 {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto w-full px-4 mb-6">
            <div class="p-4 rounded-xl bg-red-500/10 text-red-500 border border-red-500/20 font-semibold shadow-sm text-center">
                ⚠️ {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- ----------------- INTERACTIVE JOBS BOARD SECTION ----------------- -->
    <section id="jobs" class="py-16 px-4 bg-theme-primary/5 border-y border-[var(--theme-border)] transition-colors">
        <div class="max-w-7xl mx-auto w-full">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                    <h2 class="text-3xl font-black text-theme-text tracking-tight">Active Faculty Openings</h2>
                    <p class="text-xs text-theme-text/50 font-bold uppercase tracking-wider mt-1">Real-time Sandbox Database &bull; Showing {{ $vacancies->firstItem() ?? 0 }}-{{ $vacancies->lastItem() ?? 0 }} of {{ $vacancies->total() }} openings</p>
                </div>
                
                <!-- Reset Filters Button -->
                <button 
                    x-show="searchSubject || searchLocation || selectedCategory !== 'all' || '{{ request('subject') }}' || '{{ request('location') }}' || '{{ request('category') }}'"
                    @click="clearFilters(); window.location.href = '{{ route('home') }}'"
                    class="px-4 py-2 text-xs font-bold text-red-500 bg-red-50 hover:bg-red-100 border border-red-200 rounded-full transition-all cursor-pointer"
                >
                    Clear Filters
                </button>
            </div>

            <!-- Job Cards Container -->
            <div class="grid grid-cols-1 gap-6">
                @php $hasVisibleJobs = false; @endphp
                @foreach($vacancies as $job)
                    @php $hasVisibleJobs = true; @endphp
                    <div 
                        x-show="matchesFilters('{{ addslashes(str_replace(["'", '"'], ' ', $job->subject)) }}', '{{ addslashes(str_replace(["'", '"'], ' ', $job->title)) }}', '{{ addslashes(str_replace(["\r", "\n", "'", '"'], ' ', $job->description)) }}', '{{ addslashes(str_replace(["'", '"'], ' ', $job->location)) }}')"
                        class="p-6 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] flex flex-col md:flex-row justify-between items-start md:items-center gap-6 hover:border-theme-primary/30 hover:shadow-lg transition-all duration-300 relative overflow-hidden"
                    >
                        <!-- Institutional Colored Border Strip -->
                        <div class="absolute left-0 top-0 bottom-0 w-1.5" style="background-color: {{ $job->institution->primary_color ?? '#059669' }}"></div>

                        <div class="space-y-3 pl-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="font-extrabold text-theme-text text-lg">{{ $job->title }}</h4>
                                <span class="text-[10px] bg-theme-primary/10 text-theme-primary font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                    {{ $job->target_class }}
                                </span>
                            </div>
                            
                            <p class="text-xs font-bold text-theme-text/60 flex flex-wrap gap-4">
                                <span>📍 {{ $job->location }}</span>
                                <span>💼 Exp: {{ $job->experience_required }}</span>
                                @if($job->salary_max)
                                    <span>💰 Budget: ₹{{ number_format($job->salary_min/100000, 1) }}L - ₹{{ number_format($job->salary_max/100000, 1) }}L / yr</span>
                                @endif
                            </p>
                            <p class="text-sm text-theme-text/75 max-w-3xl leading-relaxed">{{ $job->description }}</p>
                            @php
                                $showRealName = false;
                                if (auth()->check()) {
                                    if (auth()->user()->isAdmin() || (auth()->user()->isInstitution() && auth()->user()->id == $job->institution->user_id)) {
                                        $showRealName = true;
                                    } elseif (auth()->user()->isCandidate()) {
                                        $userApp = \App\Models\Application::where('vacancy_id', $job->id)
                                            ->where('candidate_id', auth()->id())
                                            ->first();
                                        if ($userApp && in_array($userApp->status, ['unlocked', 'written_test', 'interview', 'demo', 'appointed'])) {
                                            $showRealName = true;
                                        }
                                    }
                                }
                            @endphp
                            <span class="text-[10px] text-theme-text/40 block">Posted by: <strong>{{ $showRealName ? $job->institution->company_name : 'Confidential Partner Brand' }}</strong></span>
                        </div>

                        <!-- 1-Click Apply Trigger or Applied Badge -->
                        @if(in_array($job->id, $appliedVacancyIds))
                            <span class="px-6 py-2.5 text-xs font-black text-green-500 bg-green-500/10 border border-green-500/20 rounded-xl shrink-0 text-center w-full md:w-auto">
                                ✓ Applied
                            </span>
                        @else
                            <button 
                                @click="applyVacancy = { id: {{ $job->id }}, title: {{ json_encode($job->title) }}, institution: 'Confidential Partner Brand' }"
                                class="px-6 py-2.5 text-xs font-bold text-white bg-theme-primary hover:bg-theme-accent rounded-xl shadow-md shadow-theme-primary/10 hover:shadow-xl shrink-0 text-center w-full md:w-auto cursor-pointer"
                            >
                                Apply with 1-Click
                            </button>
                        @endif
                    </div>
                @endforeach

                @if(!$hasVisibleJobs)
                    <div class="text-center py-16 bg-[var(--theme-card)] rounded-3xl border border-[var(--theme-border)]">
                        <span class="text-4xl block mb-2">🔍</span>
                        <h4 class="font-bold text-theme-text text-lg">No Openings Found</h4>
                        <p class="text-sm text-theme-text/60 mt-1">Try clearing filters or search another subject.</p>
                    </div>
                @endif
            </div>

            <!-- Pagination Links -->
            <div class="mt-8 flex justify-center">
                {{ $vacancies->links() }}
            </div>
        </div>
    </section>

    <!-- Dynamic Success Metrics Section -->
    <section class="py-12 border-b border-[var(--theme-border)] bg-[var(--theme-card)] text-center">
        <div class="max-w-3xl mx-auto px-4">
            <div class="grid grid-cols-3 gap-6 md:gap-16">
                <div class="flex flex-col items-center">
                    <span class="text-3xl md:text-4xl font-extrabold text-theme-text">10,000+</span>
                    <span class="text-xs md:text-sm font-bold text-theme-text/60 mt-1">Verified Profiles</span>
                </div>
                <div class="flex flex-col items-center border-x border-[var(--theme-border)] px-6">
                    <span class="text-3xl md:text-4xl font-extrabold text-theme-text">500+</span>
                    <span class="text-xs md:text-sm font-bold text-theme-text/60 mt-1">Partner Institutes</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-3xl md:text-4xl font-extrabold text-theme-text">48-Hour</span>
                    <span class="text-xs md:text-sm font-bold text-theme-text/60 mt-1">Hiring Turnaround</span>
                </div>
            </div>
        </div>
    </section>


    <!-- ----------------- INTERACTIVE CATEGORIES SECTION (Same to Same c:\jobs) ----------------- -->
    <section id="categories" class="py-16 px-4 max-w-7xl mx-auto w-full">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-black text-theme-text tracking-tight mb-4">
                Specialized Faculty Categories
            </h2>
            <p class="text-theme-text/70 font-medium max-w-xl mx-auto">
                Click any category below to instantly filter open jobs in the Jobs Board!
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <a 
                href="{{ route('home', ['category' => 'Physics']) }}"
                :class="selectedCategory === 'Physics' ? 'border-theme-primary bg-theme-primary/5 ring-2 ring-theme-primary/20' : 'bg-[var(--theme-card)] border-[var(--theme-border)] hover:border-theme-primary/20'"
                class="group p-8 rounded-2xl border text-left shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer block"
            >
                <span class="text-4xl block mb-6 filter drop-shadow">📐</span>
                <h3 class="text-xl font-bold text-theme-text mb-2 group-hover:text-theme-primary transition-colors">IIT-JEE Physics</h3>
                <span class="inline-block px-3 py-1 rounded-full bg-theme-primary/10 text-theme-primary text-xs font-bold mb-4">4,500+ Teachers</span>
                <p class="text-sm text-theme-text/60 leading-relaxed">Classical mechanics, Electromagnetism, & thermodynamics Advanced level faculty.</p>
            </a>

            <a 
                href="{{ route('home', ['category' => 'Chemistry']) }}"
                :class="selectedCategory === 'Chemistry' ? 'border-theme-primary bg-theme-primary/5 ring-2 ring-theme-primary/20' : 'bg-[var(--theme-card)] border-[var(--theme-border)] hover:border-theme-primary/20'"
                class="group p-8 rounded-2xl border text-left shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer block"
            >
                <span class="text-4xl block mb-6 filter drop-shadow">🧬</span>
                <h3 class="text-xl font-bold text-theme-text mb-2 group-hover:text-theme-primary transition-colors">NEET Chemistry</h3>
                <span class="inline-block px-3 py-1 rounded-full bg-theme-primary/10 text-theme-primary text-xs font-bold mb-4">3,800+ Teachers</span>
                <p class="text-sm text-theme-text/60 leading-relaxed">Organic, Inorganic, Physical Chemistry experts for medical exams.</p>
            </a>

            <a 
                href="{{ route('home', ['category' => 'Computer Science']) }}"
                :class="selectedCategory === 'Computer Science' ? 'border-theme-primary bg-theme-primary/5 ring-2 ring-theme-primary/20' : 'bg-[var(--theme-card)] border-[var(--theme-border)] hover:border-theme-primary/20'"
                class="group p-8 rounded-2xl border text-left shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer block"
            >
                <span class="text-4xl block mb-6 filter drop-shadow">🏫</span>
                <h3 class="text-xl font-bold text-theme-text mb-2 group-hover:text-theme-primary transition-colors">Computer Science</h3>
                <span class="inline-block px-3 py-1 rounded-full bg-theme-primary/10 text-theme-primary text-xs font-bold mb-4">2,200+ Teachers</span>
                <p class="text-sm text-theme-text/60 leading-relaxed">Distributed systems, Artificial Intelligence, algorithms, & coding instructors.</p>
            </a>

            <a 
                href="{{ route('home') }}"
                :class="selectedCategory === 'all' ? 'border-theme-primary bg-theme-primary/5 ring-2 ring-theme-primary/20' : 'bg-[var(--theme-card)] border-[var(--theme-border)] hover:border-theme-primary/20'"
                class="group p-8 rounded-2xl border text-left shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer block"
            >
                <span class="text-4xl block mb-6 filter drop-shadow">🎓</span>
                <h3 class="text-xl font-bold text-theme-text mb-2 group-hover:text-theme-primary transition-colors">All Categories</h3>
                <span class="inline-block px-3 py-1 rounded-full bg-theme-primary/10 text-theme-primary text-xs font-bold mb-4">10,000+ Total</span>
                <p class="text-sm text-theme-text/60 leading-relaxed">Browse complete list of active faculty requirements across multiple disciplines.</p>
            </a>
        </div>
    </section>

    <!-- Jobs section moved above -->

    <!-- ----------------- WHY CHOOSE US (Same to Same c:\jobs) ----------------- -->
    <section id="why-us" class="bg-slate-950 text-white py-20 px-4">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
                <span class="text-theme-primary text-sm font-bold uppercase tracking-wider block mb-3">
                    Rigorous Screening Process
                </span>
                <h2 class="text-3xl md:text-5xl font-black tracking-tight leading-tight mb-6">
                    We Don't Just Send Resumes. We Deliver Verified Talent.
                </h2>
                <p class="text-gray-300 mb-8 leading-relaxed">
                    Tired of sorting through hundreds of unverified CVs? {{ $siteSettings->platform_name ?? 'EduStaff Hub' }} evaluates candidate subject matter depth, communication flow, and demo lectures before presenting them to you.
                </p>
                
                <div class="space-y-4">
                    <div class="flex gap-4 items-start">
                        <div class="p-1 rounded-full bg-theme-primary text-white text-xs shrink-0 mt-1">✓</div>
                        <div>
                            <h4 class="font-bold text-lg text-white">Demo Lecture Screening</h4>
                            <p class="text-sm text-gray-400">Recorded video demo classes are compiled and linked directly to every candidate profile.</p>
                        </div>
                    </div>

                    <div class="flex gap-4 items-start">
                        <div class="p-1 rounded-full bg-theme-primary text-white text-xs shrink-0 mt-1">✓</div>
                        <div>
                            <h4 class="font-bold text-lg text-white">Hassle-Free Replacement</h4>
                            <p class="text-sm text-gray-400">Enjoy a comprehensive 30-day candidate replacement guarantee for peace of mind.</p>
                        </div>
                    </div>

                    <div class="flex gap-4 items-start">
                        <div class="p-1 rounded-full bg-theme-primary text-white text-xs shrink-0 mt-1">✓</div>
                        <div>
                            <h4 class="font-bold text-lg text-white">Pan-India Recruiting Network</h4>
                            <p class="text-sm text-gray-400">Source premier coaching faculty from Kota, Delhi, Patna, Sikar, and Indore.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sample Video Gallery Mockup with Play Actions (Same to Same c:\jobs) -->
            <div class="bg-slate-900 p-6 rounded-3xl border border-slate-800 shadow-2xl relative">
                <div class="absolute -top-4 -left-4 bg-theme-primary text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-lg">
                    Faculty Pool Mockup
                </div>
                
                <h3 class="text-lg font-bold mb-4 text-slate-300">Video Demo Library (Preview)</h3>
                <div class="space-y-4">
                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 flex justify-between items-center gap-4 hover:border-theme-primary/30 transition-all duration-300">
                        <div>
                            <h5 class="font-bold text-sm text-white">Dr. Vikram Aditya (Ex-Allen HOD)</h5>
                            <p class="text-xs text-slate-400 mt-1">Physics | Exp: 8 Yrs | IIT Graduate</p>
                            <span class="inline-flex items-center gap-1.5 text-theme-primary text-[10px] font-bold mt-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-theme-primary animate-ping"></span>
                                10 min Class Demo Ready
                            </span>
                        </div>
                        <button 
                            @click="activeVideo = { name: 'Dr. Vikram Aditya', sub: 'Physics | Exp: 8 Yrs | IIT Graduate' }"
                            class="px-3 py-1.5 text-xs font-bold text-white bg-theme-primary hover:bg-theme-accent rounded-lg transition-colors shrink-0 cursor-pointer"
                        >
                            Watch Demo
                        </button>
                    </div>

                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 flex justify-between items-center gap-4 hover:border-theme-primary/30 transition-all duration-300">
                        <div>
                            <h5 class="font-bold text-sm text-white">Prof. Ananya Sen (NEET Biology Expert)</h5>
                            <p class="text-xs text-slate-400 mt-1">Biology | Exp: 6 Yrs | Ph.D. Scholar</p>
                            <span class="inline-flex items-center gap-1.5 text-theme-primary text-[10px] font-bold mt-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-theme-primary animate-ping"></span>
                                12 min Lecture Demo Ready
                            </span>
                        </div>
                        <button 
                            @click="activeVideo = { name: 'Prof. Ananya Sen', sub: 'Biology | Exp: 6 Yrs | Ph.D. Scholar' }"
                            class="px-3 py-1.5 text-xs font-bold text-white bg-theme-primary hover:bg-theme-accent rounded-lg transition-colors shrink-0 cursor-pointer"
                        >
                            Watch Demo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ----------------- DYNAMIC VIDEO POPUP MODAL (Same to Same c:\jobs) ----------------- -->
    <div x-show="activeVideo" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" x-cloak>
        <div @click="activeVideo = null" class="absolute inset-0 bg-black/60 backdrop-blur-xs"></div>
        
        <div class="relative w-full max-w-2xl bg-slate-900 rounded-3xl overflow-hidden shadow-2xl p-4 sm:p-6 z-10 text-center border border-slate-800">
            <div class="flex justify-between items-center border-b border-slate-800 pb-4 mb-4">
                <div class="text-left">
                    <h4 class="font-bold text-white text-base" x-text="activeVideo ? activeVideo.name : ''"></h4>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="activeVideo ? activeVideo.sub : ''"></p>
                </div>
                <button 
                    @click="activeVideo = null"
                    class="text-slate-400 hover:text-white text-lg font-bold p-1 cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Simulated Dynamic Video Player (Stunning visual design) -->
            <div class="aspect-video bg-black rounded-xl border border-slate-800 flex flex-col justify-center items-center relative overflow-hidden">
                <span class="w-16 h-16 bg-theme-primary text-white rounded-full flex items-center justify-center shadow-lg shadow-theme-primary/30 text-xl font-bold cursor-pointer hover:scale-105 active:scale-95 transition-all">
                    ▶
                </span>
                <p class="text-xs text-slate-400 font-bold mt-4 z-10 uppercase tracking-widest">
                    Playing Live Demo Lecture Classroom Board Work
                </p>
                
                <!-- Nice blackboard layout design in background -->
                <div class="absolute inset-0 bg-cover bg-center opacity-25 flex items-center justify-center">
                    <div class="text-emerald-500 font-mono text-[9px] text-left p-4 opacity-40 leading-none">
                        F = m * a <br>
                        v = u + a*t <br>
                        E = m * c^2 <br>
                        d/dx [sin(x)] = cos(x)
                    </div>
                </div>
            </div>

            <div class="flex gap-4 mt-6">
                <a 
                    href="{{ route('login-demo') }}" 
                    class="flex-1 py-3 text-xs font-bold text-white bg-theme-primary hover:bg-theme-accent rounded-xl shadow-md transition-all text-center cursor-pointer"
                >
                    Request Candidate Shortlist
                </a>
                <button 
                    @click="activeVideo = null"
                    class="px-6 py-3 text-xs font-bold text-slate-400 bg-slate-800 hover:bg-slate-700 rounded-xl transition-all cursor-pointer"
                >
                    Close Demo
                </button>
            </div>
        </div>
    </div>

    <!-- ----------------- 1-CLICK QUICK APPLY MODAL (Blade Integrated) ----------------- -->
    <div x-show="applyVacancy" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" x-cloak>
        <div @click="applyVacancy = null" class="absolute inset-0 bg-black/60 backdrop-blur-xs"></div>
        
        <div class="relative w-full max-w-lg bg-[var(--theme-card)] text-theme-text rounded-3xl overflow-hidden shadow-2xl p-4 sm:p-6 z-10 border border-[var(--theme-border)]">
            <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-4 mb-4">
                <div>
                    <h4 class="font-extrabold text-base">Quick Apply</h4>
                    <p class="text-xs text-theme-text/60 mt-0.5" x-text="applyVacancy ? applyVacancy.title + ' @ ' + applyVacancy.institution : ''"></p>
                </div>
                <button 
                    @click="applyVacancy = null"
                    class="text-theme-text/50 hover:text-theme-text text-lg font-bold p-1 cursor-pointer"
                >
                    ✕
                </button>
            </div>

            @auth
                <!-- Authenticated Quick Application Form -->
                <form :action="'{{ url('/vacancy') }}/' + (applyVacancy ? applyVacancy.id : '') + '/apply'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-theme-text/70">Cover Note</label>
                        <textarea name="cover_letter" rows="3" required class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="Explain why you are qualified for this faculty role..."></textarea>
                    </div>

                    <!-- Peer Reference Collector Setup (Highly dynamic) -->
                    <div class="p-4 rounded-2xl bg-theme-primary/5 border border-theme-primary/10 space-y-3">
                        <span class="block text-xs font-black text-theme-primary uppercase tracking-widest">📜 Confidential Referee Request (Automated)</span>
                        <p class="text-[11px] text-theme-text/65 leading-relaxed">
                            To ensure high-quality placement credibility, {{ $siteSettings->platform_name ?? 'Sourcing Portal' }} sends a secure signed URL instantly to your referee to collect academic recommendation letters.
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                            <input type="text" name="referee_name" placeholder="Referee Name (e.g. Dean, HOD)" class="w-full p-2.5 text-xs bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-lg text-theme-text" />
                            <input type="email" name="referee_email" placeholder="Referee Email ID" class="w-full p-2.5 text-xs bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-lg text-theme-text" />
                        </div>
                    </div>

                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="applyVacancy = null" class="px-5 py-2.5 text-xs font-bold bg-gray-100 hover:bg-gray-250 text-gray-700 rounded-xl transition">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-theme-primary hover:bg-theme-accent rounded-xl shadow transition">Submit Application</button>
                    </div>
                </form>
            @else
                <!-- Guest View: Force Login -->
                <div class="text-center py-6 space-y-4">
                    <span class="text-4xl block">🔑</span>
                    <h5 class="font-bold text-lg text-theme-text">Sign In Required</h5>
                    <p class="text-sm text-theme-text/65 leading-relaxed max-w-sm mx-auto">
                        You need to log in as a Faculty Candidate to submit applications and track recommendation letters.
                    </p>
                    <div class="flex gap-3 justify-center pt-2">
                        <button type="button" @click="applyVacancy = null" class="px-5 py-2.5 text-xs font-bold bg-gray-100 hover:bg-gray-250 text-gray-700 rounded-xl transition">Close</button>
                        <a href="{{ route('login-demo') }}" class="px-6 py-2.5 text-xs font-bold text-white bg-theme-primary hover:bg-theme-accent rounded-xl shadow transition">Sign In via Demo Accounts</a>
                    </div>
                </div>
            @endauth
        </div>
    </div>

    <!-- ----------------- HOW IT WORKS (Same to Same c:\jobs) ----------------- -->
    <section id="how-it-works" class="py-20 px-4 max-w-7xl mx-auto w-full text-center">
        <div class="mb-16">
            <h2 class="text-3xl md:text-4xl font-black text-theme-text tracking-tight mb-4">
                How It Works
            </h2>
            <p class="text-theme-text/60 font-medium max-w-md mx-auto">
                A fast, seamless, and automated hiring pipeline.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-5xl mx-auto">
            <!-- For Coaching Owners -->
            <div class="bg-[var(--theme-card)] p-8 rounded-3xl border border-[var(--theme-border)] text-left hover:shadow-lg transition-all duration-300">
                <h3 class="text-2xl font-extrabold text-theme-text mb-6 flex items-center gap-3">
                    <span class="text-2xl">🏢</span> For Coaching & Schools
                </h3>
                <div class="space-y-6">
                    <div class="flex gap-4">
                        <span class="w-7 h-7 flex items-center justify-center rounded-full bg-theme-primary/20 text-theme-primary font-bold text-xs shrink-0 mt-0.5">1</span>
                        <div>
                            <h5 class="font-bold text-theme-text text-base">Post Your Requirement</h5>
                            <p class="text-sm text-theme-text/60 mt-1">Submit details about your subject, class level, location, and salary budget via our recruiter panel.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="w-7 h-7 flex items-center justify-center rounded-full bg-theme-primary/20 text-theme-primary font-bold text-xs shrink-0 mt-0.5">2</span>
                        <div>
                            <h5 class="font-bold text-theme-text text-base">Review Pre-Screened Demos</h5>
                            <p class="text-sm text-theme-text/60 mt-1">Receive the top 3 handpicked verified candidate profiles with video lecture demos and references.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="w-7 h-7 flex items-center justify-center rounded-full bg-theme-primary/20 text-theme-primary font-bold text-xs shrink-0 mt-0.5">3</span>
                        <div>
                            <h5 class="font-bold text-theme-text text-base">Interview & Onboard</h5>
                            <p class="text-sm text-theme-text/60 mt-1">Schedule interviews directly through the dashboard and onboard your dream faculty instantly.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- For Teachers -->
            <div class="bg-[var(--theme-card)] p-8 rounded-3xl border border-[var(--theme-border)] text-left hover:shadow-lg transition-all duration-300">
                <h3 class="text-2xl font-extrabold text-theme-text mb-6 flex items-center gap-3">
                    <span class="text-2xl">🧑‍🏫</span> For Teachers & Faculty
                </h3>
                <div class="space-y-6">
                    <div class="flex gap-4">
                        <span class="w-7 h-7 flex items-center justify-center rounded-full bg-theme-accent/25 text-theme-accent font-bold text-xs shrink-0 mt-0.5">1</span>
                        <div>
                            <h5 class="font-bold text-theme-text text-base">Upload Your Resume</h5>
                            <p class="text-sm text-theme-text/60 mt-1">Submit your basic credentials and career achievements in under 30 seconds to join our pool.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="w-7 h-7 flex items-center justify-center rounded-full bg-theme-accent/25 text-theme-accent font-bold text-xs shrink-0 mt-0.5">2</span>
                        <div>
                            <h5 class="font-bold text-theme-text text-base">Share a Demo Class Link</h5>
                            <p class="text-sm text-theme-text/60 mt-1">Provide a YouTube class link or upload a demo video representing your teaching style.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="w-7 h-7 flex items-center justify-center rounded-full bg-theme-accent/25 text-theme-accent font-bold text-xs shrink-0 mt-0.5">3</span>
                        <div>
                            <h5 class="font-bold text-theme-text text-base">Secure Your Dream Role</h5>
                            <p class="text-sm text-theme-text/60 mt-1">Interview directly with top premium schools and coaching institutes across India.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ----------------- DYNAMIC WHATSAPP CTA (Same to Same c:\jobs) ----------------- -->
    <a 
        href="https://wa.me/919876543210?text=Hi, I am interested in hiring faculty / looking for jobs."
        target="_blank" 
        rel="noopener noreferrer"
        class="fixed bottom-6 right-6 z-30 flex items-center gap-2 bg-[#25D366] hover:bg-[#20ba5a] text-white px-5 py-3 rounded-full shadow-2xl hover:shadow-[#25D366]/30 hover:-translate-y-1 transition-all duration-300 font-bold"
    >
        <span class="text-xl">💬</span>
        <span class="text-sm">Chat on WhatsApp</span>
    </a>

</div>
@endsection
