<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    /**
     * Show Candidate Faculty Dashboard with secure application tracking and timeline stages.
     */
    public function dashboard(Request $request)
    {
        // Enforce candidate role authorization
        if (!auth()->check() || !auth()->user()->isCandidate()) {
            return redirect()->route('login-demo')->with('error', 'Please log in as a Faculty Candidate to access your Dashboard.');
        }

        $user = auth()->user();

        // Get candidate's applications with associations
        $query = Application::where('candidate_id', $user->id)
            ->with(['vacancy.institution', 'recommendationLetters']);

        // Handle GET input: search_app (filters title, subject, company_name)
        if ($request->filled('search_app')) {
            $search = $request->input('search_app');
            $query->where(function ($q) use ($search) {
                $q->whereHas('vacancy', function ($vq) use ($search) {
                    $vq->where('title', 'like', '%' . $search . '%')
                       ->orWhere('subject', 'like', '%' . $search . '%');
                })->orWhereHas('vacancy.institution', function ($iq) use ($search) {
                    $iq->where('company_name', 'like', '%' . $search . '%');
                });
            });
        }

        // Handle GET input: sort_app (latest, oldest, status_asc, status_desc)
        $sort = $request->input('sort_app', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'status_asc':
                $query->orderBy('status', 'asc');
                break;
            case 'status_desc':
                $query->orderBy('status', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $applications = $query->paginate(5, ['*'], 'apps_page')->withQueryString();

        return view('admin.faculty-dashboard', compact('applications'));
    }

    /**
     * Edit Faculty Candidate Profile.
     */
    public function editProfile()
    {
        if (!auth()->check() || !auth()->user()->isCandidate()) {
            return redirect()->route('login-demo')->with('error', 'Unauthorized.');
        }

        $user = auth()->user();
        $profile = $user->candidateProfile;

        return view('faculty.profile', compact('user', 'profile'));
    }

    /**
     * Update Faculty Candidate Profile.
     */
    public function updateProfile(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isCandidate()) {
            return redirect()->route('login-demo')->with('error', 'Unauthorized.');
        }

        $user = auth()->user();
        $profile = $user->candidateProfile;

        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'type' => 'required|in:academic,coaching',
            'current_designation' => 'nullable|string|max:100',
            'highest_degree' => 'required|string|max:100',
            'subjects_taught' => 'required|string',
            'experience_years' => 'required|numeric|min:0',
            'current_ctc' => 'nullable|numeric|min:0',
            'expected_ctc' => 'nullable|numeric|min:0',
            'preferred_location' => 'nullable|string|max:100',
            'experience_summary' => 'nullable|string',
            'google_scholar_url' => 'nullable|url|max:150',
            'h_index' => 'nullable|integer|min:0',
            'demo_video_url' => 'nullable|url|max:150',
        ]);

        // 1. Update basic User info
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->save();

        // 2. Update Candidate Profile
        $subjects = array_map('trim', explode(',', $request->subjects_taught));
        
        if (!$profile) {
            $profile = new \App\Models\CandidateProfile();
            $profile->user_id = $user->id;
        }

        $profile->fill([
            'type' => $request->type,
            'current_designation' => $request->current_designation,
            'highest_degree' => $request->highest_degree,
            'subjects_taught' => $subjects,
            'experience_summary' => $request->experience_summary,
            'experience_years' => $request->experience_years,
            'current_ctc' => $request->current_ctc,
            'expected_ctc' => $request->expected_ctc,
            'preferred_location' => $request->preferred_location,
            'google_scholar_url' => $request->google_scholar_url,
            'h_index' => $request->h_index ?? 0,
            'demo_video_url' => $request->demo_video_url,
        ]);
        $profile->save();

        return redirect()->route('faculty.dashboard')->with('success', 'Profile updated successfully!');
    }
}
