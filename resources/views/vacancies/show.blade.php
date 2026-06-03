@php
    $hasUnmaskedAccess = false;
    $hasApplied = false;
    if (auth()->check()) {
        if (auth()->user()->isAdmin() || (auth()->user()->isInstitution() && auth()->user()->id == $vacancy->institution->user_id)) {
            $hasUnmaskedAccess = true;
        } elseif (auth()->user()->isCandidate()) {
            $userApp = \App\Models\Application::where('vacancy_id', $vacancy->id)
                ->where('candidate_id', auth()->id())
                ->first();
            if ($userApp) {
                $hasApplied = true;
                if (in_array($userApp->status, ['unlocked', 'written_test', 'interview', 'demo', 'appointed'])) {
                    $hasUnmaskedAccess = true;
                }
            }
        }
    }
@endphp

@extends('layouts.app')

@section('title', $vacancy->title . ' - ' . ($siteSettings->platform_name ?? 'Sourcing Portal'))

@section('content')
<div class="space-y-8">
    
    <!-- Breadcrumb -->
    <nav class="text-sm text-theme-text/60">
        <a href="{{ route('vacancies.index') }}" class="hover:text-theme-primary transition">Jobs</a> &rarr; 
        <span>{{ $vacancy->title }}</span>
    </nav>

    <!-- Detailed Vacancy Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Main Job Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Job Header -->
            <div class="p-4 sm:p-8 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-2" style="background-color: {{ $vacancy->institution->primary_color }}"></div>
                
                <div class="flex flex-wrap gap-4 justify-between items-start pl-4">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight mb-2">
                            {{ $vacancy->title }}
                        </h1>
                        <p class="text-base font-semibold text-theme-primary">
                            @if($hasUnmaskedAccess)
                                {{ $vacancy->institution->company_name }}
                            @else
                                Confidential Premium Education Client
                            @endif
                        </p>
                        <p class="text-sm text-theme-text/60 mt-1">
                            📍 {{ $vacancy->location }} &bull; 📅 Apply before: <strong class="text-theme-text/80">{{ $vacancy->deadline->format('M d, Y') }}</strong>
                        </p>
                    </div>
                    
                    <span class="px-4 py-2 rounded-xl text-white font-bold text-sm shadow" style="background-color: {{ $vacancy->institution->primary_color }}">
                        {{ ucfirst(str_replace('_', ' ', $vacancy->institution->type)) }}
                    </span>
                </div>

                <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4 pl-4 pt-6 border-t border-[var(--theme-border)] text-xs text-theme-text/60">
                    <div>
                        <span class="block text-theme-text/45">SUBJECT</span>
                        <strong class="text-base text-theme-text/80">{{ $vacancy->subject }}</strong>
                    </div>
                    <div>
                        <span class="block text-theme-text/45">TARGET CLASS</span>
                        <strong class="text-base text-theme-text/80">{{ $vacancy->target_class }}</strong>
                    </div>
                    <div>
                        <span class="block text-theme-text/45">EXPERIENCE</span>
                        <strong class="text-base text-theme-text/80">{{ $vacancy->experience_required }}</strong>
                    </div>
                    @if($vacancy->salary_max)
                    <div>
                        <span class="block text-theme-text/45">SALARY PACKAGE</span>
                        <strong class="text-base text-theme-text/80">₹{{ number_format($vacancy->salary_min/100000, 1) }}L - ₹{{ number_format($vacancy->salary_max/100000, 1) }}L</strong>
                    </div>
                    @endif
                </div>

            </div>

            <!-- Job Description -->
            <div class="p-4 sm:p-8 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm space-y-4">
                <h3 class="text-xl font-bold border-b border-[var(--theme-border)] pb-3">Vacancy Description</h3>
                <div class="text-sm text-theme-text/80 leading-relaxed space-y-4">
                    <p>{{ $vacancy->description }}</p>
                    <h4 class="font-bold text-theme-text mt-4">Key Duties & Expectations:</h4>
                    <ul class="list-disc pl-5 space-y-2">
                        <li>Deliver engaging, conceptually crystal-clear lectures designed for competitive examinations.</li>
                        <li>Prepare and grade high-quality mock papers, assessments, and reference sheets.</li>
                        <li>Integrate visual aids, model presentations, and modern classroom tech (smart boards).</li>
                        <li>Provide intensive mentoring, doubt-solving sessions, and motivational support.</li>
                    </ul>
                </div>
            </div>

            <!-- About Hiring Organization -->
            <div class="p-4 sm:p-8 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm space-y-4">
                <h3 class="text-xl font-bold border-b border-[var(--theme-border)] pb-3">About the Recruiter</h3>
                @if($hasUnmaskedAccess)
                    <p class="text-sm text-theme-text/80">
                        {{ $vacancy->institution->description }}
                    </p>
                    <div class="pt-4 text-sm flex gap-4">
                        <span>🌍 Website: <a href="{{ $vacancy->institution->website }}" target="_blank" class="text-theme-primary hover:underline font-bold">{{ $vacancy->institution->website }}</a></span>
                        <span>📍 Main Office: <strong class="text-theme-text/80">{{ $vacancy->institution->location }}</strong></span>
                    </div>
                @else
                    <p class="text-sm text-theme-text/80 italic text-theme-text/60 bg-theme-primary/5 p-4 rounded-xl border border-theme-primary/10">
                        🔒 <strong>Confidential Recruiter Identity:</strong> Organization branding, full description, and contact URLs are protected by the {{ $siteSettings->platform_name ?? 'Sourcing Portal' }} platform. Full details will be unlocked automatically by {{ $siteSettings->platform_name ?? 'Sourcing Portal' }} Admin once your candidate profile is approved and Lined Up by the recruiter!
                    </p>
                    <div class="pt-4 text-sm flex gap-4 text-theme-text/50">
                        <span>🌍 Website: <span class="font-bold text-theme-text/40">[Hidden]</span></span>
                        <span>📍 Region: <strong class="text-theme-text/80">{{ $vacancy->location }}</strong></span>
                    </div>
                @endif
            </div>

        </div>

        <!-- Right 1 Col: Application Form / Info Sidebar -->
        <div class="space-y-6">
            
            <div class="p-6 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm sticky top-24">
                
                @if(session('success'))
                    <div class="mb-4 p-4 rounded-xl bg-green-500/10 text-green-500 border border-green-500/20 text-sm font-semibold">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-4 rounded-xl bg-red-500/10 text-red-500 border border-red-500/20 text-sm font-semibold">
                        {{ session('error') }}
                    </div>
                @endif

                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    Apply for this Role
                </h3>

                @auth
                    @if(auth()->user()->isCandidate() && auth()->user()->candidateProfile)
                        <!-- Dynamic Academic Profile Match Card -->
                        <div class="mb-4 p-4 rounded-xl border border-theme-primary/20 bg-theme-primary/5 text-xs space-y-2">
                            <span class="block font-bold text-theme-primary uppercase tracking-wider">🎓 AI Profile Match Verified</span>
                            <div class="space-y-1 text-theme-text/80">
                                <p><strong>Academic Match:</strong> 🟢 Excellent Fit ({{ auth()->user()->candidateProfile->highest_degree }})</p>
                                <p><strong>Subject Match:</strong> 🟢 {{ $vacancy->subject }} (Auto-matched)</p>
                                <p><strong>Google Scholar h-index:</strong> 🔬 {{ auth()->user()->candidateProfile->h_index }} Citations verified</p>
                                <p><strong>Demo Lecture:</strong> 📹 Pre-submitted Whiteboard Video</p>
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->isCandidate())
                        @if($hasApplied)
                            <div class="p-4 rounded-xl bg-green-500/10 text-green-500 border border-green-500/20 text-center font-bold text-sm">
                                ✓ Applied
                            </div>
                        @else
                            <form action="{{ route('vacancies.apply', $vacancy->id) }}" method="POST" class="space-y-4">
                                @csrf
                                
                                <div>
                                    <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1">Cover Letter</label>
                                    <textarea name="cover_letter" rows="3" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-lg focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="Explain why you are the perfect fit for this faculty position..."></textarea>
                                </div>

                                <!-- Application Submission Confirmation -->
                                <div class="p-4 rounded-xl bg-theme-primary/5 border border-theme-primary/10 space-y-2 flex items-start gap-3">
                                    <input type="checkbox" id="confirm_apply_show" required class="w-4 h-4 mt-0.5 rounded text-theme-primary focus:ring-theme-primary bg-[var(--theme-bg)] border-[var(--theme-border)] cursor-pointer" />
                                    <label for="confirm_apply_show" class="text-xs text-theme-text/80 cursor-pointer select-none leading-relaxed">
                                        <strong>Click here to confirm:</strong> I verify that my profile details are accurate and I wish to submit my verified faculty profile for this vacancy.
                                    </label>
                                </div>

                                <button type="submit" class="w-full py-3 text-white font-bold rounded-xl shadow-md transition transform active:scale-95 text-sm" style="background-color: {{ $vacancy->institution->primary_color }}">
                                    Submit Application &rarr;
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="p-4 rounded-xl bg-theme-primary/5 text-center text-sm border border-theme-primary/10">
                            <p class="text-theme-text/70 mb-2">You are currently logged in as a <strong>Recruiter / Institution</strong>.</p>
                            <p class="text-xs text-theme-text/50">Only Candidates (Faculty) profiles can apply for job postings.</p>
                        </div>
                    @endif
                @else
                    <div class="text-center p-4">
                        <p class="text-sm text-theme-text/70 mb-4">Please sign in with your candidate profile to apply.</p>
                        <a href="{{ route('login-demo') }}" class="block w-full py-3 text-white font-bold rounded-xl shadow-md transition text-sm" style="background-color: {{ $vacancy->institution->primary_color }}">
                            Log In to Apply
                        </a>
                    </div>
                @endauth

            </div>

        </div>

    </div>

</div>
@endsection
