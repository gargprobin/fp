@extends('layouts.app')

@section('title', 'Portal Control, Branding & Messaging Center')

@section('content')
<div x-data="{ 
    activeTab: '{{ request('tab') }}' 
        ? '{{ request('tab') }}' 
        : (window.location.hash.startsWith('#app-') 
            ? 'applications' 
            : (window.location.hash.startsWith('#user-') 
                ? 'users' 
                : (localStorage.getItem('admin_active_tab') || 'settings'))),
    selectTab(tab) {
        this.activeTab = tab;
        localStorage.setItem('admin_active_tab', tab);
    },
    
    // Search Filters
    userSearch: '',
    userRoleFilter: 'all',
    vacancySearch: '',
    appSearch: '',
    
    // Add/Edit User Modals
    showAddUserModal: false,
    showEditUserModal: false,
    editUser: { 
        id: '', name: '', email: '', phone: '', role: 'candidate', is_verified: false,
        highest_degree: '', subjects_taught: '', experience_years: 0, current_designation: '',
        experience_summary: '', current_ctc: '', expected_ctc: '', preferred_location: '',
        demo_video_url: '', google_scholar_url: '', h_index: 0, candidate_type: 'academic',
        company_name: '', institution_type: 'coaching_center', location: '', website: '', description: ''
    },
    
    // Add/Edit Vacancy Modals
    showAddVacancyModal: false,
    showEditVacancyModal: false,
    editVacancy: { id: '', title: '', description: '', subject: '', target_class: '', experience_required: '', location: '', salary_min: '', salary_max: '', deadline: '', institution_id: '' },
    
    // WhatsApp Sandbox States
    selectedTemplate: 'fp_candidate_applied',
    templateVars: {
        'fp_candidate_applied': ['Candidate Name', 'Vacancy Title', 'Dashboard URL', 'Platform Name'],
        'fp_recruiter_sourced': ['Recruiter Name', 'Vacancy Title', 'Degree', 'Experience', 'Subject', 'Dashboard URL', 'Platform Name'],
        'fp_candidate_lined_up': ['Candidate Name', 'Vacancy Title', 'Company Name', 'Dashboard URL', 'Platform Name'],
        'fp_lineup_approved_candidate': ['Candidate Name', 'Company Name', 'Vacancy Title', 'Website URL', 'Dashboard URL', 'Platform Name'],
        'fp_lineup_approved_recruiter': ['Recruiter Name', 'Vacancy Title', 'Candidate Name', 'Candidate Email', 'Candidate Phone', 'Dashboard URL', 'Platform Name'],
        'fp_pipeline_stage_updated': ['Candidate Name', 'Vacancy Title', 'Stage Name', 'Dashboard URL', 'Platform Name']
    },
    
    openEditUser(user) {
        this.editUser = { 
            id: user.id, 
            name: user.name, 
            email: user.email, 
            phone: user.phone, 
            role: user.role, 
            is_verified: user.is_verified,
            highest_degree: user.highest_degree || '',
            subjects_taught: user.subjects_taught || '',
            experience_years: user.experience_years || 0,
            current_designation: user.current_designation || '',
            experience_summary: user.experience_summary || '',
            current_ctc: user.current_ctc || '',
            expected_ctc: user.expected_ctc || '',
            preferred_location: user.preferred_location || '',
            demo_video_url: user.demo_video_url || '',
            google_scholar_url: user.google_scholar_url || '',
            h_index: user.h_index || 0,
            candidate_type: user.candidate_type || 'academic',
            company_name: user.company_name || '',
            institution_type: user.institution_type || 'coaching_center',
            location: user.location || '',
            website: user.website || '',
            description: user.description || ''
        };
        this.showEditUserModal = true;
    },
    
    openEditVacancy(vacancy) {
        this.editVacancy = { 
            id: vacancy.id,
            title: vacancy.title,
            description: vacancy.description,
            subject: vacancy.subject,
            target_class: vacancy.target_class,
            experience_required: vacancy.experience_required,
            location: vacancy.location,
            salary_min: vacancy.salary_min,
            salary_max: vacancy.salary_max,
            deadline: vacancy.deadline,
            institution_id: vacancy.institution_id
        };
        this.showEditVacancyModal = true;
    }
}" class="space-y-8">
    
    @php
        $institutions = $users->filter(fn($u) => $u->role === 'institution' && $u->institution !== null)->map(fn($u) => $u->institution);
    @endphp

    <!-- Header Banner -->
    <div class="p-8 rounded-2xl bg-gradient-to-br from-red-500/10 via-theme-accent/5 to-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm relative overflow-hidden transition-all duration-300">
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight mb-1 flex items-center gap-2">
                    ⚙️ Portal Control, Branding & Messaging Center
                </h1>
                <p class="text-sm text-theme-text/75">
                    Manage global dynamic settings, configure WhatsApp API integrations, moderate active vacancies, track applications, and verify candidate/partner credentials.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.clear-cache') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear the entire system cache? This will reset all cached views and route settings.')">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-bold text-xs rounded-xl shadow-sm transition-all duration-200 flex items-center gap-1.5 border border-amber-600/20 cursor-pointer">
                        ⚡ Clear System Cache
                    </button>
                </form>
                <span class="px-4 py-2.5 bg-red-500/10 text-red-500 border border-red-500/20 text-xs font-bold rounded-full">
                    SYSTEM CONTROL
                </span>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-green-500/10 text-green-500 border border-green-500/20 font-semibold shadow-sm transition">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-500/10 text-red-500 border border-red-500/20 font-semibold shadow-sm transition">
            {{ session('error') }}
        </div>
    @endif

    <!-- 📊 Portal Platform Live Statistics Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="p-5 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm">
            <span class="block text-[10px] font-bold text-theme-text/50 uppercase">Registered Candidates</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-theme-primary">{{ $totalCandidates }}</span>
                <span class="text-xs text-theme-text/50">Active Users</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm">
            <span class="block text-[10px] font-bold text-theme-text/50 uppercase">Institutional Clients</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-theme-accent">{{ $totalInstitutions }}</span>
                <span class="text-xs text-theme-text/50">Institutional Partners</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm">
            <span class="block text-[10px] font-bold text-theme-text/50 uppercase">Posted Vacancies</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-amber-500">{{ $totalVacancies }}</span>
                <span class="text-xs text-theme-text/50">Active Boards</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm">
            <span class="block text-[10px] font-bold text-theme-text/50 uppercase">Total Applications</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-emerald-500">{{ $totalApplications }}</span>
                <span class="text-xs text-theme-text/50">Matches Made</span>
            </div>
        </div>

    </div>

    <!-- Interactive Navigation Tabs -->
    <div class="flex border-b border-[var(--theme-border)] gap-4 text-xs md:text-sm font-semibold overflow-x-auto whitespace-nowrap scrollbar-none pb-2 -mb-px">
        <button @click="selectTab('settings')" :class="activeTab === 'settings' ? 'text-red-500 border-b-2 border-red-500 pb-2' : 'text-theme-text/60 pb-2 hover:text-theme-text transition cursor-pointer'">
            🔧 Portal Settings
        </button>
        <button @click="selectTab('whatsapp')" :class="activeTab === 'whatsapp' ? 'text-red-500 border-b-2 border-red-500 pb-2' : 'text-theme-text/60 pb-2 hover:text-theme-text transition cursor-pointer'">
            💬 WhatsApp Settings
        </button>
        <button @click="selectTab('users')" :class="activeTab === 'users' ? 'text-red-500 border-b-2 border-red-500 pb-2' : 'text-theme-text/60 pb-2 hover:text-theme-text transition cursor-pointer'">
            👥 User Moderation
        </button>
        <button @click="selectTab('vacancies')" :class="activeTab === 'vacancies' ? 'text-red-500 border-b-2 border-red-500 pb-2' : 'text-theme-text/60 pb-2 hover:text-theme-text transition cursor-pointer'">
            💼 Vacancies
        </button>
        <button @click="selectTab('applications')" :class="activeTab === 'applications' ? 'text-red-500 border-b-2 border-red-500 pb-2' : 'text-theme-text/60 pb-2 hover:text-theme-text transition cursor-pointer'">
            📝 Sourcing Pipeline
        </button>
        <button @click="selectTab('trash')" :class="activeTab === 'trash' ? 'text-red-500 border-b-2 border-red-500 pb-2' : 'text-theme-text/60 pb-2 hover:text-theme-text transition cursor-pointer'">
            ♻️ System Trash
        </button>
    </div>

    <!-- Tab Contents -->
    <div class="bg-[var(--theme-card)] rounded-2xl p-8 border border-[var(--theme-border)] shadow-sm">
        
        <!-- Tab A: Portal Settings -->
        <div x-show="activeTab === 'settings'" class="space-y-6">
            <h3 class="text-lg font-bold border-b border-[var(--theme-border)] pb-3">Update Global Portal Info</h3>
            
            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Portal Brand Name *</label>
                        <input type="text" name="platform_name" value="{{ old('platform_name', $platformName) }}" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Platform Support Email *</label>
                        <input type="email" name="support_email" value="{{ old('support_email', $supportEmail) }}" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Homepage Hero Tagline *</label>
                    <input type="text" name="platform_tagline" value="{{ old('platform_tagline', $platformTagline) }}" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[var(--theme-border)]">
                    <button type="submit" class="px-6 py-3 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow transition transform active:scale-95 cursor-pointer">
                        Save Global Portal Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab B: User Moderation -->
        <div x-show="activeTab === 'users'" class="space-y-4">
            <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3 mb-2">
                <h3 class="text-lg font-bold">Moderate Active Platform Users</h3>
                <span class="px-2.5 py-1 bg-theme-primary/10 text-theme-primary text-xs font-black rounded-full uppercase">
                    Users List
                </span>
            </div>

            <!-- Search and Add User Controls -->
            <form action="{{ route('admin.settings') }}" method="GET" class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 w-full">
                <input type="hidden" name="tab" value="users" />
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <input type="text" name="search_user" value="{{ request('search_user') }}" placeholder="🔍 Search users by name, email..." class="p-2.5 text-xs bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text w-full sm:w-60 font-medium" />
                    <select name="user_role" onchange="this.form.submit()" class="p-2.5 text-xs bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold">
                        <option value="all" {{ request('user_role') === 'all' ? 'selected' : '' }}>👥 All Roles</option>
                        <option value="candidate" {{ request('user_role') === 'candidate' ? 'selected' : '' }}>🧑‍🏫 Candidates</option>
                        <option value="institution" {{ request('user_role') === 'institution' ? 'selected' : '' }}>🏢 Institutions</option>
                        <option value="admin" {{ request('user_role') === 'admin' ? 'selected' : '' }}>🛠️ Admins</option>
                    </select>
                    <select name="sort_user" onchange="this.form.submit()" class="p-2.5 text-xs bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold">
                        <option value="latest" {{ request('sort_user') === 'latest' ? 'selected' : '' }}>📅 Latest</option>
                        <option value="name_asc" {{ request('sort_user') === 'name_asc' ? 'selected' : '' }}>🔤 Name A-Z</option>
                        <option value="name_desc" {{ request('sort_user') === 'name_desc' ? 'selected' : '' }}>🔤 Name Z-A</option>
                    </select>
                    <button type="submit" class="px-4 py-2.5 bg-red-600 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-red-700 transition cursor-pointer">Filter</button>
                    @if(request()->anyFilled(['search_user', 'user_role', 'sort_user']))
                        <a href="{{ route('admin.settings', ['tab' => 'users']) }}" class="text-xs text-red-500 font-bold hover:underline">Clear</a>
                    @endif
                </div>
                <button type="button" @click="showAddUserModal = true" class="w-full sm:w-auto px-4 py-2.5 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                    ➕ Add New User
                </button>
            </form>

            <div class="overflow-x-auto border border-[var(--theme-border)] rounded-xl">
                <table class="w-full text-sm text-left text-theme-text">
                    <thead class="text-xs uppercase bg-[var(--theme-bg)] border-b border-[var(--theme-border)] text-theme-text/60">
                        <tr>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Phone</th>
                            <th class="px-6 py-4">Verification</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--theme-border)] bg-[var(--theme-bg)]">
                        @foreach($users as $user)
                            <tr id="user-{{ $user->id }}" class="hover:bg-theme-primary/5 transition-colors">
                                <td class="px-6 py-4 font-bold">
                                    <div class="flex flex-col">
                                        <span>{{ $user->name }}</span>
                                        @if($user->role === 'institution' && $user->institution)
                                            <span class="text-[10px] text-theme-accent font-semibold">Brand: {{ $user->institution->company_name }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $user->role === 'candidate' ? 'bg-blue-500/10 text-blue-500' : ($user->role === 'institution' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-theme-text/70">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-theme-text/70 font-semibold">{{ $user->phone ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $user->is_verified ? 'text-green-500' : 'text-amber-500' }}">
                                        {{ $user->is_verified ? '🟢 Verified Badge' : '🟡 Unverified' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Impersonate Button -->
                                        @if($user->id !== auth()->id() && !$user->isAdmin())
                                            <form action="{{ route('admin.impersonate', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-amber-500/10 text-amber-600 hover:bg-amber-500 hover:text-white transition flex items-center gap-1 cursor-pointer" title="Login as this user">
                                                    👤 Login As
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('admin.user.toggle-verification', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 text-xs font-bold rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg)] hover:bg-theme-primary/10 transition cursor-pointer">
                                                {{ $user->is_verified ? 'Unverify' : 'Verify' }}
                                            </button>
                                        </form>

                                        <button @click="openEditUser({
                                             id: {{ json_encode($user->id) }},
                                             name: {{ json_encode($user->name) }},
                                             email: {{ json_encode($user->email) }},
                                             phone: {{ json_encode($user->phone) }},
                                             role: {{ json_encode($user->role) }},
                                             is_verified: {{ $user->is_verified ? 'true' : 'false' }},
                                             highest_degree: {{ json_encode($user->candidateProfile ? $user->candidateProfile->highest_degree : '') }},
                                             subjects_taught: {{ json_encode($user->candidateProfile ? (is_array($user->candidateProfile->subjects_taught) ? implode(', ', $user->candidateProfile->subjects_taught) : $user->candidateProfile->subjects_taught) : '') }},
                                             experience_years: {{ json_encode($user->candidateProfile ? $user->candidateProfile->experience_years : 0) }},
                                             current_designation: {{ json_encode($user->candidateProfile ? $user->candidateProfile->current_designation : '') }},
                                             experience_summary: {{ json_encode($user->candidateProfile ? $user->candidateProfile->experience_summary : '') }},
                                             current_ctc: {{ json_encode($user->candidateProfile ? $user->candidateProfile->current_ctc : '') }},
                                             expected_ctc: {{ json_encode($user->candidateProfile ? $user->candidateProfile->expected_ctc : '') }},
                                             preferred_location: {{ json_encode($user->candidateProfile ? $user->candidateProfile->preferred_location : '') }},
                                             demo_video_url: {{ json_encode($user->candidateProfile ? $user->candidateProfile->demo_video_url : '') }},
                                             google_scholar_url: {{ json_encode($user->candidateProfile ? $user->candidateProfile->google_scholar_url : '') }},
                                             h_index: {{ json_encode($user->candidateProfile ? $user->candidateProfile->h_index : 0) }},
                                             candidate_type: {{ json_encode($user->candidateProfile ? $user->candidateProfile->type : 'academic') }},
                                             company_name: {{ json_encode($user->institution ? $user->institution->company_name : '') }},
                                             institution_type: {{ json_encode($user->institution ? $user->institution->type : 'coaching_center') }},
                                             location: {{ json_encode($user->institution ? $user->institution->location : '') }},
                                             website: {{ json_encode($user->institution ? $user->institution->website : '') }},
                                             description: {{ json_encode($user->institution ? $user->institution->description : '') }}
                                         })" class="px-2.5 py-1.5 text-xs font-bold rounded-lg border border-theme-primary/20 text-theme-primary bg-theme-primary/5 hover:bg-theme-primary hover:text-white transition cursor-pointer">
                                            Edit
                                        </button>

                                        <!-- Delete User -->
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.user.delete', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to move this user to the system trash?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1.5 text-xs font-bold rounded-lg text-red-500 bg-red-500/10 hover:bg-red-500 hover:text-white transition cursor-pointer">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>

        <!-- Tab C: Vacancies Moderation -->
        <div x-show="activeTab === 'vacancies'" class="space-y-4">
            <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3 mb-2">
                <h3 class="text-lg font-bold">Moderate Faculty Vacancies</h3>
                <span class="px-2.5 py-1 bg-amber-500/10 text-amber-500 text-xs font-black rounded-full uppercase">
                    Vacancies List
                </span>
            </div>

            <!-- Search and Add Vacancy Controls -->
            <form action="{{ route('admin.settings') }}" method="GET" class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 w-full">
                <input type="hidden" name="tab" value="vacancies" />
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <input type="text" name="search_vacancy" value="{{ request('search_vacancy') }}" placeholder="🔍 Search jobs by title, subject, brand..." class="p-2.5 text-xs bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text w-full sm:w-60 font-medium" />
                    <select name="sort_vacancy" onchange="this.form.submit()" class="p-2.5 text-xs bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold">
                        <option value="latest" {{ request('sort_vacancy') === 'latest' ? 'selected' : '' }}>📅 Latest</option>
                        <option value="title_asc" {{ request('sort_vacancy') === 'title_asc' ? 'selected' : '' }}>🔤 Title A-Z</option>
                        <option value="salary_high" {{ request('sort_vacancy') === 'salary_high' ? 'selected' : '' }}>💰 Highest Salary</option>
                    </select>
                    <button type="submit" class="px-4 py-2.5 bg-red-600 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-red-700 transition cursor-pointer">Filter</button>
                    @if(request()->anyFilled(['search_vacancy', 'sort_vacancy']))
                        <a href="{{ route('admin.settings', ['tab' => 'vacancies']) }}" class="text-xs text-red-500 font-bold hover:underline">Clear</a>
                    @endif
                </div>
                <button type="button" @click="showAddVacancyModal = true" class="w-full sm:w-auto px-4 py-2.5 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                    ➕ Post New Vacancy
                </button>
            </form>

            <div class="overflow-x-auto border border-[var(--theme-border)] rounded-xl">
                <table class="w-full text-sm text-left text-theme-text">
                    <thead class="text-xs uppercase bg-[var(--theme-bg)] border-b border-[var(--theme-border)] text-theme-text/60">
                        <tr>
                            <th class="px-6 py-4">Vacancy Title</th>
                            <th class="px-6 py-4">Hiring Brand</th>
                            <th class="px-6 py-4">Subject</th>
                            <th class="px-6 py-4">Salary Range</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--theme-border)] bg-[var(--theme-bg)]">
                        @forelse($vacancies as $vacancy)
                            <tr class="hover:bg-theme-primary/5 transition-colors">
                                <td class="px-6 py-4 font-bold">{{ $vacancy->title }}</td>
                                <td class="px-6 py-4 text-theme-primary font-semibold">{{ $vacancy->institution->company_name }}</td>
                                <td class="px-6 py-4 text-theme-text/75">{{ $vacancy->subject }} ({{ $vacancy->target_class }})</td>
                                <td class="px-6 py-4 text-theme-text/70 font-semibold">₹{{ number_format($vacancy->salary_min) }} - ₹{{ number_format($vacancy->salary_max) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $vacancy->status === 'open' ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">
                                        {{ ucfirst($vacancy->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Toggle Status -->
                                        <form action="{{ route('admin.vacancy.toggle-status', $vacancy->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 text-xs font-bold rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg)] hover:bg-theme-primary/10 transition cursor-pointer">
                                                {{ $vacancy->status === 'open' ? 'Close Board' : 'Open Board' }}
                                            </button>
                                        </form>

                                        <!-- Edit Vacancy Button -->
                                        <button @click="openEditVacancy({
                                            id: {{ json_encode($vacancy->id) }},
                                            title: {{ json_encode($vacancy->title) }},
                                            description: {{ json_encode($vacancy->description) }},
                                            subject: {{ json_encode($vacancy->subject) }},
                                            target_class: {{ json_encode($vacancy->target_class) }},
                                            experience_required: {{ json_encode($vacancy->experience_required) }},
                                            location: {{ json_encode($vacancy->location) }},
                                            salary_min: {{ json_encode($vacancy->salary_min) }},
                                            salary_max: {{ json_encode($vacancy->salary_max) }},
                                            deadline: {{ json_encode($vacancy->deadline ? $vacancy->deadline->format('Y-m-d') : '') }},
                                            institution_id: {{ json_encode($vacancy->institution_id) }}
                                        })" class="px-2.5 py-1.5 text-xs font-bold rounded-lg border border-theme-primary/20 text-theme-primary bg-theme-primary/5 hover:bg-theme-primary hover:text-white transition cursor-pointer">
                                            Edit
                                        </button>

                                        <!-- Delete Vacancy -->
                                        <form action="{{ route('admin.vacancy.delete', $vacancy->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to move this job vacancy to the system trash?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 text-xs font-bold rounded-lg text-red-500 bg-red-500/10 hover:bg-red-500 hover:text-white transition cursor-pointer">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-theme-text/50">No vacancies posted on the platform yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $vacancies->links() }}
            </div>
        </div>

        <!-- Tab D: Active Applications -->
        <div x-show="activeTab === 'applications'" class="space-y-4">
            <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3 mb-2">
                <h3 class="text-lg font-bold">Monitor & Moderate Faculty Sourcing Pipeline</h3>
                <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-500 text-xs font-black rounded-full uppercase">
                    Pipeline Funnel
                </span>
            </div>
            <p class="text-xs text-theme-text/60 leading-normal">
                💼 <strong>Placement CRM Sourcing Flow:</strong> Sourcing is moderated via Admin approval loops. Shortlist applicants to forward masked CVs, or Approve unlock requests to reveal contact details!
            </p>

            <!-- Search Controls -->
            <form action="{{ route('admin.settings') }}" method="GET" class="flex flex-wrap items-center gap-3 mb-4 w-full">
                <input type="hidden" name="tab" value="applications" />
                <input type="text" name="search_app" value="{{ request('search_app') }}" placeholder="🔍 Search pipeline by candidate or vacancy..." class="p-2.5 text-xs bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text w-full sm:w-60 font-medium" />
                <select name="status_app" onchange="this.form.submit()" class="p-2.5 text-xs bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold">
                    <option value="all" {{ request('status_app') === 'all' ? 'selected' : '' }}>📌 All Statuses</option>
                    <option value="applied" {{ request('status_app') === 'applied' ? 'selected' : '' }}>📥 Applied</option>
                    <option value="shortlisted_by_admin" {{ request('status_app') === 'shortlisted_by_admin' ? 'selected' : '' }}>🔎 Shortlisted (Admin)</option>
                    <option value="shortlisted_by_institution" {{ request('status_app') === 'shortlisted_by_institution' ? 'selected' : '' }}>🔑 Lined Up</option>
                    <option value="unlocked" {{ request('status_app') === 'unlocked' ? 'selected' : '' }}>🔓 Unlocked</option>
                    <option value="appointed" {{ request('status_app') === 'appointed' ? 'selected' : '' }}>🎉 Appointed</option>
                    <option value="rejected" {{ request('status_app') === 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                </select>
                <select name="sort_app" onchange="this.form.submit()" class="p-2.5 text-xs bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold">
                    <option value="latest" {{ request('sort_app') === 'latest' ? 'selected' : '' }}>📅 Latest</option>
                    <option value="oldest" {{ request('sort_app') === 'oldest' ? 'selected' : '' }}>📅 Oldest</option>
                </select>
                <button type="submit" class="px-4 py-2.5 bg-red-600 text-white text-xs font-bold rounded-xl shadow-sm hover:bg-red-700 transition cursor-pointer">Filter</button>
                @if(request()->anyFilled(['search_app', 'status_app', 'sort_app']))
                    <a href="{{ route('admin.settings', ['tab' => 'applications']) }}" class="text-xs text-red-500 font-bold hover:underline">Clear</a>
                @endif
            </form>

            <div class="overflow-x-auto border border-[var(--theme-border)] rounded-xl">
                <table class="w-full text-sm text-left text-theme-text">
                    <thead class="text-xs uppercase bg-[var(--theme-bg)] border-b border-[var(--theme-border)] text-theme-text/60">
                        <tr>
                            <th class="px-6 py-4">Candidate Profile</th>
                            <th class="px-6 py-4">Applied Vacancy</th>
                            <th class="px-6 py-4">Recruiter / Client</th>
                            <th class="px-6 py-4">Pipeline Status</th>
                            <th class="px-6 py-4 text-right">Moderator Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--theme-border)] bg-[var(--theme-bg)]">
                        @forelse($applications as $application)
                            <tr id="app-{{ $application->id }}" class="hover:bg-theme-primary/5 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-theme-text">{{ $application->candidate->name }}</div>
                                    <div class="text-xs text-theme-text/50">Degree: {{ $application->candidate->candidateProfile?->highest_degree ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-theme-text/80">{{ $application->vacancy->title }}</div>
                                    <div class="text-xs text-theme-text/50">Subject: {{ $application->vacancy->subject }}</div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-theme-primary">
                                    {{ $application->vacancy->institution->company_name }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($application->status === 'applied')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                            📥 Raw Applicant
                                        </span>
                                    @elseif($application->status === 'shortlisted_by_admin')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-500/10 text-blue-500 border border-blue-500/20">
                                            🔎 Shortlisted by Admin (Masked)
                                        </span>
                                    @elseif($application->status === 'shortlisted_by_institution')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-purple-500/10 text-purple-500 border border-purple-500/20 animate-pulse">
                                            🔑 Lined Up (Awaiting Approval)
                                        </span>
                                    @elseif($application->status === 'unlocked')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-500/10 text-green-500 border border-green-500/20">
                                            🔓 Approved & Details Exchanged
                                        </span>
                                    @elseif(in_array($application->status, ['written_test', 'interview', 'demo']))
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-500/10 text-indigo-500 border border-indigo-500/20">
                                            ⚙️ {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                        </span>
                                    @elseif($application->status === 'appointed')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-500 text-white border border-emerald-600">
                                            🎉 Appointed!
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-500/10 text-red-500 border border-red-500/20">
                                            {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($application->status === 'applied')
                                            <form action="{{ route('admin.application.shortlist', $application->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 text-xs font-extrabold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition cursor-pointer">
                                                    Shortlist Candidate
                                                </button>
                                            </form>
                                        @elseif($application->status === 'shortlisted_by_institution')
                                            <form action="{{ route('admin.application.unlock-approve', $application->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 text-xs font-extrabold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm transition animate-pulse cursor-pointer">
                                                    Approve Line Up & Exchange
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-theme-text/40 font-semibold italic mr-2">Pipeline active</span>
                                        @endif

                                        <!-- Soft Delete Application -->
                                        <form action="{{ route('admin.application.delete', $application->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to move this application record to the system trash?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 text-xs font-bold rounded-lg text-red-500 bg-red-500/10 hover:bg-red-500 hover:text-white transition cursor-pointer">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-theme-text/50">No applications submitted yet in the pipeline.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $applications->links() }}
            </div>
        </div>

        <!-- Tab E: WhatsApp Settings -->
        <div x-show="activeTab === 'whatsapp'" class="space-y-8">
            <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3">
                <h3 class="text-lg font-bold">💬 WhatsApp Sourcing Notifications & API Gateway</h3>
                <span class="px-3 py-1 bg-green-500/10 text-green-500 border border-green-500/20 text-xs font-bold rounded-full uppercase">
                    API LIVE
                </span>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 bg-[var(--theme-bg)] p-6 rounded-xl border border-[var(--theme-border)]">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">API Provider Gateway *</label>
                        <select name="whatsapp_api_provider" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text">
                            <option value="wa.rj19web.com Official Gateway" {{ $whatsappProvider === 'wa.rj19web.com Official Gateway' ? 'selected' : '' }}>wa.rj19web.com Official API Gateway</option>
                            <option value="Meta Cloud API" {{ $whatsappProvider === 'Meta Cloud API' ? 'selected' : '' }}>Meta Cloud API (Official)</option>
                            <option value="Twilio WhatsApp Gateway" {{ $whatsappProvider === 'Twilio WhatsApp Gateway' ? 'selected' : '' }}>Twilio WhatsApp Business Gateway</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">WhatsApp API Send Message Endpoint URL *</label>
                        <input type="text" name="whatsapp_api_url" value="{{ old('whatsapp_api_url', $whatsappApiUrl) }}" placeholder="e.g. https://wa.rj19web.com/api/your-vendor-id/contact/send-message" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                </div>



                <div class="flex items-center gap-3">
                    <input type="checkbox" name="whatsapp_enabled" value="1" id="whatsapp_enabled" {{ $whatsappEnabled == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-red-500 focus:ring-red-500 bg-[var(--theme-bg)] border-[var(--theme-border)]" />
                    <label for="whatsapp_enabled" class="text-xs font-bold text-theme-text/75 uppercase tracking-wider cursor-pointer">Enable Automated WhatsApp Push Triggers on Sourcing & Placement events</label>
                </div>

                <div class="flex justify-end pt-4 border-t border-[var(--theme-border)]">
                    <button type="submit" class="px-6 py-3 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow transition transform active:scale-95 cursor-pointer">
                        Save WhatsApp API Settings
                    </button>
                </div>
            </form>

            <!-- WhatsApp Test Bench / Manual Sandbox Form -->
            <div class="p-6 rounded-2xl bg-gradient-to-br from-theme-primary/5 via-theme-accent/5 to-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm space-y-4">
                <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3">
                    <div>
                        <h4 class="text-sm font-black uppercase tracking-wider text-theme-text/80">🧪 WhatsApp API Test Bench (Manual Sandbox)</h4>
                        <p class="text-xs text-theme-text/60 mt-0.5">Fire custom templates and evaluate gateway delivery responses manually.</p>
                    </div>
                    <span class="px-2 py-0.5 bg-theme-primary/10 text-theme-primary text-[10px] font-black rounded-full uppercase">
                        Admin Sandbox
                    </span>
                </div>

                <form action="{{ route('admin.whatsapp.test-send') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Recipient Phone Number (e.g. 919999999999) *</label>
                            <input type="text" name="phone_number" value="{{ auth()->user()->phone }}" placeholder="e.g. 919876543210" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Select Template Preset to Test *</label>
                            <select x-model="selectedTemplate" name="template_name" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-bold">
                                <option value="fp_candidate_applied">fp_candidate_applied (Candidate Apply)</option>
                                <option value="fp_recruiter_sourced">fp_recruiter_sourced (Recruiter Source alert)</option>
                                <option value="fp_candidate_lined_up">fp_candidate_lined_up (Lined Up alert)</option>
                                <option value="fp_lineup_approved_candidate">fp_lineup_approved_candidate (Unlock notification for Candidate)</option>
                                <option value="fp_lineup_approved_recruiter">fp_lineup_approved_recruiter (Unlock details to Recruiter)</option>
                                <option value="fp_pipeline_stage_updated">fp_pipeline_stage_updated (Funnel Stage moved)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Preset Fields Generator based on selected template -->
                    <div class="bg-[var(--theme-bg)] p-4 rounded-xl border border-[var(--theme-border)] space-y-3">
                        <span class="block text-xs font-extrabold uppercase tracking-widest text-theme-primary">📋 Dynamic Template Variables</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="(label, index) in templateVars[selectedTemplate]" :key="index">
                                <div class="space-y-1">
                                    <label class="block text-[11px] font-black text-theme-text/75 uppercase" x-text="'Variable {{' + (index + 1) + '}} — ' + label"></label>
                                    <input type="text" :name="'field_' + (index + 1)" placeholder="Provide value..." class="w-full p-2.5 text-xs bg-[var(--theme-card)] border border-[var(--theme-border)] rounded-lg focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-theme-primary hover:bg-theme-accent rounded-xl shadow-md transition transform active:scale-95 cursor-pointer">
                            🚀 Dispatch Test Template Message
                        </button>
                    </div>
                </form>
            </div>

            <!-- Live Simulated WhatsApp Sent Messages Log Table -->
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h4 class="text-sm font-black uppercase tracking-wider text-theme-text/70">📲 Live Sent Messages Log (Recent Gateway Broadcasts)</h4>
                    <span class="px-2 py-0.5 bg-green-500/10 text-green-500 text-[10px] font-extrabold rounded-full">SIMULATED ACTIVE</span>
                </div>

                <div class="overflow-x-auto border border-[var(--theme-border)] rounded-xl">
                    <table class="w-full text-xs text-left text-theme-text">
                        <thead class="uppercase bg-[var(--theme-bg)] border-b border-[var(--theme-border)] text-theme-text/60 font-black">
                            <tr>
                                <th class="px-4 py-3">Recipient</th>
                                <th class="px-4 py-3">Phone</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Broadcast Message Details</th>
                                <th class="px-4 py-3">Gateway Status</th>
                                <th class="px-4 py-3">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--theme-border)] bg-[var(--theme-bg)]">
                            @forelse($whatsappSentLogs as $log)
                                <tr class="hover:bg-theme-primary/5 transition-colors">
                                    <td class="px-4 py-3 font-bold">{{ $log['recipient_name'] }}</td>
                                    <td class="px-4 py-3 text-theme-text/70 font-semibold">{{ $log['recipient_phone'] }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-1.5 py-0.5 text-[9px] font-black rounded-full uppercase {{ $log['recipient_role'] === 'candidate' ? 'bg-blue-500/10 text-blue-500' : 'bg-emerald-500/10 text-emerald-500' }}">
                                            {{ $log['recipient_role'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 max-w-xs space-y-1">
                                        <div class="font-extrabold text-theme-primary">{{ $log['title'] }}</div>
                                        <div class="text-theme-text/80 leading-relaxed">{{ $log['message'] }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusVal = $log['status'] ?? ($log['provider'] ?? '⚠️ Sent (Legacy)');
                                            $isSuccess = str_contains($statusVal, '✅') || str_contains($statusVal, 'Sent') || str_contains($statusVal, 'Success');
                                        @endphp
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $isSuccess ? 'bg-green-500/10 text-green-500' : 'bg-amber-500/10 text-amber-500' }}">
                                            {{ $statusVal }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-theme-text/50 font-medium">{{ $log['timestamp'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-theme-text/40 italic">
                                        No automated messages dispatched yet. Trigger placement pipeline events to broadcast WhatsApp logs.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab F: System Trash -->
        <div x-show="activeTab === 'trash'" class="space-y-8">
            <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3">
                <div>
                    <h3 class="text-lg font-bold">♻️ System Trash & Recover Center</h3>
                    <p class="text-xs text-theme-text/60 mt-0.5">Quickly restore soft-deleted users, faculty vacancies, and sourcing applications with a single click.</p>
                </div>
                <span class="px-3 py-1 bg-red-500/10 text-red-500 border border-red-500/20 text-xs font-black rounded-full uppercase">
                    TRASH BIN
                </span>
            </div>

            <!-- Deleted Users Sub-Card -->
            <div class="space-y-3">
                <h4 class="text-sm font-bold text-theme-text flex items-center gap-1.5">
                    👥 Deleted Users (Candidates / Recruiter Brand Accounts)
                </h4>
                <div class="overflow-x-auto border border-[var(--theme-border)] rounded-xl">
                    <table class="w-full text-xs text-left text-theme-text">
                        <thead class="uppercase bg-[var(--theme-bg)] border-b border-[var(--theme-border)] text-theme-text/60 font-black">
                            <tr>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Role</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Deleted At</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--theme-border)] bg-[var(--theme-bg)]">
                            @forelse($deletedUsers as $du)
                                <tr class="hover:bg-theme-primary/5 transition-colors">
                                    <td class="px-6 py-3 font-bold">{{ $du->name }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 text-[10px] font-black rounded-full uppercase {{ $du->role === 'candidate' ? 'bg-blue-500/10 text-blue-500' : 'bg-emerald-500/10 text-emerald-500' }}">
                                            {{ $du->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-theme-text/75">{{ $du->email }}</td>
                                    <td class="px-6 py-3 text-theme-text/50 font-semibold">{{ $du->deleted_at->toDayDateTimeString() }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <form action="{{ route('admin.user.restore', $du->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition active:scale-95 cursor-pointer">
                                                ♻️ Restore User
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-theme-text/40 italic bg-[var(--theme-card)]">No deleted users found in the trash.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Deleted Vacancies Sub-Card -->
            <div class="space-y-3 pt-4 border-t border-[var(--theme-border)]">
                <h4 class="text-sm font-bold text-theme-text flex items-center gap-1.5">
                    💼 Deleted Vacancies
                </h4>
                <div class="overflow-x-auto border border-[var(--theme-border)] rounded-xl">
                    <table class="w-full text-xs text-left text-theme-text">
                        <thead class="uppercase bg-[var(--theme-bg)] border-b border-[var(--theme-border)] text-theme-text/60 font-black">
                            <tr>
                                <th class="px-6 py-3">Vacancy Title</th>
                                <th class="px-6 py-3">Hiring Brand</th>
                                <th class="px-6 py-3">Subject</th>
                                <th class="px-6 py-3">Deleted At</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--theme-border)] bg-[var(--theme-bg)]">
                            @forelse($deletedVacancies as $dv)
                                <tr class="hover:bg-theme-primary/5 transition-colors">
                                    <td class="px-6 py-3 font-bold">{{ $dv->title }}</td>
                                    <td class="px-6 py-3 text-theme-primary font-semibold">{{ $dv->institution->company_name }}</td>
                                    <td class="px-6 py-3 text-theme-text/75">{{ $dv->subject }}</td>
                                    <td class="px-6 py-3 text-theme-text/50 font-semibold">{{ $dv->deleted_at->toDayDateTimeString() }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <form action="{{ route('admin.vacancy.restore', $dv->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition active:scale-95 cursor-pointer">
                                                ♻️ Restore Job
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-theme-text/40 italic bg-[var(--theme-card)]">No deleted vacancies found in the trash.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Deleted Applications Sub-Card -->
            <div class="space-y-3 pt-4 border-t border-[var(--theme-border)]">
                <h4 class="text-sm font-bold text-theme-text flex items-center gap-1.5">
                    📝 Deleted Pipeline Applications
                </h4>
                <div class="overflow-x-auto border border-[var(--theme-border)] rounded-xl">
                    <table class="w-full text-xs text-left text-theme-text">
                        <thead class="uppercase bg-[var(--theme-bg)] border-b border-[var(--theme-border)] text-theme-text/60 font-black">
                            <tr>
                                <th class="px-6 py-3">Candidate</th>
                                <th class="px-6 py-3">Hiring Board / Vacancy</th>
                                <th class="px-6 py-3">Original Status</th>
                                <th class="px-6 py-3">Deleted At</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--theme-border)] bg-[var(--theme-bg)]">
                            @forelse($deletedApplications as $da)
                                <tr class="hover:bg-theme-primary/5 transition-colors">
                                    <td class="px-6 py-3 font-bold">{{ $da->candidate->name }}</td>
                                    <td class="px-6 py-3 font-semibold text-theme-primary">{{ $da->vacancy->title }} under {{ $da->vacancy->institution->company_name }}</td>
                                    <td class="px-6 py-3 text-theme-text/75 uppercase font-medium">{{ $da->status }}</td>
                                    <td class="px-6 py-3 text-theme-text/50 font-semibold">{{ $da->deleted_at->toDayDateTimeString() }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <form action="{{ route('admin.application.restore', $da->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition active:scale-95 cursor-pointer">
                                                ♻️ Restore Application
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-6 text-center text-theme-text/40 italic bg-[var(--theme-card)]">No deleted applications found in the trash.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ================================================================
     | ALPINE MODALS OVERLAY PORTALS
     | ================================================================ -->

    <!-- Modal 1: Add User -->
    <div x-show="showAddUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition x-cloak>
        <div @click.away="showAddUserModal = false" class="relative w-full max-w-lg p-6 bg-[var(--theme-card)] border border-[var(--theme-border)] rounded-2xl shadow-2xl space-y-4 text-theme-text">
            <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3">
                <h4 class="text-base font-extrabold flex items-center gap-1.5">👤 Create Platform Account</h4>
                <button @click="showAddUserModal = false" class="text-theme-text/50 hover:text-theme-text text-lg cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('admin.user.store') }}" method="POST" x-data="{ selectedRole: 'candidate' }" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2">Account Name *</label>
                    <input type="text" name="name" placeholder="Full name of user" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Email ID *</label>
                        <input type="email" name="email" placeholder="e.g. user@gmail.com" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Phone Number *</label>
                        <input type="text" name="phone" placeholder="10 digit phone number" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Account Role *</label>
                        <select name="role" x-model="selectedRole" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-bold">
                            <option value="candidate">🧑‍🏫 Faculty Candidate</option>
                            <option value="institution">🏢 Sourcing Recruiter</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Account Password *</label>
                        <input type="password" name="password" placeholder="Min. 6 characters" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                </div>

                <!-- Recruiter specific branding name field -->
                <div x-show="selectedRole === 'institution'" x-transition>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2">Hiring Institution / Company Name *</label>
                    <input type="text" name="company_name" placeholder="e.g. Unacademy, PhysicsWallah" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold" :required="selectedRole === 'institution'" />
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-[var(--theme-border)]">
                    <button type="button" @click="showAddUserModal = false" class="px-4 py-2.5 text-xs font-bold rounded-xl border border-[var(--theme-border)] hover:bg-theme-primary/10 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md transition cursor-pointer">
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Edit User -->
    <div x-show="showEditUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition x-cloak>
        <div @click.away="showEditUserModal = false" class="relative w-full max-w-2xl p-6 bg-[var(--theme-card)] border border-[var(--theme-border)] rounded-2xl shadow-2xl space-y-4 text-theme-text max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3">
                <h4 class="text-base font-extrabold flex items-center gap-1.5">👤 Edit User Account</h4>
                <button @click="showEditUserModal = false" class="text-theme-text/50 hover:text-theme-text text-lg cursor-pointer">&times;</button>
            </div>

            <form :action="'{{ url('/admin/user') }}/' + editUser.id + '/update'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2">Account Name *</label>
                    <input type="text" name="name" x-model="editUser.name" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Email ID *</label>
                        <input type="email" name="email" x-model="editUser.email" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Phone Number *</label>
                        <input type="text" name="phone" x-model="editUser.phone" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                </div>

                <!-- Candidate fields -->
                <div x-show="editUser.role === 'candidate'" class="space-y-4 pt-4 border-t border-[var(--theme-border)]" x-transition>
                    <h5 class="text-xs font-black text-red-500 uppercase tracking-wider">🎓 Faculty Profile Information</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">Category Type *</label>
                            <select name="candidate_type" x-model="editUser.candidate_type" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text">
                                <option value="academic">Academic</option>
                                <option value="coaching">Coaching</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">Current Designation</label>
                            <input type="text" name="current_designation" x-model="editUser.current_designation" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">Highest Degree</label>
                            <input type="text" name="highest_degree" x-model="editUser.highest_degree" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">Experience (Years)</label>
                            <input type="number" step="0.1" name="experience_years" x-model="editUser.experience_years" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Subjects Taught (comma separated)</label>
                        <input type="text" name="subjects_taught" x-model="editUser.subjects_taught" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">Current CTC</label>
                            <input type="number" name="current_ctc" x-model="editUser.current_ctc" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">Expected CTC</label>
                            <input type="number" name="expected_ctc" x-model="editUser.expected_ctc" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">Preferred Location</label>
                            <input type="text" name="preferred_location" x-model="editUser.preferred_location" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">Google Scholar Profile URL</label>
                            <input type="url" name="google_scholar_url" x-model="editUser.google_scholar_url" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">H-Index</label>
                            <input type="number" name="h_index" x-model="editUser.h_index" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Demo Video URL</label>
                        <input type="url" name="demo_video_url" x-model="editUser.demo_video_url" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Brief Summary</label>
                        <textarea name="experience_summary" x-model="editUser.experience_summary" rows="2" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text"></textarea>
                    </div>
                </div>

                <!-- Recruiter/Institution fields -->
                <div x-show="editUser.role === 'institution'" class="space-y-4 pt-4 border-t border-[var(--theme-border)]" x-transition>
                    <h5 class="text-xs font-black text-red-500 uppercase tracking-wider">🏢 Institution Profile Information</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">Hiring Institution / Company Name *</label>
                            <input type="text" name="company_name" x-model="editUser.company_name" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text font-semibold" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">Institution Type *</label>
                            <select name="institution_type" x-model="editUser.institution_type" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text">
                                <option value="coaching_center">Coaching Center</option>
                                <option value="school">School / Academy</option>
                                <option value="university">University</option>
                                <option value="other">Other Educational Org</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">Location</label>
                            <input type="text" name="location" x-model="editUser.location" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2">Official Website</label>
                            <input type="url" name="website" x-model="editUser.website" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Description</label>
                        <textarea name="description" x-model="editUser.description" rows="2" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-theme-text"></textarea>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-[var(--theme-bg)] p-3 rounded-xl border border-[var(--theme-border)]">
                    <input type="checkbox" name="is_verified" value="1" id="edit_is_verified" x-model="editUser.is_verified" class="w-4 h-4 rounded text-red-500 focus:ring-red-500 bg-[var(--theme-card)] border-[var(--theme-border)]" />
                    <label for="edit_is_verified" class="text-xs font-bold uppercase tracking-wider cursor-pointer">Verify badge active</label>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-[var(--theme-border)]">
                    <button type="button" @click="showEditUserModal = false" class="px-4 py-2.5 text-xs font-bold rounded-xl border border-[var(--theme-border)] hover:bg-theme-primary/10 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md transition cursor-pointer">
                        Update Details
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Add Vacancy -->
    <div x-show="showAddVacancyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition x-cloak>
        <div @click.away="showAddVacancyModal = false" class="relative w-full max-w-2xl p-6 bg-[var(--theme-card)] border border-[var(--theme-border)] rounded-2xl shadow-2xl space-y-4 text-theme-text max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3">
                <h4 class="text-base font-extrabold flex items-center gap-1.5">💼 Post Dynamic Job Vacancy</h4>
                <button @click="showAddVacancyModal = false" class="text-theme-text/50 hover:text-theme-text text-lg cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('admin.vacancy.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Hiring Institution / Partner Brand *</label>
                        <select name="institution_id" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-bold" required>
                            <option value="">-- Choose Brand --</option>
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->company_name }} (UserID: {{ $inst->user_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Vacancy Title *</label>
                        <input type="text" name="title" placeholder="e.g. Senior IIT-JEE Physics Faculty" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold" required />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2">Job Description *</label>
                    <textarea name="description" rows="3" placeholder="Provide candidate eligibility criteria and role requirements..." class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Subject / Specialization *</label>
                        <input type="text" name="subject" placeholder="e.g. Physics, Chemistry, Maths" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Target Grade / Class *</label>
                        <input type="text" name="target_class" placeholder="e.g. Class 11-12, IIT-JEE" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Experience Required *</label>
                        <input type="text" name="experience_required" placeholder="e.g. 3+ Years" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Job Location *</label>
                        <input type="text" name="location" placeholder="e.g. Kota, Rajasthan" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Salary Min (Per Annum) *</label>
                        <input type="number" name="salary_min" placeholder="e.g. 600000" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Salary Max (Per Annum) *</label>
                        <input type="number" name="salary_max" placeholder="e.g. 1500000" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2">Application Deadline *</label>
                    <input type="date" name="deadline" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold" required />
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-[var(--theme-border)]">
                    <button type="button" @click="showAddVacancyModal = false" class="px-4 py-2.5 text-xs font-bold rounded-xl border border-[var(--theme-border)] hover:bg-theme-primary/10 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md transition cursor-pointer">
                        Publish Vacancy
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 4: Edit Vacancy -->
    <div x-show="showEditVacancyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition x-cloak>
        <div @click.away="showEditVacancyModal = false" class="relative w-full max-w-2xl p-6 bg-[var(--theme-card)] border border-[var(--theme-border)] rounded-2xl shadow-2xl space-y-4 text-theme-text max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3">
                <h4 class="text-base font-extrabold flex items-center gap-1.5">💼 Edit Job Board details</h4>
                <button @click="showEditVacancyModal = false" class="text-theme-text/50 hover:text-theme-text text-lg cursor-pointer">&times;</button>
            </div>

            <form :action="'{{ url('/admin/vacancy') }}/' + editVacancy.id + '/update'" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Hiring Institution / Partner Brand *</label>
                        <select name="institution_id" x-model="editVacancy.institution_id" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-bold" required>
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->company_name }} (UserID: {{ $inst->user_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Vacancy Title *</label>
                        <input type="text" name="title" x-model="editVacancy.title" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold" required />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2">Job Description *</label>
                    <textarea name="description" x-model="editVacancy.description" rows="3" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Subject / Specialization *</label>
                        <input type="text" name="subject" x-model="editVacancy.subject" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Target Grade / Class *</label>
                        <input type="text" name="target_class" x-model="editVacancy.target_class" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Experience Required *</label>
                        <input type="text" name="experience_required" x-model="editVacancy.experience_required" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Job Location *</label>
                        <input type="text" name="location" x-model="editVacancy.location" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Salary Min (Per Annum) *</label>
                        <input type="number" name="salary_min" x-model="editVacancy.salary_min" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-2">Salary Max (Per Annum) *</label>
                        <input type="number" name="salary_max" x-model="editVacancy.salary_max" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" required />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2">Application Deadline *</label>
                    <input type="date" name="deadline" x-model="editVacancy.deadline" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text font-semibold" required />
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-[var(--theme-border)]">
                    <button type="button" @click="showEditVacancyModal = false" class="px-4 py-2.5 text-xs font-bold rounded-xl border border-[var(--theme-border)] hover:bg-theme-primary/10 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md transition cursor-pointer">
                        Update Board
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
