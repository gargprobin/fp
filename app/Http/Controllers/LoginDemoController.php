<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CandidateProfile;
use App\Models\Institution;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginDemoController extends Controller
{
    /**
     * Show mock login options.
     */
    public function show()
    {
        return view('login-demo');
    }

    /**
     * Execute quick login for demo.
     */
    public function login(Request $request)
    {
        $role = $request->query('role', 'candidate');

        // 1. Create or Find mock user
        if ($role === 'admin') {
            $user = User::firstOrCreate(
                ['email' => 'admin@sourcing.com'],
                [
                    'name' => 'Platform Super-Admin',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'theme_preference' => 'dark',
                    'is_verified' => true
                ]
            );
        } elseif ($role === 'institution') {
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

            // Create Institution Profile if missing
            if (!$user->institution) {
                Institution::create([
                    'user_id' => $user->id,
                    'company_name' => 'Vidyapeeth Coaching Classes',
                    'type' => 'coaching_center',
                    'website' => 'https://vidyapeeth.in',
                    'description' => 'India\'s premium institute for IIT-JEE and NEET preparation.',
                    'location' => 'Kota, Rajasthan',
                    'branding_settings' => [
                        'primary_color' => '#16a34a', // Emerald Green branding!
                        'accent_color' => '#22c55e',
                        'theme_preset' => 'emerald'
                    ]
                ]);
            }
        } else {
            $user = User::firstOrCreate(
                ['email' => 'faculty@gmail.com'],
                [
                    'name' => 'Prof. Amit Verma',
                    'password' => Hash::make('password'),
                    'role' => 'candidate',
                    'theme_preference' => 'academic_blue', // Starts with elegant Academic Blue
                    'is_verified' => true
                ]
            );

            // Create Candidate Profile if missing
            if (!$user->candidateProfile) {
                CandidateProfile::create([
                    'user_id' => $user->id,
                    'type' => 'coaching',
                    'current_designation' => 'Senior Physics Faculty',
                    'highest_degree' => 'M.Tech from IIT Kharagpur',
                    'subjects_taught' => ['Physics', 'Mechanics', 'Optics'],
                    'experience_summary' => 'Ex-Allen senior faculty with 8+ years of teaching mechanics for IIT-JEE Advanced aspirants.',
                    'experience_years' => 8.5,
                    'google_scholar_url' => 'https://scholar.google.com/citations?user=xyz',
                    'h_index' => 5,
                    'demo_video_url' => 'https://www.youtube.com/watch?v=demo',
                    'current_ctc' => 1500000,
                    'expected_ctc' => 2000000,
                    'preferred_location' => 'Kota or Delhi'
                ]);
            }
        }

        Auth::login($user);

        if ($user->isAdmin()) {
            return redirect()->route('admin.settings')->with('success', 'Logged in as Admin Control Console!');
        } elseif ($user->isInstitution()) {
            return redirect()->route('recruiter.dashboard')->with('success', 'Logged in as Recruiter Vidyapeeth Coaching Classes!');
        }

        return redirect()->route('faculty.dashboard')->with('success', 'Logged in as Candidate ' . $user->name . '!');
    }

    /**
     * Logout.
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('vacancies.index')->with('success', 'Logged out.');
    }

    /**
     * Show SignUp form.
     */
    public function showSignUp()
    {
        return view('signup');
    }

    /**
     * Process SignUp form submission.
     */
    public function signUp(Request $request)
    {
        $role = $request->input('role', 'candidate');

        // 1. Validation
        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email|max:150',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:candidate,institution',
            'phone' => 'required|string|max:20',
        ];

        if ($role === 'candidate') {
            $rules = array_merge($rules, [
                'current_designation' => 'required|string|max:100',
                'highest_degree' => 'required|string|max:100',
                'subjects_taught' => 'required|string',
                'experience_years' => 'required|numeric|min:0',
                'current_ctc' => 'required|numeric|min:0',
                'expected_ctc' => 'required|numeric|min:0',
                'preferred_location' => 'required|string|max:100',
                'experience_summary' => 'required|string',
            ]);
        } else {
            $rules = array_merge($rules, [
                'company_name' => 'required|string|max:150',
                'type' => 'required|string|in:coaching_center,school,university,other',
                'website' => 'required|url|max:150',
                'location' => 'required|string|max:150',
                'description' => 'required|string',
            ]);
        }

        $validated = $request->validate($rules);

        // 2. Create User
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'],
            'theme_preference' => $validated['role'] === 'candidate' ? 'academic_blue' : 'light',
            'is_verified' => false, // New registrations require admin approval to unlock details!
        ]);

        // 3. Create Corresponding Profile
        if ($user->role === 'candidate') {
            // Parse subjects taught as array
            $subjects = array_map('trim', explode(',', $validated['subjects_taught']));
            CandidateProfile::create([
                'user_id' => $user->id,
                'type' => 'coaching',
                'current_designation' => $validated['current_designation'],
                'highest_degree' => $validated['highest_degree'],
                'subjects_taught' => $subjects,
                'experience_summary' => $validated['experience_summary'],
                'experience_years' => $validated['experience_years'],
                'current_ctc' => $validated['current_ctc'],
                'expected_ctc' => $validated['expected_ctc'],
                'preferred_location' => $validated['preferred_location'],
            ]);
        } else {
            Institution::create([
                'user_id' => $user->id,
                'company_name' => $validated['company_name'],
                'type' => $validated['type'],
                'website' => $validated['website'],
                'location' => $validated['location'],
                'description' => $validated['description'],
                'branding_settings' => [
                    'primary_color' => '#2563eb', // Default elegant blue
                    'accent_color' => '#3b82f6',
                    'theme_preset' => 'classic'
                ]
            ]);
        }

        // 4. Log in the newly registered user
        Auth::login($user);

        // 5. Send real-time notifications to user and admin
        \App\Models\Notification::send(
            $user->id,
            '👋 Welcome to ' . (SiteSetting::getVal('platform_name', 'Sourcing Portal')),
            "Thank you for creating an account! Complete your profile settings or browse jobs.",
            $user->role === 'candidate' ? route('faculty.dashboard') : route('recruiter.dashboard'),
            'success'
        );

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $ad) {
            \App\Models\Notification::send(
                $ad->id,
                '🔔 New User Registered',
                "New user {$user->name} ({$user->email}) registered as " . ($user->role === 'candidate' ? 'Faculty Candidate' : 'Institution Recruiter') . ". Check verification.",
                route('admin.settings'),
                'info'
            );
        }

        // 6. Redirect
        if ($user->isInstitution()) {
            return redirect()->route('recruiter.dashboard')->with('success', 'Registration successful! Welcome to your Recruiter Panel.');
        }

        return redirect()->route('faculty.dashboard')->with('success', 'Registration successful! Welcome to your Faculty Panel.');
    }
}
