@extends('layouts.app')

@section('title', 'Reference Submitted Successfully')

@section('content')
<div class="max-w-md mx-auto my-12 text-center bg-[var(--theme-card)] rounded-2xl p-8 shadow-xl border border-[var(--theme-border)] transition-colors">
    
    <div class="inline-flex p-4 bg-green-500/10 text-green-600 rounded-full mb-6">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
    </div>

    <h1 class="text-2xl font-extrabold text-theme-text mb-2">
        Thank You!
    </h1>
    <p class="text-sm text-theme-text/70 mb-6">
        The confidential evaluation has been securely submitted for <strong class="text-theme-text font-bold">{{ $letter->application->candidate->name }}</strong>. 
    </p>

    <div class="p-4 rounded-xl bg-[var(--theme-bg)] border border-[var(--theme-border)] text-xs text-theme-text/60 text-left space-y-2">
        <p><strong>Position:</strong> {{ $letter->application->vacancy->title }}</p>
        <p><strong>Institution:</strong> {{ $letter->application->vacancy->institution->company_name }}</p>
        <p><strong>Submitted At:</strong> {{ $letter->submitted_at?->format('F d, Y h:i A') ?? now()->format('F d, Y h:i A') }}</p>
    </div>

    <p class="text-[10px] text-theme-text/40 mt-8">
        This link has been deactivated. You may now close this tab.
    </p>

</div>
@endsection
