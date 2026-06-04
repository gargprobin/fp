@extends('layouts.app')

@section('title', 'Edit My Faculty Profile')

@section('content')
<div class="max-w-3xl mx-auto my-4 sm:my-8 bg-[var(--theme-card)] rounded-2xl p-4 sm:p-8 shadow-xl border border-[var(--theme-border)] transition-all duration-300">
    <div class="flex items-center justify-between border-b border-[var(--theme-border)] pb-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-theme-text">🧑‍🏫 Edit Profile Details</h1>
            <p class="text-xs text-theme-text/60 mt-1">Keep your teaching credentials and preferences up-to-date.</p>
        </div>
        <a href="{{ route('faculty.dashboard') }}" class="px-4 py-2 border border-[var(--theme-border)] text-theme-text/70 hover:bg-theme-primary/10 rounded-xl text-xs font-bold transition">
            &larr; Back to Dashboard
        </a>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 text-red-500 border border-red-500/20 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('faculty.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Section 1: Basic Information -->
        <h3 class="text-sm font-black text-theme-primary uppercase tracking-wider">👤 Basic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text font-semibold" />
            </div>
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Contact Number *</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" />
            </div>
        </div>

        <div class="pt-4 border-t border-[var(--theme-border)]"></div>

        <!-- Section 2: Teaching Credentials -->
        <h3 class="text-sm font-black text-theme-primary uppercase tracking-wider">🎓 Academic & Experience Details</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Profile Vetting Category *</label>
                <select name="type" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text font-bold">
                    <option value="academic" {{ old('type', $profile?->type) === 'academic' ? 'selected' : '' }}>🏫 Academic (Schools/Universities)</option>
                    <option value="coaching" {{ old('type', $profile?->type) === 'coaching' ? 'selected' : '' }}>🏢 Test prep / Coaching Institute</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Current Designation</label>
                <input type="text" name="current_designation" value="{{ old('current_designation', $profile?->current_designation) }}" placeholder="e.g. HOD Physics / Senior Faculty" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Highest Degree *</label>
                <input type="text" name="highest_degree" value="{{ old('highest_degree', $profile?->highest_degree ?? 'Not Specified') }}" required placeholder="e.g. M.Tech from IIT Bombay" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text font-semibold" />
            </div>
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Years of Experience *</label>
                <input type="number" step="0.1" name="experience_years" value="{{ old('experience_years', $profile?->experience_years ?? 0.0) }}" required placeholder="e.g. 5.5" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" />
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Subjects Taught (Comma Separated) *</label>
            <input type="text" name="subjects_taught" value="{{ old('subjects_taught', $profile ? (is_array($profile->subjects_taught) ? implode(', ', $profile->subjects_taught) : $profile->subjects_taught) : 'General') }}" required placeholder="e.g. Physics, Mechanics, Chemistry" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text font-semibold" />
        </div>

        <div class="pt-4 border-t border-[var(--theme-border)]"></div>

        <!-- Section 3: Salary Preferences & Location -->
        <h3 class="text-sm font-black text-theme-primary uppercase tracking-wider">💼 CTC Preferences & Locations</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Current CTC (INR / year)</label>
                <input type="number" name="current_ctc" value="{{ old('current_ctc', $profile?->current_ctc ? (int) $profile->current_ctc : '') }}" placeholder="e.g. 1500000" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" />
            </div>
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Expected CTC (INR / year)</label>
                <input type="number" name="expected_ctc" value="{{ old('expected_ctc', $profile?->expected_ctc ? (int) $profile->expected_ctc : '') }}" placeholder="e.g. 2000000" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" />
            </div>
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Preferred Location</label>
                <input type="text" name="preferred_location" value="{{ old('preferred_location', $profile?->preferred_location) }}" placeholder="e.g. Kota or Bangalore" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" />
            </div>
        </div>

        <div class="pt-4 border-t border-[var(--theme-border)]"></div>

        <!-- Section 4: Whiteboard Video & Academic Citations -->
        <h3 class="text-sm font-black text-theme-primary uppercase tracking-wider">🔬 Video Demos & Research</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Demo Whiteboard Video URL</label>
                <input type="url" name="demo_video_url" value="{{ old('demo_video_url', $profile?->demo_video_url) }}" placeholder="e.g. https://youtube.com/watch?v=xyz" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" />
            </div>
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Google Scholar H-Index</label>
                <input type="number" name="h_index" value="{{ old('h_index', $profile?->h_index) }}" placeholder="e.g. 5" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" />
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Google Scholar Profile URL</label>
            <input type="url" name="google_scholar_url" value="{{ old('google_scholar_url', $profile?->google_scholar_url) }}" placeholder="e.g. https://scholar.google.com/citations?user=xyz" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" />
        </div>

        <div>
            <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Brief Profile Summary</label>
            <textarea name="experience_summary" rows="4" placeholder="Brief details about your target student scores, coaching methodologies, or classroom dynamics..." class="w-full p-4 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text">{{ old('experience_summary', $profile?->experience_summary) }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Detailed CV File (PDF, DOC, DOCX - Max 5MB)</label>
            <input type="file" name="cv_file" accept=".pdf,.doc,.docx" class="w-full p-2.5 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-theme-primary/10 file:text-theme-primary hover:file:bg-theme-primary/20" />
            @if($profile && $profile->cv_path)
                <div class="mt-2 text-xs">
                    <span class="text-theme-text/60">📄 Current CV: </span>
                    <a href="{{ route('cv.view', ['filename' => basename($profile->cv_path)]) }}" class="text-theme-primary font-bold hover:underline">
                        View Uploaded CV File
                    </a>
                </div>
            @endif
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-3 pt-6 border-t border-[var(--theme-border)]">
            <a href="{{ route('faculty.dashboard') }}" class="px-6 py-3 text-sm font-bold border border-[var(--theme-border)] hover:bg-theme-primary/10 rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 text-sm font-bold text-white bg-theme-primary hover:bg-theme-accent rounded-xl shadow-lg transition transform active:scale-95 cursor-pointer">
                Save & Update Profile
            </button>
        </div>
    </form>
</div>
@endsection
