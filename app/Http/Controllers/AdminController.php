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
    public function index()
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
        $whatsappProvider = SiteSetting::getVal('whatsapp_api_provider', 'Meta Cloud API');
        $whatsappApiKey = SiteSetting::getVal('whatsapp_api_key', 'jUDMUIGKNETSvQ4CaAjYbyRJS9YNAMXsVTQvJjxUPlpc5LvQIluo36YrMNAcGB5O');
        $whatsappApiUrl = SiteSetting::getVal('whatsapp_api_url', 'https://wa.rj19web.com/api/6e2f51c7-21b0-450b-9329-14955a8c79a/contact/send-message');
        $whatsappEnabled = SiteSetting::getVal('whatsapp_enabled', '1');

        $whatsappSentLogs = json_decode(SiteSetting::getVal('whatsapp_sent_logs', '[]'), true);

        // 2. Platform Statistics
        $totalCandidates = User::where('role', 'candidate')->count();
        $totalInstitutions = User::where('role', 'institution')->count();
        $totalVacancies = Vacancy::count();
        $totalApplications = Application::count();

        // 3. Moderation Lists
        $users = User::with('candidateProfile', 'institution')->latest()->get();
        $vacancies = Vacancy::with('institution')->latest()->get();
        $applications = Application::with('vacancy', 'candidate')->latest()->get();

        return view('admin.settings', compact(
            'platformName', 'platformTagline', 'supportEmail',
            'whatsappProvider', 'whatsappApiKey', 'whatsappApiUrl', 'whatsappEnabled', 'whatsappSentLogs',
            'totalCandidates', 'totalInstitutions', 'totalVacancies', 'totalApplications',
            'users', 'vacancies', 'applications'
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

            return back()->with('success', 'SaaS Platform global settings updated successfully!');
        }

        if ($request->has('whatsapp_api_provider')) {
            SiteSetting::setVal('whatsapp_api_provider', $request->whatsapp_api_provider);
            SiteSetting::setVal('whatsapp_api_key', $request->whatsapp_api_key);
            SiteSetting::setVal('whatsapp_api_url', $request->whatsapp_api_url);
            SiteSetting::setVal('whatsapp_enabled', $request->has('whatsapp_enabled') ? '1' : '0');

            return back()->with('success', 'WhatsApp API Gateway configurations and messaging triggers updated successfully!');
        }

        return back();
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
            route('faculty.dashboard'),
            'success'
        );

        \App\Models\Notification::send(
            $application->vacancy->institution->user_id,
            '🔎 Masked Profile Sourced',
            "Admin has shortlisted Candidate #{$application->candidate_id} for {$application->vacancy->title}. Review masked CV and request Line Up.",
            route('recruiter.dashboard'),
            'info'
        );

        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $ad) {
            \App\Models\Notification::send(
                $ad->id,
                '🔎 Candidate Shortlisted',
                "You shortlisted {$application->candidate->name} for {$application->vacancy->title}.",
                route('admin.settings'),
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
            route('faculty.dashboard'),
            'success'
        );

        \App\Models\Notification::send(
            $application->vacancy->institution->user_id,
            '🟢 Line Up Approved!',
            "Admin approved Line Up for {$application->candidate->name} for {$application->vacancy->title}. Contact details unlocked!",
            route('recruiter.dashboard'),
            'success'
        );

        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $ad) {
            \App\Models\Notification::send(
                $ad->id,
                '🟢 Line Up Approved',
                "You approved the Line Up of {$application->candidate->name} under {$application->vacancy->institution->company_name}.",
                route('admin.settings'),
                'success'
            );
        }

        return back()->with('success', "Institution unlock request approved! Faculty contact details are now fully visible to the recruiter.");
    }
}
