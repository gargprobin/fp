@extends('layouts.app')

@section('title', 'Faculty Candidate Console - ' . auth()->user()->name)

@section('content')
<div class="space-y-8">
    
    <!-- Candidate Header Banner -->
    <div class="p-8 rounded-2xl bg-gradient-to-br from-theme-primary/10 via-theme-accent/5 to-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm relative overflow-hidden transition-all duration-300">
        <div class="absolute left-0 top-0 bottom-0 w-2 bg-theme-primary"></div>
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 pl-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight mb-2">
                    🧑‍🏫 Faculty Candidate Console
                </h1>
                <p class="text-theme-text/75 text-base">
                    Welcome back, <strong class="text-theme-primary">{{ auth()->user()->name }}</strong>. Track your job placements and pipeline status in real-time.
                </p>
                <p class="text-xs text-theme-text/60 mt-2">
                    🎓 Highest Qualification: {{ auth()->user()->candidateProfile?->highest_degree ?? 'N/A' }} &bull; 🔬 H-Index: {{ auth()->user()->candidateProfile?->h_index ?? 0 }} Citations
                </p>
            </div>
            
            <div class="flex gap-2">
                <span class="px-4 py-2 bg-theme-primary/10 text-theme-primary border border-theme-primary/20 text-xs font-black rounded-full uppercase tracking-wider">
                    Verified Candidate: {{ ucfirst(auth()->user()->candidateProfile?->type ?? 'Academic') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-green-500/10 text-green-500 border border-green-500/20 font-semibold shadow-sm transition">
            🎉 {{ session('success') }}
        </div>
    @endif

    <!-- Candidate Applications and Vetting Pipeline Section -->
    <div class="space-y-6">
        <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3">
            <h3 class="text-xl font-bold">My Active Applications ({{ $applications->count() }})</h3>
            <span class="px-2.5 py-1 bg-blue-500/10 text-blue-500 text-xs font-bold rounded-full">
                Symmetrical Privacy Enabled
            </span>
        </div>

        @forelse($applications as $app)
            @php
                // Check if the application is unmasked/unlocked for mutual details disclosure
                // unlocked and all pipeline stages after it allow details unmasking
                $isLinedUp = in_array($app->status, ['unlocked', 'written_test', 'interview', 'demo', 'appointed']);
            @endphp
            
            <div id="app-{{ $app->id }}" class="bg-[var(--theme-card)] rounded-2xl p-6 border border-[var(--theme-border)] shadow-sm space-y-6 relative overflow-hidden transition-all hover:shadow-md">
                
                <!-- Status Colored Accent -->
                <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $app->status === 'appointed' ? 'bg-emerald-500' : ($isLinedUp ? 'bg-indigo-500' : 'bg-amber-500') }}"></div>
                
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 pl-3">
                    <!-- Vacancy Title & Subject Info -->
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-theme-text/50">Vacancy Applied</span>
                        <h4 class="text-xl font-extrabold text-theme-text">{{ $app->vacancy->title }}</h4>
                        <p class="text-xs text-theme-text/60 mt-1">
                            📍 {{ $app->vacancy->location }} &bull; Subject: <strong class="text-theme-text/80">{{ $app->vacancy->subject }}</strong> &bull; Applied: {{ $app->created_at->format('M d, Y') }}
                        </p>
                    </div>

                    <!-- Visual Stage Badge -->
                    <div>
                        @if($app->status === 'applied')
                            <span class="px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                📥 Applied & Under Screening
                            </span>
                        @elseif($app->status === 'shortlisted_by_admin')
                            <span class="px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-blue-500/10 text-blue-500 border border-blue-500/20">
                                🔎 Shortlisted by Admin
                            </span>
                        @elseif($app->status === 'shortlisted_by_institution')
                            <span class="px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-purple-500/10 text-purple-500 border border-purple-500/20 animate-pulse">
                                🤝 Lined Up (CV Forwarded & Masked)
                            </span>
                        @elseif($app->status === 'unlocked')
                            <span class="px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-indigo-600 text-white shadow-sm">
                                🟢 Line Up Approved! (Details Exchanged)
                            </span>
                        @elseif($app->status === 'written_test')
                            <span class="px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-indigo-500/10 text-indigo-500 border border-indigo-500/20">
                                📝 Written Test Scheduled
                            </span>
                        @elseif($app->status === 'interview')
                            <span class="px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-indigo-500/10 text-indigo-500 border border-indigo-500/20">
                                🗣️ Interview Round
                            </span>
                        @elseif($app->status === 'demo')
                            <span class="px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-indigo-500/10 text-indigo-500 border border-indigo-500/20">
                                📹 Classroom Demo Round
                            </span>
                        @elseif($app->status === 'appointed')
                            <span class="px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-emerald-500 text-white border border-emerald-600">
                                🎉 Congratulations! Appointed
                            </span>
                        @else
                            <span class="px-3.5 py-1.5 text-xs font-extrabold rounded-full bg-red-500/10 text-red-500 border border-red-500/20">
                                {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- 🚀 Pipeline Interactive Timeline Progression Tracker (Desktop) -->
                <div class="hidden md:block pl-3 py-4 border-t border-b border-[var(--theme-border)]">
                    <div class="relative flex items-center justify-between w-full max-w-3xl mx-auto text-center">
                        <!-- Progress Background Bar -->
                        <div class="absolute left-0 right-0 top-1/2 h-1 bg-[var(--theme-border)] -translate-y-1/2 z-0"></div>
                        
                        <!-- Dynamic Progress Bar width indicator -->
                        @php
                            $stages = ['applied', 'shortlisted_by_admin', 'shortlisted_by_institution', 'unlocked', 'vetting', 'appointed'];
                            $currentStageIdx = 0;
                            if ($app->status === 'applied') $currentStageIdx = 0;
                            elseif ($app->status === 'shortlisted_by_admin') $currentStageIdx = 1;
                            elseif ($app->status === 'shortlisted_by_institution') $currentStageIdx = 2;
                            elseif ($app->status === 'unlocked') $currentStageIdx = 3;
                            elseif (in_array($app->status, ['written_test', 'interview', 'demo'])) $currentStageIdx = 4;
                            elseif ($app->status === 'appointed') $currentStageIdx = 5;
                            $pct = ($currentStageIdx / (count($stages) - 1)) * 100;
                        @endphp
                        <div class="absolute left-0 top-1/2 h-1 bg-theme-primary -translate-y-1/2 z-0 transition-all duration-500" style="width: {{ $pct }}%;"></div>

                        <!-- Stage A: Applied -->
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition-all duration-300 {{ $currentStageIdx >= 0 ? 'bg-theme-primary text-white scale-110' : 'bg-[var(--theme-card)] text-theme-text/40 border border-[var(--theme-border)]' }}">
                                📥
                            </div>
                            <span class="text-[10px] font-bold mt-2 text-theme-text uppercase tracking-wide">Applied</span>
                        </div>

                        <!-- Stage B: Screened -->
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition-all duration-300 {{ $currentStageIdx >= 1 ? 'bg-theme-primary text-white scale-110' : 'bg-[var(--theme-card)] text-theme-text/40 border border-[var(--theme-border)]' }}">
                                🔎
                            </div>
                            <span class="text-[10px] font-bold mt-2 text-theme-text uppercase tracking-wide">Screened</span>
                        </div>

                        <!-- Stage C: Lined Up -->
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition-all duration-300 {{ $currentStageIdx >= 2 ? 'bg-theme-primary text-white scale-110' : 'bg-[var(--theme-card)] text-theme-text/40 border border-[var(--theme-border)]' }}">
                                🤝
                            </div>
                            <span class="text-[10px] font-bold mt-2 text-theme-text uppercase tracking-wide">Lined Up</span>
                        </div>

                        <!-- Stage D: Approved -->
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition-all duration-300 {{ $currentStageIdx >= 3 ? 'bg-theme-primary text-white scale-110' : 'bg-[var(--theme-card)] text-theme-text/40 border border-[var(--theme-border)]' }}">
                                🔓
                            </div>
                            <span class="text-[10px] font-bold mt-2 text-theme-text uppercase tracking-wide">Approved</span>
                        </div>

                        <!-- Stage E: Vetting -->
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition-all duration-300 {{ $currentStageIdx >= 4 ? 'bg-theme-primary text-white scale-110' : 'bg-[var(--theme-card)] text-theme-text/40 border border-[var(--theme-border)]' }}">
                                📊
                            </div>
                            <span class="text-[10px] font-bold mt-2 text-theme-text uppercase tracking-wide">Vetting</span>
                        </div>

                        <!-- Stage F: Appointed -->
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm transition-all duration-300 {{ $currentStageIdx >= 5 ? 'bg-emerald-500 text-white scale-110' : 'bg-[var(--theme-card)] text-theme-text/40 border border-[var(--theme-border)]' }}">
                                🎉
                            </div>
                            <span class="text-[10px] font-bold mt-2 text-theme-text uppercase tracking-wide">Hired!</span>
                        </div>
                    </div>
                </div>

                <!-- 🚀 Pipeline Interactive Timeline Progression Tracker (Mobile) -->
                <div class="block md:hidden pl-3 py-4 border-t border-b border-[var(--theme-border)]">
                    <div class="relative flex flex-col gap-6 pl-8">
                        <!-- Progress Background Bar (Vertical) -->
                        <div class="absolute left-4 top-2 bottom-2 w-1 bg-[var(--theme-border)] z-0 rounded-full"></div>
                        
                        <!-- Dynamic Progress Bar height indicator -->
                        @php
                            $vertPct = ($currentStageIdx / (count($stages) - 1)) * 100;
                        @endphp
                        <div class="absolute left-4 top-2 w-1 bg-theme-primary z-0 transition-all duration-500 rounded-full" style="height: {{ $vertPct }}%;"></div>

                        <!-- Stage A: Applied -->
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm shrink-0 transition-all duration-300 {{ $currentStageIdx >= 0 ? 'bg-theme-primary text-white scale-110' : 'bg-[var(--theme-card)] text-theme-text/40 border border-[var(--theme-border)]' }}">
                                📥
                            </div>
                            <span class="text-xs font-bold text-theme-text uppercase tracking-wide">Applied</span>
                        </div>

                        <!-- Stage B: Screened -->
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm shrink-0 transition-all duration-300 {{ $currentStageIdx >= 1 ? 'bg-theme-primary text-white scale-110' : 'bg-[var(--theme-card)] text-theme-text/40 border border-[var(--theme-border)]' }}">
                                🔎
                            </div>
                            <span class="text-xs font-bold text-theme-text uppercase tracking-wide">Screened</span>
                        </div>

                        <!-- Stage C: Lined Up -->
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm shrink-0 transition-all duration-300 {{ $currentStageIdx >= 2 ? 'bg-theme-primary text-white scale-110' : 'bg-[var(--theme-card)] text-theme-text/40 border border-[var(--theme-border)]' }}">
                                🤝
                            </div>
                            <span class="text-xs font-bold text-theme-text uppercase tracking-wide">Lined Up</span>
                        </div>

                        <!-- Stage D: Approved -->
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm shrink-0 transition-all duration-300 {{ $currentStageIdx >= 3 ? 'bg-theme-primary text-white scale-110' : 'bg-[var(--theme-card)] text-theme-text/40 border border-[var(--theme-border)]' }}">
                                🔓
                            </div>
                            <span class="text-xs font-bold text-theme-text uppercase tracking-wide">Approved</span>
                        </div>

                        <!-- Stage E: Vetting -->
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm shrink-0 transition-all duration-300 {{ $currentStageIdx >= 4 ? 'bg-theme-primary text-white scale-110' : 'bg-[var(--theme-card)] text-theme-text/40 border border-[var(--theme-border)]' }}">
                                📊
                            </div>
                            <span class="text-xs font-bold text-theme-text uppercase tracking-wide">Vetting</span>
                        </div>

                        <!-- Stage F: Appointed -->
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm shrink-0 transition-all duration-300 {{ $currentStageIdx >= 5 ? 'bg-emerald-500 text-white scale-110' : 'bg-[var(--theme-card)] text-theme-text/40 border border-[var(--theme-border)]' }}">
                                🎉
                            </div>
                            <span class="text-xs font-bold text-theme-text uppercase tracking-wide">Hired!</span>
                        </div>
                    </div>
                </div>

                <!-- Recruiter / Coaching Details Section -->
                <div class="pl-3 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    
                    <!-- Symmetrical Privacy Card -->
                    <div class="p-4 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg)] flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-theme-text/50 uppercase block mb-1">Hiring Brand / Recruiter details</span>
                            
                            @if($isLinedUp)
                                <!-- UNMASKED coaching details! -->
                                <div class="space-y-2">
                                    <h5 class="text-lg font-black text-theme-primary">🟢 {{ $app->vacancy->institution->company_name }}</h5>
                                    <p class="text-xs text-theme-text/75 leading-relaxed">{{ $app->vacancy->institution->description }}</p>
                                    <div class="pt-2 text-xs flex flex-wrap gap-x-4 gap-y-1">
                                        <span>🌍 Website: <a href="{{ $app->vacancy->institution->website }}" target="_blank" class="text-theme-primary hover:underline font-bold">{{ $app->vacancy->institution->website }}</a></span>
                                        <span>📍 Main Office: <strong class="text-theme-text/80">{{ $app->vacancy->institution->location }}</strong></span>
                                    </div>
                                </div>
                            @else
                                <!-- MASKED coaching details! -->
                                <div class="space-y-3">
                                    <h5 class="text-base font-extrabold text-theme-text/60 italic flex items-center gap-1.5">
                                        🔒 Confidential Recruiter Client
                                    </h5>
                                    <p class="text-[11px] text-theme-text/60 leading-relaxed bg-theme-primary/5 p-3 rounded-lg border border-theme-primary/10">
                                        Identity of this Kota/prep coaching institute is masked. Recruiter will evaluate your academic score and whiteboard teaching video. Once they shortlist your profile and Admin approves the <strong>"Line Up"</strong> request, their full details, address, and websites will be revealed automatically!
                                    </p>
                                    <div class="text-[11px] text-theme-text/50 flex gap-4">
                                        <span>🌍 Website: <span class="font-bold text-theme-text/40">[Hidden]</span></span>
                                        <span>📍 Region: <strong class="text-theme-text/75">{{ $app->vacancy->location }}</strong></span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Vetting Pipeline / Interview Status & Feedback -->
                    <div class="p-4 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-bg)] space-y-3">
                        <span class="text-[10px] font-bold text-theme-text/50 uppercase block">Vetting Funnel Details & feedback</span>
                        
                        <div class="space-y-2 text-xs text-theme-text/85">
                            @if($app->status === 'applied')
                                <p class="italic text-theme-text/60">
                                    ⏳ Your profile is currently in the initial screening phase. Our admin team is reviewing your teaching credentials and Google Scholar publications.
                                </p>
                            @elseif($app->status === 'shortlisted_by_admin')
                                <p class="text-blue-500 font-semibold">
                                    🔎 Our admin team has approved and pre-shortlisted your profile! It has been successfully forwarded to the recruiter.
                                </p>
                            @elseif($app->status === 'shortlisted_by_institution')
                                <p class="text-purple-500 font-semibold animate-pulse">
                                    🤝 Outstanding! The recruiter has shortlisted your masked CV and requested a <strong>"Line Up"</strong>. Awaiting Admin approval to unlock mutual contact details.
                                </p>
                            @elseif($isLinedUp)
                                <div class="space-y-2">
                                    <p class="text-green-600 font-extrabold">
                                        🟢 Mutual Line Up Active! Full profiles are unmasked.
                                    </p>
                                    
                                    @if($app->status === 'written_test')
                                        <p class="bg-indigo-500/10 p-2.5 rounded-lg text-indigo-500 font-bold">
                                            📝 Written Test Round: Recruiter is scheduling your specialized subject test paper. Check your inbox/phone for details.
                                        </p>
                                    @elseif($app->status === 'interview')
                                        <p class="bg-indigo-500/10 p-2.5 rounded-lg text-indigo-500 font-bold">
                                            🗣️ Interview Round: Oral interview and board discussion scheduled. Ensure good audio and visual connection.
                                        </p>
                                    @elseif($app->status === 'demo')
                                        <p class="bg-indigo-500/10 p-2.5 rounded-lg text-indigo-500 font-bold">
                                            📹 Classroom Demo Lecture Round: Live teaching round.
                                            @if($app->demo_rating)
                                                <span class="block mt-1.5 text-theme-primary font-black text-xs uppercase">🎯 Demo rating received: {{ $app->demo_rating }}/10</span>
                                            @endif
                                        </p>
                                    @elseif($app->status === 'appointed')
                                        <p class="bg-emerald-500 text-white p-3 rounded-lg font-black text-center shadow">
                                            🎉 Placed! You have been successfully appointed as faculty member at {{ $app->vacancy->institution->company_name }}. Welcome aboard!
                                        </p>
                                    @else
                                        <p class="italic text-theme-text/60">
                                            🔒 Contact details are revealed! You will receive test/interview details shortly.
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recommendation letter requests tracking for this application -->
                @if($app->recommendationLetters->count() > 0)
                    <div class="pl-3 pt-3 border-t border-[var(--theme-border)] text-xs">
                        <span class="text-theme-primary font-bold tracking-wider uppercase text-[10px] block mb-2">📜 CONFIDENTIAL REFEREE STATUS</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($app->recommendationLetters as $rec)
                                <div class="p-3 rounded-lg bg-[var(--theme-bg)] border border-[var(--theme-border)] flex justify-between items-center text-xs">
                                    <div>
                                        <p class="font-bold text-theme-text">{{ $rec->referee_name }}</p>
                                        <p class="text-[10px] text-theme-text/50">{{ $rec->referee_email }}</p>
                                    </div>
                                    <div>
                                        @if($rec->isSubmitted())
                                            <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-green-500/10 text-green-500 border border-green-500/20">
                                                Received & Screened
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                                Awaiting Submission
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        @empty
            <div class="bg-[var(--theme-card)] rounded-2xl p-8 border border-[var(--theme-border)] text-center text-theme-text/60">
                <span class="text-3xl block mb-2">🧑‍🏫</span>
                <p class="font-bold">No vacancy applications submitted yet.</p>
                <p class="text-xs text-theme-text/50 mt-1">Browse the active jobs and apply to start sourcing pipelines!</p>
                <div class="mt-4">
                    <a href="{{ route('vacancies.index') }}" class="inline-block px-4 py-2 text-xs font-black text-white bg-theme-primary hover:bg-theme-accent rounded-lg shadow transition">
                        Explore Open Vacancies &rarr;
                    </a>
                </div>
            </div>
        @endforelse
    </div>

</div>
@endsection
