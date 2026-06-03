@extends('layouts.app')

@section('title', 'Recruiter Sourcing Console - ' . $institution->company_name)

@section('content')
<div x-data="{ 
    activeTab: window.location.hash.startsWith('#app-') ? 'pipeline' : (localStorage.getItem('recruiter_active_tab') || 'pipeline'),
    selectTab(tab) {
        this.activeTab = tab;
        localStorage.setItem('recruiter_active_tab', tab);
    }
}" class="space-y-8">
    
    <!-- Recruiter Header Banner -->
    <div class="p-8 rounded-2xl bg-gradient-to-br from-theme-primary/10 via-theme-accent/5 to-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm relative overflow-hidden transition-all duration-300">
        <div class="absolute left-0 top-0 bottom-0 w-2 bg-theme-primary"></div>
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 pl-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight mb-2">
                    🏢 Recruiter Candidate Sourcing Console
                </h1>
                <p class="text-theme-text/75 text-base">
                    Welcome back, <strong class="text-theme-primary">{{ auth()->user()->name }}</strong>. Managing hiring for <strong class="text-theme-text font-black">{{ $institution->company_name }}</strong>.
                </p>
                <p class="text-xs text-theme-text/60 mt-2">
                    📍 Head Office: {{ $institution->location }} &bull; 🌐 Site: <a href="{{ $institution->website }}" target="_blank" class="text-theme-primary hover:underline font-bold">{{ $institution->website }}</a>
                </p>
            </div>
            
            <div class="flex gap-2">
                <span class="px-4 py-2 bg-theme-primary/10 text-theme-primary border border-theme-primary/20 text-xs font-black rounded-full uppercase tracking-wider">
                    Partner Brand: {{ ucfirst($institution->type) }}
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
    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-500/10 text-red-500 border border-red-500/20 font-semibold shadow-sm transition">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <!-- 📊 Pipeline Sourcing Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm">
            <span class="block text-[10px] font-bold text-theme-text/50 uppercase">Active Faculty Sourced</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-theme-primary">{{ $applications->count() }}</span>
                <span class="text-xs text-theme-text/50">Masked & Unlocked</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm">
            <span class="block text-[10px] font-bold text-theme-text/50 uppercase">Under Admin Screening</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-amber-500">{{ $pendingAdminScreeningCount }}</span>
                <span class="text-xs text-theme-text/50">Raw Applicants</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm">
            <span class="block text-[10px] font-bold text-theme-text/50 uppercase">In Vetting Funnel</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-indigo-500">
                    {{ $applications->whereIn('status', ['unlocked', 'written_test', 'interview', 'demo'])->count() }}
                </span>
                <span class="text-xs text-theme-text/50">Active Vettings</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm">
            <span class="block text-[10px] font-bold text-theme-text/50 uppercase">Faculty Appointed</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-emerald-500">
                    {{ $applications->where('status', 'appointed')->count() }}
                </span>
                <span class="text-xs text-theme-text/50">Hired!</span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-[var(--theme-border)] gap-4 text-xs md:text-sm font-semibold overflow-x-auto whitespace-nowrap scrollbar-none pb-2 -mb-px">
        <button @click="selectTab('pipeline')" :class="activeTab === 'pipeline' ? 'text-theme-primary border-b-2 border-theme-primary pb-2' : 'text-theme-text/60 pb-2 hover:text-theme-text transition cursor-pointer'">
            🔎 Sourced Candidates Pipeline
        </button>
        <button @click="selectTab('post-vacancy')" :class="activeTab === 'post-vacancy' ? 'text-theme-primary border-b-2 border-theme-primary pb-2' : 'text-theme-text/60 pb-2 hover:text-theme-text transition cursor-pointer'">
            ➕ Post New Vacancy
        </button>
        <button @click="selectTab('my-vacancies')" :class="activeTab === 'my-vacancies' ? 'text-theme-primary border-b-2 border-theme-primary pb-2' : 'text-theme-text/60 pb-2 hover:text-theme-text transition cursor-pointer'">
            💼 My Posted Vacancies ({{ $vacancies->count() }})
        </button>
    </div>

    <!-- Tab Contents -->
    <div class="bg-[var(--theme-card)] rounded-2xl p-8 border border-[var(--theme-border)] shadow-sm">
        
        <!-- Tab 1: Candidates Sourcing Funnel -->
        <div x-show="activeTab === 'pipeline'" class="space-y-6">
            <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3">
                <h3 class="text-lg font-bold">Confidential Faculty Placement Pipeline</h3>
                <span class="px-2.5 py-1 bg-amber-500/10 text-amber-500 text-xs font-bold rounded-full">
                    Candidate Privacy Masking Active
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-theme-text">
                    <thead class="text-xs uppercase bg-[var(--theme-bg)] border border-[var(--theme-border)] text-theme-text/60">
                        <tr>
                            <th class="px-6 py-4">Applied Position</th>
                            <th class="px-6 py-4">Faculty Masked CV Profile</th>
                            <th class="px-6 py-4">Sourcing Stage</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--theme-border)]">
                        @forelse($applications as $app)
                            <tr id="app-{{ $app->id }}" class="hover:bg-[var(--theme-bg)] transition-colors">
                                <!-- Applied Vacancy info -->
                                <td class="px-6 py-4">
                                    <div class="font-bold text-theme-text">{{ $app->vacancy->title }}</div>
                                    <div class="text-xs text-theme-text/50">Subject: {{ $app->vacancy->subject }}</div>
                                    <div class="text-xs text-theme-primary font-bold mt-1">Sourcing ID: #{{ $app->id }}</div>
                                </td>

                                <!-- Masked CV profile details -->
                                <td class="px-6 py-4 max-w-md space-y-2">
                                    @if(in_array($app->status, ['shortlisted_by_admin', 'shortlisted_by_institution']))
                                        <!-- MASKED personal credentials -->
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black bg-slate-900 text-white px-2 py-0.5 rounded">🔒 Candidate #{{ $app->candidate->id }}</span>
                                            <span class="text-xs text-theme-text/45">(Personal Contact Details Masked)</span>
                                        </div>
                                    @else
                                        <!-- UNMASKED full personal details! -->
                                        <div class="space-y-1">
                                            <div class="font-black text-theme-text text-base">🟢 {{ $app->candidate->name }}</div>
                                            <div class="text-xs font-bold text-theme-text/70">
                                                📧 Email: <a href="mailto:{{ $app->candidate->email }}" class="text-theme-primary hover:underline">{{ $app->candidate->email }}</a> &bull; 📞 Mob: {{ $app->candidate->phone ?? 'N/A' }}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Vetting Credentials (CV details are always visible to institute) -->
                                    <div class="p-3 bg-[var(--theme-bg)] rounded-xl border border-[var(--theme-border)] space-y-1 text-xs text-theme-text/75">
                                        <p><strong>Highest Degree:</strong> {{ $app->candidate->candidateProfile?->highest_degree }}</p>
                                        <p><strong>Subjects Taught:</strong> {{ implode(', ', $app->candidate->candidateProfile?->subjects_taught ?? []) }}</p>
                                        <p><strong>Experience:</strong> {{ $app->candidate->candidateProfile?->experience_years }} Years teaching competitive aspirants</p>
                                        <p class="mt-2 text-theme-text/60 italic">"{{ $app->candidate->candidateProfile?->experience_summary }}"</p>
                                        
                                        <!-- Referees -->
                                        @if($app->recommendationLetters->count() > 0)
                                            <div class="mt-2 pt-2 border-t border-[var(--theme-border)] text-[10px]">
                                                <span class="text-theme-primary font-bold">📜 CONFIDENTIAL REFERENCES COLLECTED:</span>
                                                <ul class="list-disc pl-4 mt-1 text-theme-text/50">
                                                    @foreach($app->recommendationLetters as $rec)
                                                        <li>
                                                            {{ $rec->referee_name }} ({{ $rec->referee_email }}) - 
                                                            @if($rec->isSubmitted())
                                                                <span class="text-green-500 font-extrabold">🟢 Received & Verified</span>
                                                            @else
                                                                <span class="text-amber-500 font-semibold">🟡 Awaiting response</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        
                                        <!-- Demo Video Link -->
                                        @if($app->candidate->candidateProfile?->demo_video_url)
                                            <div class="mt-2">
                                                <a href="{{ $app->candidate->candidateProfile->demo_video_url }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-red-500 bg-red-500/10 px-2 py-0.5 rounded hover:bg-red-500/20 transition">
                                                    📹 Watch Candidate Whiteboard Demo
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Pipeline selection stage -->
                                <td class="px-6 py-4">
                                    @if($app->status === 'shortlisted_by_admin')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-500/10 text-blue-500 border border-blue-500/20">
                                            🔎 Shortlisted by Admin
                                        </span>
                                    @elseif($app->status === 'shortlisted_by_institution')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-purple-500/10 text-purple-500 border border-purple-500/20 animate-pulse">
                                            🔑 Lined Up (Awaiting Admin Approval)
                                        </span>
                                    @elseif($app->status === 'unlocked')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-500/10 text-green-500 border border-green-500/20">
                                            🔓 Approved & Details Exchanged
                                        </span>
                                    @elseif($app->status === 'written_test')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-500/10 text-indigo-500 border border-indigo-500/20">
                                            📝 Written Test Stage
                                        </span>
                                    @elseif($app->status === 'interview')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-500/10 text-indigo-500 border border-indigo-500/20">
                                            🗣️ Interview Scheduled
                                        </span>
                                    @elseif($app->status === 'demo')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-500/10 text-indigo-500 border border-indigo-500/20">
                                            📹 In-Class Demo Round
                                        </span>
                                    @elseif($app->status === 'appointed')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-500 text-white">
                                            🎉 Faculty Appointed!
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-500/10 text-red-500">
                                            {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                                        </span>
                                    @endif

                                    <!-- Demo Score Badge if Appointed -->
                                    @if($app->demo_rating)
                                        <div class="mt-2 text-xs font-extrabold text-theme-primary">
                                            🎯 Evaluator Demo Score: {{ $app->demo_rating }}/10
                                        </div>
                                    @endif
                                </td>

                                <!-- Sourcing pipeline actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col gap-2 items-end justify-end">
                                        
                                        @if($app->status === 'shortlisted_by_admin')
                                            <!-- Recruiter Shortlists and Requests Unlock -->
                                            <form action="{{ route('recruiter.request-unlock', $app->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3.5 py-2 text-xs font-extrabold text-white bg-theme-primary hover:bg-theme-accent rounded-lg shadow-sm transition">
                                                    Shortlist & Request Contact Unlock
                                                </button>
                                            </form>
                                        
                                        @elseif($app->status === 'shortlisted_by_institution')
                                            <!-- Awaiting unlock request -->
                                            <span class="text-xs text-theme-text/45 font-bold italic">Awaiting Admin Unlock approval...</span>
                                        
                                        @elseif(in_array($app->status, ['unlocked', 'written_test', 'interview', 'demo']))
                                            <!-- Sourcing vetting steps pipeline management -->
                                            <form action="{{ route('recruiter.update-pipeline', $app->id) }}" method="POST" class="space-y-2 max-w-[200px]">
                                                @csrf
                                                <select name="status" class="w-full p-2 text-xs bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-lg text-theme-text">
                                                    <option value="written_test" {{ $app->status === 'written_test' ? 'selected' : '' }}>📝 Move to Written Test</option>
                                                    <option value="interview" {{ $app->status === 'interview' ? 'selected' : '' }}>🗣️ Schedule Interview</option>
                                                    <option value="demo" {{ $app->status === 'demo' ? 'selected' : '' }}>📹 Schedule Classroom Demo</option>
                                                    <option value="appointed" {{ $app->status === 'appointed' ? 'selected' : '' }}>🎉 Appoint Faculty</option>
                                                    <option value="rejected">❌ Reject Candidate</option>
                                                </select>

                                                <!-- Conditional Demo Rating score box when moving to Demo/Appointed -->
                                                <div class="grid grid-cols-1 gap-1">
                                                    <input type="number" name="demo_rating" min="1.0" max="10.0" step="0.1" value="{{ $app->demo_rating }}" placeholder="Classroom Demo Score (1-10)" class="w-full p-2 text-[10px] bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded text-theme-text" />
                                                </div>

                                                <button type="submit" class="w-full py-1.5 text-[10px] font-black text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition uppercase tracking-wider">
                                                    Update Funnel State
                                                </button>
                                            </form>

                                        @elseif($app->status === 'appointed')
                                            <span class="text-xs text-green-500 font-extrabold">🤝 Faculty onboarding complete!</span>
                                        
                                        @else
                                            <span class="text-xs text-theme-text/40 font-bold italic">Funnel closed.</span>
                                        @endif
                                        
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-theme-text/50">
                                    No pre-shortlisted candidate profiles forwarded by Super-Admin yet. Fresh applicants are currently undergoing admin screening.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Post New Faculty Vacancy -->
        <div x-show="activeTab === 'post-vacancy'" class="space-y-6">
            <h3 class="text-lg font-bold border-b border-[var(--theme-border)] pb-3">Post a New Faculty Recruitment Requirement</h3>
            
            <form action="{{ route('recruiter.post-vacancy') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Vacancy Position Title *</label>
                        <input type="text" name="title" placeholder="e.g. Senior HOD Physics Teacher" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Core Subject Discipline *</label>
                        <input type="text" name="subject" placeholder="e.g. Physics, Botany, Inorganic Chemistry" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Target Student Class *</label>
                        <input type="text" name="target_class" placeholder="e.g. IIT-JEE Advanced, NEET UG" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Experience Required *</label>
                        <input type="text" name="experience_required" placeholder="e.g. 5+ Years Allen/Resonance" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Job Location *</label>
                        <input type="text" name="location" placeholder="e.g. Kota, Rajasthan" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Salary Min (Annual CTC in INR) *</label>
                        <input type="number" name="salary_min" placeholder="e.g. 1200000" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Salary Max (Annual CTC in INR) *</label>
                        <input type="number" name="salary_max" placeholder="e.g. 1800000" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Application Deadline *</label>
                        <input type="date" name="deadline" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Deep Description & Expectations *</label>
                    <textarea name="description" rows="5" placeholder="List syllabus depth, weekly teaching hours, board classroom work expectations, and communication skill constraints..." class="w-full p-4 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[var(--theme-border)]">
                    <button type="submit" class="px-6 py-3 text-sm font-bold text-white bg-theme-primary hover:bg-theme-accent rounded-xl shadow transition transform active:scale-95">
                        Post Requirement for sourcing
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab 3: My Posted Vacancies -->
        <div x-show="activeTab === 'my-vacancies'" class="space-y-4">
            <h3 class="text-lg font-bold border-b border-[var(--theme-border)] pb-3">My Posted Faculty Sourcing Requirements</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-theme-text">
                    <thead class="text-xs uppercase bg-[var(--theme-bg)] border border-[var(--theme-border)] text-theme-text/60">
                        <tr>
                            <th class="px-6 py-4">Vacancy Title</th>
                            <th class="px-6 py-4">Subject</th>
                            <th class="px-6 py-4">Target Class</th>
                            <th class="px-6 py-4">Salary Package</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--theme-border)]">
                        @foreach($vacancies as $vac)
                            <tr class="hover:bg-[var(--theme-bg)] transition-colors">
                                <td class="px-6 py-4 font-bold">{{ $vac->title }}</td>
                                <td class="px-6 py-4 text-theme-text/75">{{ $vac->subject }}</td>
                                <td class="px-6 py-4 text-theme-text/70">{{ $vac->target_class }}</td>
                                <td class="px-6 py-4 font-semibold text-theme-text/90">
                                    ₹{{ number_format($vac->salary_min/100000, 1) }}L - ₹{{ number_format($vac->salary_max/100000, 1) }}L
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $vac->status === 'open' ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">
                                        {{ ucfirst($vac->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('recruiter.vacancy.edit', $vac->id) }}" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-theme-primary/20 text-theme-primary bg-theme-primary/5 hover:bg-theme-primary hover:text-white transition cursor-pointer">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
