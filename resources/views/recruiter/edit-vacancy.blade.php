@extends('layouts.app')

@section('title', 'Edit Faculty Vacancy')

@section('content')
<div class="max-w-3xl mx-auto my-4 sm:my-8 bg-[var(--theme-card)] rounded-2xl p-4 sm:p-8 shadow-xl border border-[var(--theme-border)] transition-all duration-300">
    <div class="flex items-center justify-between border-b border-[var(--theme-border)] pb-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-theme-text">💼 Edit Faculty Vacancy</h1>
            <p class="text-xs text-theme-text/60 mt-1">Modify sourcing parameters and requirements for candidates.</p>
        </div>
        <a href="{{ route('recruiter.dashboard', ['tab' => 'my-vacancies']) }}" class="px-4 py-2 border border-[var(--theme-border)] text-theme-text/70 hover:bg-theme-primary/10 rounded-xl text-xs font-bold transition">
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

    <form action="{{ route('recruiter.vacancy.update', $vacancy->id) }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Vacancy Position Title *</label>
                <input type="text" name="title" value="{{ old('title', $vacancy->title) }}" placeholder="e.g. Senior HOD Physics Teacher" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text font-semibold" required />
            </div>

            <div>
                <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Core Subject Discipline *</label>
                <input type="text" name="subject" value="{{ old('subject', $vacancy->subject) }}" placeholder="e.g. Physics, Botany" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Target Student Class *</label>
                <input type="text" name="target_class" value="{{ old('target_class', $vacancy->target_class) }}" placeholder="e.g. IIT-JEE Advanced, NEET UG" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
            </div>

            <div>
                <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Experience Required *</label>
                <input type="text" name="experience_required" value="{{ old('experience_required', $vacancy->experience_required) }}" placeholder="e.g. 5+ Years Allen/Resonance" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
            </div>

            <div>
                <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Job Location *</label>
                <input type="text" name="location" value="{{ old('location', $vacancy->location) }}" placeholder="e.g. Kota, Rajasthan" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Salary Min (Annual CTC in INR) *</label>
                <input type="number" name="salary_min" value="{{ old('salary_min', (int)$vacancy->salary_min) }}" placeholder="e.g. 1200000" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
            </div>

            <div>
                <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Salary Max (Annual CTC in INR) *</label>
                <input type="number" name="salary_max" value="{{ old('salary_max', (int)$vacancy->salary_max) }}" placeholder="e.g. 1800000" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required />
            </div>

            <div>
                <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Application Deadline *</label>
                <input type="date" name="deadline" value="{{ old('deadline', \Carbon\Carbon::parse($vacancy->deadline)->format('Y-m-d')) }}" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text font-semibold" required />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Status *</label>
                <select name="status" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text font-bold" required>
                    <option value="open" {{ old('status', $vacancy->status) === 'open' ? 'selected' : '' }}>🟢 Open for Applications</option>
                    <option value="closed" {{ old('status', $vacancy->status) === 'closed' ? 'selected' : '' }}>🔴 Closed</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-theme-text/75 uppercase tracking-wider mb-2">Deep Description & Expectations *</label>
            <textarea name="description" rows="6" placeholder="List syllabus depth, weekly teaching hours, board classroom work expectations..." class="w-full p-4 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" required>{{ old('description', $vacancy->description) }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-[var(--theme-border)]">
            <a href="{{ route('recruiter.dashboard', ['tab' => 'my-vacancies']) }}" class="px-6 py-3 text-sm font-bold border border-[var(--theme-border)] hover:bg-theme-primary/10 rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 text-sm font-bold text-white bg-theme-primary hover:bg-theme-accent rounded-xl shadow-lg transition transform active:scale-95 cursor-pointer">
                Save & Update Vacancy
            </button>
        </div>
    </form>
</div>
@endsection
