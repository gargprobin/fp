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
            $user = User::where('email', 'admin@sourcing.com')->first();
            if (!$user) {
                $user = new User([
                    'email' => 'admin@sourcing.com',
                    'name' => 'Platform Super-Admin',
                    'password' => Hash::make('password'),
                    'theme_preference' => 'dark',
                ]);
                $user->role = 'admin';
                $user->is_verified = true;
                $user->save();
            }
        } elseif ($role === 'institution') {
            $user = User::where('email', 'recruiter@sourcing.com')->first();
            if (!$user) {
                $user = new User([
                    'email' => 'recruiter@sourcing.com',
                    'name' => 'Dr. Sharma (Director)',
                    'password' => Hash::make('password'),
                    'theme_preference' => 'light',
                ]);
                $user->role = 'institution';
                $user->is_verified = true;
                $user->save();
            }

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
            $user = User::where('email', 'faculty@gmail.com')->first();
            if (!$user) {
                $user = new User([
                    'email' => 'faculty@gmail.com',
                    'name' => 'Prof. Amit Verma',
                    'password' => Hash::make('password'),
                    'theme_preference' => 'academic_blue', // Starts with elegant Academic Blue
                ]);
                $user->role = 'candidate';
                $user->is_verified = true;
                $user->save();
            }

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
     * Submit login form manually.
     */
    public function submitLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->isAdmin()) {
                return redirect()->route('admin.settings')->with('success', 'Logged in successfully!');
            } elseif ($user->isInstitution()) {
                return redirect()->route('recruiter.dashboard')->with('success', 'Logged in successfully!');
            }

            return redirect()->route('faculty.dashboard')->with('success', 'Logged in successfully!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Logout.
     */
    public function logout()
    {
        session()->forget('impersonate_admin_id');
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
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
                'current_designation' => 'nullable|string|max:100',
                'highest_degree' => 'nullable|string|max:100',
                'subjects_taught' => 'nullable|string',
                'experience_years' => 'nullable|numeric|min:0',
                'current_ctc' => 'nullable|numeric|min:0',
                'expected_ctc' => 'nullable|numeric|min:0',
                'preferred_location' => 'nullable|string|max:100',
                'experience_summary' => 'nullable|string',
            ]);
        } else {
            $rules = array_merge($rules, [
                'company_name' => 'nullable|string|max:150',
                'type' => 'nullable|string|in:coaching_center,school,university,other',
                'website' => 'nullable|url|max:150',
                'location' => 'nullable|string|max:150',
                'description' => 'nullable|string',
            ]);
        }

        $validated = $request->validate($rules);

        // 2. Create User explicitly assigning non-fillable fields
        $user = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'theme_preference' => $validated['role'] === 'candidate' ? 'academic_blue' : 'light',
        ]);
        $user->role = $validated['role'];
        $user->is_verified = false; // New registrations require admin approval to unlock details!
        $user->save();

        // 3. Create Corresponding Profile
        if ($user->role === 'candidate') {
            $subjectsInput = trim($validated['subjects_taught'] ?? '');
            $subjects = $subjectsInput !== '' ? array_map('trim', explode(',', $subjectsInput)) : ['General'];
            
            CandidateProfile::create([
                'user_id' => $user->id,
                'type' => 'coaching',
                'current_designation' => $validated['current_designation'] ?? 'Candidate',
                'highest_degree' => $validated['highest_degree'] ?? 'Not Specified',
                'subjects_taught' => $subjects,
                'experience_summary' => $validated['experience_summary'] ?? 'Teaching enthusiast.',
                'experience_years' => $validated['experience_years'] ?? 0.0,
                'current_ctc' => $validated['current_ctc'] ?? 0.0,
                'expected_ctc' => $validated['expected_ctc'] ?? 0.0,
                'preferred_location' => $validated['preferred_location'] ?? 'Not Specified',
            ]);
        } else {
            Institution::create([
                'user_id' => $user->id,
                'company_name' => $validated['company_name'] ?? ($validated['name'] . "'s Institute"),
                'type' => $validated['type'] ?? 'coaching_center',
                'website' => $validated['website'] ?? null,
                'location' => $validated['location'] ?? 'Not Specified',
                'description' => $validated['description'] ?? 'An educational organization.',
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
                route('admin.settings', ['tab' => 'users']) . '#user-' . $user->id,
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
