<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    /**
     * Show Candidate Faculty Dashboard with secure application tracking and timeline stages.
     */
    public function dashboard()
    {
        // Enforce candidate role authorization
        if (!auth()->check() || !auth()->user()->isCandidate()) {
            return redirect()->route('login-demo')->with('error', 'Please log in as a Faculty Candidate to access your Dashboard.');
        }

        $user = auth()->user();

        // Get candidate's applications with associations
        $applications = Application::where('candidate_id', $user->id)
            ->with(['vacancy.institution', 'recommendationLetters'])
            ->latest()
            ->get();

        return view('admin.faculty-dashboard', compact('applications'));
    }
}
