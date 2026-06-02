<?php

namespace App\Http\Controllers;

use App\Models\Vacancy;
use App\Models\Institution;
use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class VacancyController extends Controller
{
    /**
     * Display a listing of active faculty vacancies.
     */
    public function index(Request $request)
    {
        // 1. Dynamic Auto-Seeding for rich UX out of the box
        if (Vacancy::count() === 0) {
            $user = User::firstOrCreate(
                ['email' => 'recruiter@sourcing.com'],
                [
                    'name' => 'Dr. Sharma (Director)',
                    'password' => Hash::make('password'),
                    'role' => 'institution',
                    'theme_preference' => 'light',
                    'is_verified' => true
                ]
            );
            $institution = Institution::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => 'Vidyapeeth Coaching Classes',
                    'type' => 'coaching_center',
                    'website' => 'https://vidyapeeth.in',
                    'description' => 'India\'s premium institute for IIT-JEE and NEET preparation.',
                    'location' => 'Kota, Rajasthan',
                    'branding_settings' => [
                        'primary_color' => '#16a34a', // Emerald green
                        'accent_color' => '#22c55e',
                        'theme_preset' => 'emerald'
                    ]
                ]
            );

            $univUser = User::firstOrCreate(
                ['email' => 'dean@apexuniv.edu'],
                [
                    'name' => 'Dr. Rachel Green (Dean)',
                    'password' => Hash::make('password'),
                    'role' => 'institution',
                    'theme_preference' => 'light',
                    'is_verified' => true
                ]
            );
            $univ = Institution::firstOrCreate(
                ['user_id' => $univUser->id],
                [
                    'company_name' => 'Apex Global University',
                    'type' => 'university',
                    'website' => 'https://apexuniv.edu',
                    'description' => 'A premier world-class research university offering multidisciplinary education.',
                    'location' => 'Bangalore, Karnataka',
                    'branding_settings' => [
                        'primary_color' => '#b91c1c', // Crimson Red
                        'accent_color' => '#ef4444',
                        'theme_preset' => 'ruby'
                    ]
                ]
            );

            Vacancy::create([
                'institution_id' => $institution->id,
                'title' => 'Senior IIT-JEE Physics Faculty',
                'description' => 'We are seeking an outstanding Physics teacher to train IIT-JEE Advanced level aspirants. Must have deep understanding of classical mechanics, electromagnetism, and thermodynamics.',
                'subject' => 'Physics',
                'target_class' => 'IIT-JEE Advanced',
                'experience_required' => '5 years',
                'location' => 'Kota, Rajasthan',
                'salary_min' => 1200000,
                'salary_max' => 1800000,
                'deadline' => now()->addDays(30)->toDateString(),
                'status' => 'open'
            ]);

            Vacancy::create([
                'institution_id' => $institution->id,
                'title' => 'Senior NEET Chemistry Teacher',
                'description' => 'Looking for an experienced Chemistry faculty with a strong record in teaching Organic and Inorganic chemistry for NEET Medical aspirants.',
                'subject' => 'Chemistry',
                'target_class' => 'NEET UG',
                'experience_required' => '4 years',
                'location' => 'Kota, Rajasthan',
                'salary_min' => 1000000,
                'salary_max' => 1500000,
                'deadline' => now()->addDays(20)->toDateString(),
                'status' => 'open'
            ]);

            Vacancy::create([
                'institution_id' => $univ->id,
                'title' => 'Assistant Professor of Computer Science',
                'description' => 'The Department of Computer Science invite applications for an Assistant Professor position. Special preference for experts in Machine Learning, Distributed Systems, and Database Systems.',
                'subject' => 'Computer Science',
                'target_class' => 'B.Tech / M.Tech CS',
                'experience_required' => '3 years',
                'location' => 'Bangalore, Karnataka',
                'salary_min' => 1500000,
                'salary_max' => 2400000,
                'deadline' => now()->addDays(45)->toDateString(),
                'status' => 'open'
            ]);
        }

        $query = Vacancy::with('institution.user')->open();

        // Apply Search & Filters
        if ($request->filled('subject')) {
            $query->filterBySubject($request->subject);
        }
        if ($request->filled('location')) {
            $query->filterByLocation($request->location);
        }
        if ($request->filled('type')) {
            $query->whereHas('institution', function($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        $vacancies = $query->latest()->paginate(10);

        return view('vacancies.index', compact('vacancies'));
    }

    /**
     * Show a detailed vacancy page with Custom Brand styling.
     */
    public function show(Vacancy $vacancy)
    {
        // Load the relationship
        $vacancy->load('institution.user');

        return view('vacancies.show', compact('vacancy'));
    }

    /**
     * Candidate applies to a vacancy (Mock action).
     */
    public function apply(Request $request, Vacancy $vacancy)
    {
        if (!auth()->check()) {
            return redirect()->route('login-demo')->with('error', 'Please log in to apply.');
        }

        $user = auth()->user();

        // Prevent duplicate applications
        $exists = Application::where('vacancy_id', $vacancy->id)
                             ->where('candidate_id', $user->id)
                             ->exists();

        if ($exists) {
            return back()->with('error', 'You have already applied to this vacancy.');
        }

        // Create application
        $application = Application::create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => $user->id,
            'cover_letter' => $request->input('cover_letter', 'Interested in this faculty position.'),
            'status' => 'applied'
        ]);

        // Trigger automated confidential recommendation letter requests if referees are provided
        if ($request->filled('referee_name') && $request->filled('referee_email')) {
            $application->recommendationLetters()->create([
                'referee_name' => $request->referee_name,
                'referee_email' => $request->referee_email,
                'token' => bin2hex(random_bytes(16)),
            ]);
        }

        // Trigger Notifications for all 3 categories of users
        \App\Models\Notification::send(
            $user->id,
            '📥 Application Submitted',
            "You have applied for {$vacancy->title}. Your profile is sent for Admin screening.",
            route('faculty.dashboard') . '#app-' . $application->id,
            'success'
        );

        \App\Models\Notification::send(
            $vacancy->institution->user_id,
            '📥 New Sourced Applicant',
            "A new candidate has applied for {$vacancy->title}. Sourcing & Admin screening is underway.",
            route('recruiter.dashboard') . '#app-' . $application->id,
            'info'
        );

        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $ad) {
            \App\Models\Notification::send(
                $ad->id,
                '📥 New Faculty Applicant',
                "{$user->name} has applied for {$vacancy->title}. Review and shortlist.",
                route('admin.settings') . '#app-' . $application->id,
                'info'
            );
        }

        return back()->with('success', 'Application submitted successfully! Recommendation letter requests sent to your referee.');
    }
}
