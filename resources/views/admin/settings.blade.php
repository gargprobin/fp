@extends('layouts.app')

@section('title', 'SaaS Control, Branding & Messaging Center')

@section('content')
<div x-data="{ activeTab: 'settings' }" class="space-y-8">
    
    <!-- Header Banner -->
    <div class="p-8 rounded-2xl bg-gradient-to-br from-red-500/10 via-theme-accent/5 to-[var(--theme-card)] border border-[var(--theme-border)] shadow-sm relative overflow-hidden transition-all duration-300">
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight mb-1 flex items-center gap-2">
                    ⚙️ SaaS Control, Branding & Messaging Center
                </h1>
                <p class="text-sm text-theme-text/75">
                    Manage global dynamic settings, configure WhatsApp API integrations, moderate active vacancies, track applications, and verify candidate/partner credentials.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.clear-cache') }}" method="POST" onsubmit="return confirm('Bhai, kya aap sachme pure system ka cache clear karna chahte hain?')">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-bold text-xs rounded-xl shadow-sm transition-all duration-200 flex items-center gap-1.5 border border-amber-600/20">
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

    <!-- 📊 SaaS Platform Live Statistics Grid -->
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
                <span class="text-xs text-theme-text/50">SaaS Tenants</span>
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
    <div class="flex flex-wrap border-b border-[var(--theme-border)] gap-6 text-sm font-semibold">
        <button @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'text-red-500 border-b-2 border-red-500 pb-3' : 'text-theme-text/60 pb-3 hover:text-theme-text transition'">
            🔧 SaaS Settings
        </button>
        <button @click="activeTab = 'whatsapp'" :class="activeTab === 'whatsapp' ? 'text-red-500 border-b-2 border-red-500 pb-3' : 'text-theme-text/60 pb-3 hover:text-theme-text transition'">
            💬 WhatsApp Settings
        </button>
        <button @click="activeTab = 'users'" :class="activeTab === 'users' ? 'text-red-500 border-b-2 border-red-500 pb-3' : 'text-theme-text/60 pb-3 hover:text-theme-text transition'">
            👥 User Moderation
        </button>
        <button @click="activeTab = 'vacancies'" :class="activeTab === 'vacancies' ? 'text-red-500 border-b-2 border-red-500 pb-3' : 'text-theme-text/60 pb-3 hover:text-theme-text transition'">
            💼 Vacancies
        </button>
        <button @click="activeTab = 'applications'" :class="activeTab === 'applications' ? 'text-red-500 border-b-2 border-red-500 pb-3' : 'text-theme-text/60 pb-3 hover:text-theme-text transition'">
            📝 Sourcing Pipeline
        </button>
    </div>

    <!-- Tab Contents -->
    <div class="bg-[var(--theme-card)] rounded-2xl p-8 border border-[var(--theme-border)] shadow-sm">
        
        <!-- Tab A: SaaS Settings -->
        <div x-show="activeTab === 'settings'" class="space-y-6">
            <h3 class="text-lg font-bold border-b border-[var(--theme-border)] pb-3">Update Global Portal Info</h3>
            
            <form action="/admin/settings" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">SaaS Brand Name *</label>
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
                    <button type="submit" class="px-6 py-3 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow transition transform active:scale-95">
                        Save Global SaaS Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab B: User Moderation -->
        <div x-show="activeTab === 'users'" class="space-y-4">
            <h3 class="text-lg font-bold border-b border-[var(--theme-border)] pb-3">Moderate Active Platform Users</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-theme-text">
                    <thead class="text-xs uppercase bg-[var(--theme-bg)] border border-[var(--theme-border)] text-theme-text/60">
                        <tr>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Verification</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--theme-border)]">
                        @foreach($users as $user)
                            <tr class="hover:bg-[var(--theme-bg)] transition-colors">
                                <td class="px-6 py-4 font-bold">{{ $user->name }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $user->role === 'candidate' ? 'bg-blue-500/10 text-blue-500' : ($user->role === 'institution' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-theme-text/70">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $user->is_verified ? 'text-green-500' : 'text-amber-500' }}">
                                        {{ $user->is_verified ? '🟢 Verified Badge' : '🟡 Unverified' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="/admin/user/{{ $user->id }}/toggle-verification" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg)] hover:bg-theme-primary/10 transition">
                                            {{ $user->is_verified ? 'Revoke Verification' : 'Verify Account' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab C: Vacancies Moderation -->
        <div x-show="activeTab === 'vacancies'" class="space-y-4">
            <h3 class="text-lg font-bold border-b border-[var(--theme-border)] pb-3">Moderate Faculty Vacancies</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-theme-text">
                    <thead class="text-xs uppercase bg-[var(--theme-bg)] border border-[var(--theme-border)] text-theme-text/60">
                        <tr>
                            <th class="px-6 py-4">Vacancy Title</th>
                            <th class="px-6 py-4">Hiring Brand</th>
                            <th class="px-6 py-4">Subject</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--theme-border)]">
                        @foreach($vacancies as $vacancy)
                            <tr class="hover:bg-[var(--theme-bg)] transition-colors">
                                <td class="px-6 py-4 font-bold">{{ $vacancy->title }}</td>
                                <td class="px-6 py-4 text-theme-primary font-semibold">{{ $vacancy->institution->company_name }}</td>
                                <td class="px-6 py-4 text-theme-text/75">{{ $vacancy->subject }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $vacancy->status === 'open' ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500' }}">
                                        {{ ucfirst($vacancy->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    
                                    <!-- Toggle Status -->
                                    <form action="/admin/vacancy/{{ $vacancy->id }}/toggle-status" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-[var(--theme-border)] bg-[var(--theme-bg)] hover:bg-theme-primary/10 transition">
                                            {{ $vacancy->status === 'open' ? 'Close Board' : 'Open Board' }}
                                        </button>
                                    </form>

                                    <!-- Delete Vacancy -->
                                    <form action="/admin/vacancy/{{ $vacancy->id }}" method="POST" class="inline" onsubmit="return confirm('Are you absolutely sure you want to delete this job vacancy? This action is permanent.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-lg text-white bg-red-600 hover:bg-red-700 transition">
                                            Delete
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab D: Active Applications -->
        <div x-show="activeTab === 'applications'" class="space-y-4">
            <h3 class="text-lg font-bold border-b border-[var(--theme-border)] pb-3">Monitor & Moderate Faculty Sourcing Pipeline</h3>
            <p class="text-xs text-theme-text/60 leading-normal">
                💼 <strong>Placement CRM Sourcing Flow:</strong> Sourcing is moderated via Admin approval loops. Shortlist applicants to forward masked CVs, or Approve unlock requests to reveal contact details!
            </p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-theme-text">
                    <thead class="text-xs uppercase bg-[var(--theme-bg)] border border-[var(--theme-border)] text-theme-text/60">
                        <tr>
                            <th class="px-6 py-4">Candidate Profile</th>
                            <th class="px-6 py-4">Applied Vacancy</th>
                            <th class="px-6 py-4">Recruiter / Client</th>
                            <th class="px-6 py-4">Pipeline Status</th>
                            <th class="px-6 py-4 text-right">Moderator Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--theme-border)]">
                        @forelse($applications as $application)
                            <tr class="hover:bg-[var(--theme-bg)] transition-colors">
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
                                    @if($application->status === 'applied')
                                        <form action="{{ route('admin.application.shortlist', $application->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3.5 py-1.5 text-xs font-extrabold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition">
                                                Shortlist Candidate
                                            </button>
                                        </form>
                                    @elseif($application->status === 'shortlisted_by_institution')
                                        <form action="{{ route('admin.application.unlock-approve', $application->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3.5 py-1.5 text-xs font-extrabold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm transition animate-bounce">
                                                Approve Line Up & Exchange
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-theme-text/40 font-semibold italic">Pipeline managed by Recruiter</span>
                                    @endif
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
        </div>

        <!-- Tab E: WhatsApp Settings -->
        <div x-show="activeTab === 'whatsapp'" class="space-y-6">
            <div class="flex justify-between items-center border-b border-[var(--theme-border)] pb-3">
                <h3 class="text-lg font-bold">💬 WhatsApp Sourcing Notifications & API Gateway</h3>
                <span class="px-3 py-1 bg-green-500/10 text-green-500 border border-green-500/20 text-xs font-bold rounded-full uppercase">
                    API LIVE
                </span>
            </div>

            <form action="/admin/settings" method="POST" class="space-y-6 bg-[var(--theme-bg)] p-6 rounded-xl border border-[var(--theme-border)]">
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

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-1">API Access Token (Optional if already embedded in the URL)</label>
                    <input type="password" name="whatsapp_api_key" value="{{ old('whatsapp_api_key', $whatsappApiKey) }}" placeholder="e.g. jUDMUIGKNETSvQ4CaAjYbyRJS9YNAMXs..." class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 text-theme-text" />
                    <p class="text-[10px] text-theme-text/50 leading-relaxed font-semibold italic">
                        💡 <strong>Flexible Config:</strong> URL only (with token=... in query parameter) OR separate URL and Token parameter. Our backend dynamically supports both and auto-broadcasts!
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="whatsapp_enabled" value="1" id="whatsapp_enabled" {{ $whatsappEnabled == '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-red-500 focus:ring-red-500 bg-[var(--theme-bg)] border-[var(--theme-border)]" />
                    <label for="whatsapp_enabled" class="text-xs font-bold text-theme-text/75 uppercase tracking-wider cursor-pointer">Enable Automated WhatsApp Push Triggers on Sourcing & Placement events</label>
                </div>

                <div class="flex justify-end pt-4 border-t border-[var(--theme-border)]">
                    <button type="submit" class="px-6 py-3 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow transition transform active:scale-95">
                        Save WhatsApp API Settings
                    </button>
                </div>
            </form>

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
                                <th class="px-4 py-3">Gateway</th>
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
                                    <td class="px-4 py-3 text-theme-text/60">{{ $log['status'] ?? $log['provider'] ?? '—' }}</td>
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

    </div>

</div>
@endsection
