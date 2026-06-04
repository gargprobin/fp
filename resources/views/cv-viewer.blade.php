@extends('layouts.app')

@section('title', 'View Candidate CV')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between bg-[var(--theme-card)] p-4 rounded-xl border border-[var(--theme-border)] shadow-sm">
        <div class="flex items-center gap-3">
            <button onclick="history.back()" class="flex items-center gap-2 text-theme-text/70 hover:text-theme-primary transition px-3 py-1.5 rounded-lg hover:bg-[var(--theme-bg)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                <span class="font-bold">Back</span>
            </button>
            <h1 class="text-xl font-black text-theme-primary">📄 Candidate Resume / CV</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('cv.raw', ['filename' => $filename]) }}" download class="flex items-center gap-2 text-sm font-bold bg-[var(--theme-bg)] hover:bg-theme-primary hover:text-white text-theme-text px-4 py-2 rounded-lg transition border border-[var(--theme-border)]">
                ⬇️ Download CV
            </a>
            <a href="{{ route('cv.raw', ['filename' => $filename]) }}" target="_blank" class="flex items-center gap-2 text-sm font-bold bg-theme-primary text-white px-4 py-2 rounded-lg hover:brightness-110 transition shadow-sm">
                ↗️ Open in New Tab
            </a>
        </div>
    </div>

    <div class="bg-[var(--theme-card)] rounded-xl border border-[var(--theme-border)] shadow-sm overflow-hidden" style="height: 80vh;">
        @php
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        @endphp

        @if(in_array($ext, ['doc', 'docx']))
            <div class="flex flex-col items-center justify-center h-full p-8 text-center space-y-4">
                <div class="text-6xl">📝</div>
                <h2 class="text-2xl font-black text-theme-text">Word Document format</h2>
                <p class="text-theme-text/70 max-w-md">
                    This CV is in MS Word format ({{ strtoupper($ext) }}) which cannot be previewed securely inline in the browser.
                </p>
                <a href="{{ route('cv.raw', ['filename' => $filename]) }}" download class="bg-theme-primary text-white font-bold px-6 py-3 rounded-xl hover:brightness-110 transition shadow-lg inline-flex items-center gap-2 mt-4">
                    ⬇️ Download Document to View
                </a>
            </div>
        @else
            <iframe src="{{ route('cv.raw', ['filename' => $filename]) }}" class="w-full h-full border-0" title="CV Viewer"></iframe>
        @endif
    </div>
</div>
@endsection
