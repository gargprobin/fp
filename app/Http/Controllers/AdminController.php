<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\Application;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display SaaS platform global settings and administrative dashboard.
     */
    public function index(Request $request)
    {
        // Enforce strict Admin authorization
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        // 1. SaaS Platform Settings
        $platformName = SiteSetting::getVal('platform_name', 'Sourcing Portal');
        $platformTagline = SiteSetting::getVal('platform_tagline', "Premium Faculty Placement & Recruitment Service");
        $supportEmail = SiteSetting::getVal('support_email', 'support@sourcing.com');

        // WhatsApp settings
        $whatsappProvider = SiteSetting::getVal('whatsapp_api_provider', 'wa.rj19web.com Official Gateway');
        $whatsappApiKey = SiteSetting::getVal('whatsapp_api_key');
        if (!empty($whatsappApiKey)) {
            $whatsappApiKey = '********************';
        }
        $whatsappApiUrl = SiteSetting::getVal('whatsapp_api_url');
        if (!empty($whatsappApiUrl)) {
            $whatsappApiUrl = '********************';
        }
        $whatsappEnabled = SiteSetting::getVal('whatsapp_enabled', '1');

        $whatsappSentLogs = json_decode(SiteSetting::getVal('whatsapp_sent_logs', '[]'), true);

        // 2. Platform Statistics
        $totalCandidates = User::where('role', 'candidate')->count();
        $totalInstitutions = User::where('role', 'institution')->count();
        $totalVacancies = Vacancy::count();
        $totalApplications = Application::count();

        // 3. Moderation Lists with Page-independent pagination, search & sort
        $searchUser = $request->input('search_user');
        $userRole = $request->input('user_role');
        $sortUser = $request->input('sort_user', 'latest');

        $usersQuery = User::with('candidateProfile', 'institution');
        if ($searchUser) {
            $usersQuery->where(function($q) use ($searchUser) {
                $q->where('name', 'like', "%{$searchUser}%")
                  ->orWhere('email', 'like', "%{$searchUser}%")
                  ->orWhere('phone', 'like', "%{$searchUser}%");
            });
        }
        if ($userRole && $userRole !== 'all') {
            $usersQuery->where('role', $userRole);
        }
        if ($sortUser === 'name_asc') {
            $usersQuery->orderBy('name', 'asc');
        } elseif ($sortUser === 'name_desc') {
            $usersQuery->orderBy('name', 'desc');
        } else {
            $usersQuery->latest();
        }
        $users = $usersQuery->paginate(15, ['*'], 'users_page')->withQueryString();

        $searchVacancy = $request->input('search_vacancy');
        $sortVacancy = $request->input('sort_vacancy', 'latest');

        $vacanciesQuery = Vacancy::with('institution');
        if ($searchVacancy) {
            $vacanciesQuery->where(function($q) use ($searchVacancy) {
                $q->where('title', 'like', "%{$searchVacancy}%")
                  ->orWhere('subject', 'like', "%{$searchVacancy}%")
                  ->orWhere('location', 'like', "%{$searchVacancy}%");
            });
        }
        if ($sortVacancy === 'title_asc') {
            $vacanciesQuery->orderBy('title', 'asc');
        } elseif ($sortVacancy === 'salary_high') {
            $vacanciesQuery->orderBy('salary_max', 'desc');
        } else {
            $vacanciesQuery->latest();
        }
        $vacancies = $vacanciesQuery->paginate(15, ['*'], 'vacancies_page')->withQueryString();

        $searchApp = $request->input('search_app');
        $statusApp = $request->input('status_app');
        $sortApp = $request->input('sort_app', 'latest');

        $appsQuery = Application::with('vacancy.institution', 'candidate');
        if ($searchApp) {
            $appsQuery->where(function($q) use ($searchApp) {
                $q->whereHas('candidate', function($query) use ($searchApp) {
                    $query->where('name', 'like', "%{$searchApp}%");
                })->orWhereHas('vacancy', function($query) use ($searchApp) {
                    $query->where('title', 'like', "%{$searchApp}%")
                          ->orWhere('subject', 'like', "%{$searchApp}%");
                });
            });
        }
        if ($statusApp && $statusApp !== 'all') {
            $appsQuery->where('status', $statusApp);
        }
        if ($sortApp === 'oldest') {
            $appsQuery->oldest();
        } else {
            $appsQuery->latest();
        }
        $applications = $appsQuery->paginate(15, ['*'], 'apps_page')->withQueryString();

        // 4. Soft Deleted (Trash Lists)
        $searchDelUser = $request->input('search_del_user');
        $sortDelUser = $request->input('sort_del_user', 'latest');

        $deletedUsersQuery = User::onlyTrashed()->with('candidateProfile', 'institution');
        if ($searchDelUser) {
            $deletedUsersQuery->where(function($q) use ($searchDelUser) {
                $q->where('name', 'like', "%{$searchDelUser}%")
                  ->orWhere('email', 'like', "%{$searchDelUser}%");
            });
        }
        if ($sortDelUser === 'oldest') {
            $deletedUsersQuery->orderBy('deleted_at', 'asc');
        } elseif ($sortDelUser === 'name') {
            $deletedUsersQuery->orderBy('name', 'asc');
        } else {
            $deletedUsersQuery->orderBy('deleted_at', 'desc');
        }
        $deletedUsers = $deletedUsersQuery->paginate(15, ['*'], 'deleted_users_page')->withQueryString();

        $searchDelVac = $request->input('search_del_vac');
        $sortDelVac = $request->input('sort_del_vac', 'latest');

        $deletedVacanciesQuery = Vacancy::onlyTrashed()->with('institution');
        if ($searchDelVac) {
            $deletedVacanciesQuery->where(function($q) use ($searchDelVac) {
                $q->where('title', 'like', "%{$searchDelVac}%")
                  ->orWhere('subject', 'like', "%{$searchDelVac}%")
                  ->orWhereHas('institution', function($instQ) use ($searchDelVac) {
                      $instQ->where('company_name', 'like', "%{$searchDelVac}%");
                  });
            });
        }
        if ($sortDelVac === 'oldest') {
            $deletedVacanciesQuery->orderBy('deleted_at', 'asc');
        } elseif ($sortDelVac === 'title') {
            $deletedVacanciesQuery->orderBy('title', 'asc');
        } else {
            $deletedVacanciesQuery->orderBy('deleted_at', 'desc');
        }
        $deletedVacancies = $deletedVacanciesQuery->paginate(15, ['*'], 'deleted_vacancies_page')->withQueryString();

        $searchDelApp = $request->input('search_del_app');
        $sortDelApp = $request->input('sort_del_app', 'latest');

        $deletedApplicationsQuery = Application::onlyTrashed()->with('vacancy.institution', 'candidate');
        if ($searchDelApp) {
            $deletedApplicationsQuery->where(function($q) use ($searchDelApp) {
                $q->whereHas('candidate', function($candQ) use ($searchDelApp) {
                    $candQ->where('name', 'like', "%{$searchDelApp}%");
                })->orWhereHas('vacancy', function($vacQ) use ($searchDelApp) {
                    $vacQ->where('title', 'like', "%{$searchDelApp}%");
                });
            });
        }
        if ($sortDelApp === 'oldest') {
            $deletedApplicationsQuery->orderBy('deleted_at', 'asc');
        } elseif ($sortDelApp === 'status') {
            $deletedApplicationsQuery->orderBy('status', 'asc');
        } else {
            $deletedApplicationsQuery->orderBy('deleted_at', 'desc');
        }
        $deletedApplications = $deletedApplicationsQuery->paginate(15, ['*'], 'deleted_apps_page')->withQueryString();

        return view('admin.settings', compact(
            'platformName', 'platformTagline', 'supportEmail',
            'whatsappProvider', 'whatsappApiKey', 'whatsappApiUrl', 'whatsappEnabled', 'whatsappSentLogs',
            'totalCandidates', 'totalInstitutions', 'totalVacancies', 'totalApplications',
            'users', 'vacancies', 'applications',
            'deletedUsers', 'deletedVacancies', 'deletedApplications'
        ));
    }

    /**
     * Update SaaS global configuration in the database.
     */
    public function update(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        if ($request->has('platform_name')) {
            $request->validate([
                'platform_name' => 'required|string|max:50',
                'platform_tagline' => 'required|string|max:150',
                'support_email' => 'required|email|max:100',
            ]);

            SiteSetting::setVal('platform_name', $request->platform_name);
            SiteSetting::setVal('platform_tagline', $request->platform_tagline);
            SiteSetting::setVal('support_email', $request->support_email);

            return back()->with('success', 'Portal Platform global settings updated successfully!');
        }

        if ($request->has('whatsapp_api_provider')) {
            SiteSetting::setVal('whatsapp_api_provider', $request->whatsapp_api_provider);
            
            // Avoid overwriting database values if the input contains mask patterns
            $apiKey = $request->whatsapp_api_key;
            if (!preg_match('/^[\*•]{3,}$/', (string)$apiKey)) {
                SiteSetting::setVal('whatsapp_api_key', $apiKey);
            }

            $apiUrl = $request->whatsapp_api_url;
            if (!preg_match('/^[\*•]{3,}$/', (string)$apiUrl)) {
                SiteSetting::setVal('whatsapp_api_url', $apiUrl);
            }

            SiteSetting::setVal('whatsapp_enabled', $request->has('whatsapp_enabled') ? '1' : '0');

            return back()->with('success', 'WhatsApp API Gateway configurations and messaging triggers updated successfully!');
        }

        return back();
    }

    /**
     * Clear and reload all system caches (configs, routes, views, app cache).
     */
    public function clearCache()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');

            return back()->with('success', '⚡ System Cache cleared successfully! All settings and routes have been reloaded.');
        } catch (\Exception $e) {
            return back()->with('error', '❌ Failed to clear system cache: ' . $e->getMessage());
        }
    }

    /**
     * Toggle a user's verification badge.
     */
    public function toggleVerification($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $user = User::findOrFail($id);
        $user->is_verified = !$user->is_verified;
        $user->save();

        return back()->with('success', "User '{$user->name}' verification status updated successfully!");
    }

    /**
     * Toggle a vacancy's status (open/closed).
     */
    public function toggleVacancyStatus($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $vacancy = Vacancy::findOrFail($id);
        $vacancy->status = ($vacancy->status === 'open') ? 'closed' : 'open';
        $vacancy->save();

        return back()->with('success', "Vacancy status for '{$vacancy->title}' updated successfully!");
    }

    /**
     * Delete a vacancy.
     */
    public function deleteVacancy($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $vacancy = Vacancy::findOrFail($id);
        $title = $vacancy->title;
        $vacancy->delete();

        return back()->with('success', "Vacancy '{$title}' has been successfully deleted.");
    }

    /**
     * Shortlist a raw faculty application to be forwarded to the institution (masked CV).
     */
    public function shortlistApplication($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $application = Application::findOrFail($id);
        $application->status = 'shortlisted_by_admin';
        $application->save();

        // Trigger Notifications for all 3 categories of users
        \App\Models\Notification::send(
            $application->candidate_id,
            '🔎 Profile Shortlisted',
            "Good news! Admin has screened and shortlisted your profile for {$application->vacancy->title}. Recruiter is reviewing.",
            route('faculty.dashboard') . '#app-' . $application->id,
            'success'
        );

        \App\Models\Notification::send(
            $application->vacancy->institution->user_id,
            '🔎 Masked Profile Sourced',
            "Admin has shortlisted Candidate #{$application->candidate_id} for {$application->vacancy->title}. Review masked CV and request Line Up.",
            route('recruiter.dashboard') . '#app-' . $application->id,
            'info'
        );

        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $ad) {
            \App\Models\Notification::send(
                $ad->id,
                '🔎 Candidate Shortlisted',
                "You shortlisted {$application->candidate->name} for {$application->vacancy->title}.",
                route('admin.settings') . '#app-' . $application->id,
                'success'
            );
        }

        return back()->with('success', "Faculty application successfully pre-screened and shortlisted! Masked CV is now forwarded to the Recruiter dashboard.");
    }

    /**
     * Approve the recruiter's contact details unlock request.
     */
    public function unlockApprove($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $application = Application::findOrFail($id);
        $application->status = 'unlocked';
        $application->save();

        // Trigger Notifications for all 3 categories of users
        \App\Models\Notification::send(
            $application->candidate_id,
            '🟢 Line Up Approved!',
            "Congratulations! You are Lined Up with {$application->vacancy->institution->company_name} for {$application->vacancy->title}. Coaching details unlocked!",
            route('faculty.dashboard') . '#app-' . $application->id,
            'success'
        );

        \App\Models\Notification::send(
            $application->vacancy->institution->user_id,
            '🟢 Line Up Approved!',
            "Admin approved Line Up for {$application->candidate->name} for {$application->vacancy->title}. Contact details unlocked!",
            route('recruiter.dashboard') . '#app-' . $application->id,
            'success',
            $application->candidate_id
        );

        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $ad) {
            \App\Models\Notification::send(
                $ad->id,
                '🟢 Line Up Approved',
                "You approved the Line Up of {$application->candidate->name} under {$application->vacancy->institution->company_name}.",
                route('admin.settings') . '#app-' . $application->id,
                'success'
            );
        }

        return back()->with('success', "Institution unlock request approved! Faculty contact details are now fully visible to the recruiter.");
    }

    /* ================================================================
     | USER IMPERSONATION SYSTEM
     | ================================================================ */

    /**
     * Login as a different user (Candidate or Recruiter).
     */
    public function impersonate($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return back()->with('error', '❌ Admin cannot impersonate another Admin.');
        }

        // Store original admin ID in session
        session(['impersonate_admin_id' => auth()->id()]);

        // Login as target user
        auth()->login($user);

        // Redirect to their dashboard
        if ($user->isCandidate()) {
            return redirect()->route('faculty.dashboard')->with('success', "👤 Impersonating: You are now logged in as {$user->name}.");
        } elseif ($user->isInstitution() || $user->isRecruiter()) {
            return redirect()->route('recruiter.dashboard')->with('success', "👤 Impersonating: You are now logged in as {$user->name}.");
        }

        return redirect()->route('home')->with('success', "👤 Impersonating: You are now logged in as {$user->name}.");
    }

    /**
     * Leave impersonation mode and return to admin.
     */
    public function leaveImpersonate()
    {
        if (!session()->has('impersonate_admin_id')) {
            return redirect()->route('home')->with('error', 'No active impersonation session.');
        }

        $adminId = session('impersonate_admin_id');
        $admin = User::find($adminId);

        if (!$admin) {
            session()->forget('impersonate_admin_id');
            auth()->logout();
            return redirect()->route('login-demo')->with('error', 'Admin user not found.');
        }

        // Login as Admin
        auth()->login($admin);
        session()->forget('impersonate_admin_id');

        return redirect()->route('admin.settings')->with('success', '⚡ Returned to Portal Control Center successfully.');
    }

    /* ================================================================
     | ADVANCED USER CRUD & MODERATION
     | ================================================================ */

    /**
     * Create a new Candidate or Recruiter User.
     */
    public function storeUser(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:15',
            'role'     => 'required|in:candidate,institution',
            'password' => 'required|string|min:6',
            // Recruiter specific
            'company_name' => 'required_if:role,institution|nullable|string|max:100',
        ]);

        $user = new User([
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'password'    => bcrypt($request->password),
        ]);
        $user->role = $request->role;
        $user->is_verified = true;
        $user->save();

        if ($request->role === 'candidate') {
            \App\Models\CandidateProfile::create([
                'user_id'             => $user->id,
                'type'                => 'academic',
                'current_designation' => 'Lecturer',
                'highest_degree'      => 'Postgraduate',
                'subjects_taught'     => ['General'],
                'experience_summary'  => 'A passionate academic educator.',
                'experience_years'    => 1.0,
                'preferred_location'  => 'Kota',
            ]);
        } elseif ($request->role === 'institution') {
            \App\Models\Institution::create([
                'user_id'           => $user->id,
                'company_name'      => $request->company_name ?? 'Sourcing Partner',
                'type'              => 'coaching_center',
                'website'           => 'https://fp.rj19web.com',
                'description'       => 'Leading educational brand and portal partner.',
                'location'          => 'Kota, Rajasthan',
                'branding_settings' => [
                    'primary_color' => '#2563eb',
                    'accent_color'  => '#3b82f6',
                    'theme_preset'  => 'classic'
                ]
            ]);
        }

        return back()->with('success', "👥 User '{$user->name}' successfully created!");
    }

    /**
     * Update a user's details.
     */
    public function updateUser(Request $request, $id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $user = User::findOrFail($id);

        $rules = [
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
        ];

        if ($user->isCandidate()) {
            $rules = array_merge($rules, [
                'highest_degree' => 'nullable|string|max:100',
                'subjects_taught' => 'nullable|string',
                'experience_years' => 'nullable|numeric|min:0',
                'current_designation' => 'nullable|string|max:100',
                'experience_summary' => 'nullable|string',
                'current_ctc' => 'nullable|numeric|min:0',
                'expected_ctc' => 'nullable|numeric|min:0',
                'preferred_location' => 'nullable|string|max:100',
                'demo_video_url' => 'nullable|url|max:150',
                'google_scholar_url' => 'nullable|url|max:150',
                'h_index' => 'nullable|integer|min:0',
                'candidate_type' => 'nullable|in:academic,coaching',
            ]);
        } elseif ($user->isInstitution()) {
            $rules = array_merge($rules, [
                'company_name' => 'nullable|string|max:150',
                'institution_type' => 'nullable|string|in:coaching_center,school,university,other',
                'location' => 'nullable|string|max:150',
                'website' => 'nullable|url|max:150',
                'description' => 'nullable|string',
            ]);
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->is_verified = $request->has('is_verified');
        $user->save();

        if ($user->isCandidate()) {
            $profile = $user->candidateProfile;
            if (!$profile) {
                $profile = new \App\Models\CandidateProfile();
                $profile->user_id = $user->id;
            }
            $subjectsInput = trim($request->subjects_taught ?? '');
            $subjects = $subjectsInput !== '' ? array_map('trim', explode(',', $subjectsInput)) : ['General'];

            $profile->fill([
                'type' => $request->candidate_type ?? 'academic',
                'highest_degree' => $request->highest_degree ?? 'Not Specified',
                'subjects_taught' => $subjects,
                'experience_years' => $request->experience_years ?? 0.0,
                'current_designation' => $request->current_designation,
                'experience_summary' => $request->experience_summary,
                'current_ctc' => $request->current_ctc,
                'expected_ctc' => $request->expected_ctc,
                'preferred_location' => $request->preferred_location,
                'demo_video_url' => $request->demo_video_url,
                'google_scholar_url' => $request->google_scholar_url,
                'h_index' => $request->h_index ?? 0,
            ]);
            $profile->save();
        } elseif ($user->isInstitution()) {
            $inst = $user->institution;
            if (!$inst) {
                $inst = new \App\Models\Institution();
                $inst->user_id = $user->id;
            }
            $inst->fill([
                'company_name' => $request->company_name ?? ($user->name . "'s Institute"),
                'type' => $request->institution_type ?? 'coaching_center',
                'location' => $request->location ?? 'Not Specified',
                'website' => $request->website,
                'description' => $request->description,
            ]);
            $inst->save();
        }

        return back()->with('success', "👥 User '{$user->name}' updated successfully!");
    }

    /**
     * Soft delete a User.
     */
    public function deleteUser($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', '❌ Admin cannot delete their own account.');
        }

        $user->delete();

        return back()->with('success', "👥 User '{$user->name}' successfully soft-deleted! Added to System Trash.");
    }

    /**
     * Restore a soft-deleted User.
     */
    public function restoreUser($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', "👥 User '{$user->name}' successfully restored!");
    }

    /* ================================================================
     | ADVANCED VACANCY CRUD
     | ================================================================ */

    /**
     * Create a new Vacancy.
     */
    public function storeVacancy(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'institution_id'      => 'required|exists:institutions,id',
            'title'               => 'required|string|max:100',
            'description'         => 'required|string',
            'subject'             => 'required|string|max:50',
            'target_class'        => 'required|string|max:50',
            'experience_required' => 'required|string|max:50',
            'location'            => 'required|string|max:100',
            'salary_min'          => 'required|numeric',
            'salary_max'          => 'required|numeric',
            'deadline'            => 'required|date',
        ]);

        Vacancy::create($request->all() + ['status' => 'open']);

        return back()->with('success', '💼 Vacancy successfully created under Sourcing Institution!');
    }

    /**
     * Update a Vacancy.
     */
    public function updateVacancy(Request $request, $id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $vacancy = Vacancy::findOrFail($id);

        $request->validate([
            'title'               => 'required|string|max:100',
            'description'         => 'required|string',
            'subject'             => 'required|string|max:50',
            'target_class'        => 'required|string|max:50',
            'experience_required' => 'required|string|max:50',
            'location'            => 'required|string|max:100',
            'salary_min'          => 'required|numeric',
            'salary_max'          => 'required|numeric',
            'deadline'            => 'required|date',
        ]);

        $vacancy->update($request->all());

        return back()->with('success', "💼 Vacancy '{$vacancy->title}' updated successfully!");
    }

    /**
     * Restore a soft-deleted Vacancy.
     */
    public function restoreVacancy($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $vacancy = Vacancy::onlyTrashed()->findOrFail($id);
        $vacancy->restore();

        return back()->with('success', "💼 Vacancy '{$vacancy->title}' successfully restored!");
    }

    /* ================================================================
     | PIPELINE APPLICATION CRUD
     | ================================================================ */

    /**
     * Soft delete an Application.
     */
    public function deleteApplication($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $application = Application::findOrFail($id);
        $application->delete();

        return back()->with('success', "📝 Application successfully soft-deleted! Added to System Trash.");
    }

    /**
     * Restore an Application.
     */
    public function restoreApplication($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $application = Application::onlyTrashed()->findOrFail($id);
        $application->restore();

        return back()->with('success', "📝 Application successfully restored!");
    }

    /* ================================================================
     | WHATSAPP API TEST BENCH (SANDBOX)
     | ================================================================ */

    /**
     * Send manual WhatsApp message using registered templates.
     */
    public function sendTestWhatsApp(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'phone_number'  => 'required|string|max:15',
            'template_name' => 'required|string',
            'field_1'       => 'nullable|string',
            'field_2'       => 'nullable|string',
            'field_3'       => 'nullable|string',
            'field_4'       => 'nullable|string',
            'field_5'       => 'nullable|string',
            'field_6'       => 'nullable|string',
            'field_7'       => 'nullable|string',
        ]);

        // Construct clean variables array
        $variables = [];
        if ($request->filled('field_1')) $variables[] = $request->field_1;
        if ($request->filled('field_2')) $variables[] = $request->field_2;
        if ($request->filled('field_3')) $variables[] = $request->field_3;
        if ($request->filled('field_4')) $variables[] = $request->field_4;
        if ($request->filled('field_5')) $variables[] = $request->field_5;
        if ($request->filled('field_6')) $variables[] = $request->field_6;
        if ($request->filled('field_7')) $variables[] = $request->field_7;

        $user = auth()->user();

        // Dispatch manual test
        $success = \App\Models\Notification::dispatchWhatsApp(
            $request->phone_number,
            $request->template_name,
            $variables,
            $user
        );

        if ($success) {
            return back()->with('success', '⚡ WhatsApp sandbox message successfully sent! Verify your phone.');
        }

        return back()->with('error', '❌ WhatsApp sandbox dispatch failed. Check laravel.log for errors.');
    }
}
