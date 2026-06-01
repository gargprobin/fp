@extends('layouts.app')

@section('title', 'Submit Reference Letter - ' . ($siteSettings->platform_name ?? 'Sourcing Portal'))

@section('content')
<div class="max-w-2xl mx-auto my-8 bg-[var(--theme-card)] rounded-2xl p-8 shadow-xl border border-[var(--theme-border)] transition-colors">
    
    <!-- Branding Accent -->
    <div class="h-2 rounded-t-2xl -mt-8 -mx-8 mb-6" style="background-color: {{ $letter->application->vacancy->institution->primary_color }}"></div>

    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-theme-text mb-2">
            Confidential Academic Evaluation
        </h1>
        <p class="text-sm text-theme-text/75 leading-relaxed">
            Dear <strong class="text-theme-text">{{ $letter->referee_name }}</strong>, you have been requested to provide a confidential reference letter for 
            <strong class="text-theme-text">{{ $letter->application->candidate->name }}</strong> who has applied for the position of 
            <strong class="text-theme-primary">{{ $letter->application->vacancy->title }}</strong> at 
            <strong class="text-theme-text">{{ $letter->application->vacancy->institution->company_name }}</strong>.
        </p>
    </div>

    <!-- Confidentiality Warning Alert -->
    <div class="p-4 rounded-xl bg-theme-accent/5 border border-theme-accent/15 text-xs text-theme-text/80 space-y-1 mb-6 flex gap-3 items-start">
        <div class="p-1 bg-theme-accent/10 rounded text-theme-accent mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0-6h.01M20.24 18.12A10 10 0 111.76 5.88 10 10 0 0120.24 18.12z"></path></svg>
        </div>
        <div>
            <span class="block font-bold">🔒 Strict Confidentiality Policy</span>
            <p>
                This reference letter is completely confidential. The candidate ({{ $letter->application->candidate->name }}) has waived their right to access this feedback, and it will be visible ONLY to the search committee and authorized recruiters.
            </p>
        </div>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 rounded-lg bg-red-500/10 text-red-500 border border-red-500/20 text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <!-- Form Submit -->
    <!-- We append the current URL query parameters to maintain the secure cryptographical signature on POST -->
    <form action="{{ request()->fullUrl() }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <div>
            <label class="block text-sm font-bold text-theme-text/70 mb-2">Confidential Written Evaluation *</label>
            <textarea name="comments" rows="6" class="w-full p-4 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text @error('comments') border-red-500 @enderror" placeholder="Provide a summary of the candidate's academic capabilities, teaching expertise, research acumen, and character..." required>{{ old('comments') }}</textarea>
            @error('comments')
                <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-theme-text/70 mb-2">Upload Signed Reference Document (Optional, PDF format)</label>
            <input type="file" name="recommendation_file" class="w-full p-3 bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text/80 @error('recommendation_file') border-red-500 @enderror" />
            <p class="text-[10px] text-theme-text/50 mt-1">Accepted format: PDF only. Max size: 5MB.</p>
            @error('recommendation_file')
                <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full py-4 text-white font-extrabold rounded-xl shadow-md transition transform active:scale-[0.98] text-sm" style="background-color: {{ $letter->application->vacancy->institution->primary_color }}">
            Submit Confidential Reference Securely &rarr;
        </button>
    </form>

</div>
@endsection
