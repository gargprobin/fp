<?php

namespace App\Http\Controllers;

use App\Models\RecommendationLetter;
use Illuminate\Http\Request;

class RefereeController extends Controller
{
    /**
     * Show confidential upload form for referee.
     * Uses Laravel cryptographically signed URL checks to secure access.
     */
    public function showForm(Request $request, $token)
    {
        // Enforce valid signed URL signature
        if (! $request->hasValidSignature()) {
            abort(403, 'This recommendation link has expired or has an invalid signature.');
        }

        $letter = RecommendationLetter::where('token', $token)->firstOrFail();

        if ($letter->isSubmitted()) {
            return view('referee.completed', compact('letter'));
        }

        return view('referee.upload', compact('letter'));
    }

    /**
     * Store submitted recommendation letter and update application state.
     */
    public function submit(Request $request, $token)
    {
        // Enforce valid signed URL signature on POST too
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid signature.');
        }

        $letter = RecommendationLetter::where('token', $token)->firstOrFail();

        if ($letter->isSubmitted()) {
            return back()->with('error', 'This recommendation has already been submitted.');
        }

        $request->validate([
            'comments' => 'required|string|min:20',
            'recommendation_file' => 'nullable|file|mimes:pdf|max:5120', // Max 5MB PDF
        ]);

        $filePath = null;
        if ($request->hasFile('recommendation_file')) {
            // Save file in private/secure storage
            $filePath = $request->file('recommendation_file')->store('recommendations', 'local');
        }

        // Update letter record
        $letter->update([
            'comments' => $request->comments,
            'letter_path' => $filePath,
            'submitted_at' => now(),
        ]);

        // Mock state transition: update application status
        $application = $letter->application;
        if ($application->status === 'applied') {
            $application->update([
                'status' => 'shortlisted_by_admin', // Advance status to a valid enum option
            ]);
        }

        return redirect()->route('referee.success', ['token' => $token]);
    }

    /**
     * Success landing page.
     */
    public function success($token)
    {
        $letter = RecommendationLetter::where('token', $token)->firstOrFail();
        return view('referee.completed', compact('letter'));
    }
}
