<?php

namespace App\Http\Controllers;

use App\Models\Vacancy;
use App\Models\Application;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\Request;

class RecruiterController extends Controller
{
    /**
     * Show Recruiter Dashboard with Vacancy Posting and masked recruitment pipelines.
     */
    public function dashboard(Request $request)
    {
        // Enforce Institution role authorization
        if (!auth()->check() || !auth()->user()->isInstitution()) {
            return redirect()->route('login-demo')->with('error', 'Please log in as an Institution to access the Recruiter Dashboard.');
        }

        $user = auth()->user();
        $institution = $user->institution;

        if (!$institution) {
            return redirect()->route('home')->with('error', 'Institution profile not found.');
        }

        // Get all vacancy IDs for this institution to query applications
        $allVacancyIds = Vacancy::where('institution_id', $institution->id)->pluck('id');

        $allowedLimits = [10, 25, 50, 100, 500];

        $limitVacs = intval($request->input('per_page_vacs', session('per_page_recruiter_vacs', 25)));
        if (!in_array($limitVacs, $allowedLimits)) $limitVacs = 25;
        session(['per_page_recruiter_vacs' => $limitVacs]);

        $limitApps = intval($request->input('per_page_apps', session('per_page_recruiter_apps', 25)));
        if (!in_array($limitApps, $allowedLimits)) $limitApps = 25;
        session(['per_page_recruiter_apps' => $limitApps]);

        // Handle GET inputs for Vacancies: search_vac and sort_vac
        $vacanciesQuery = Vacancy::where('institution_id', $institution->id);
        if ($request->filled('search_vac')) {
            $searchVac = $request->input('search_vac');
            $vacanciesQuery->where(function ($q) use ($searchVac) {
                $q->where('title', 'like', '%' . $searchVac . '%')
                  ->orWhere('subject', 'like', '%' . $searchVac . '%')
                  ->orWhere('target_class', 'like', '%' . $searchVac . '%');
            });
        }
        $sortVac = $request->input('sort_vac', 'latest');
        switch ($sortVac) {
            case 'oldest':
                $vacanciesQuery->oldest();
                break;
            case 'title':
                $vacanciesQuery->orderBy('title', 'asc');
                break;
            case 'latest':
            default:
                $vacanciesQuery->latest();
                break;
        }
        $vacancies = $vacanciesQuery->paginate($limitVacs, ['*'], 'vacs_page')->withQueryString();

        // Handle GET inputs for Applications pipeline: search_app and sort_app
        $applicationsQuery = Application::whereIn('vacancy_id', $allVacancyIds)
            ->whereIn('status', [
                'shortlisted_by_admin', 
                'shortlisted_by_institution', 
                'unlocked', 
                'written_test', 
                'interview', 
                'demo', 
                'appointed', 
                'rejected'
            ])
            ->with(['vacancy', 'candidate.candidateProfile', 'recommendationLetters']);

        if ($request->filled('search_app')) {
            $searchApp = $request->input('search_app');
            $applicationsQuery->where(function ($q) use ($searchApp) {
                $q->whereHas('candidate', function ($cq) use ($searchApp) {
                    $cq->where('name', 'like', '%' . $searchApp . '%');
                })->orWhereHas('vacancy', function ($vq) use ($searchApp) {
                    $vq->where('title', 'like', '%' . $searchApp . '%')
                       ->orWhere('subject', 'like', '%' . $searchApp . '%');
                });
            });
        }

        $sortApp = $request->input('sort_app', 'latest');
        switch ($sortApp) {
            case 'oldest':
                $applicationsQuery->oldest();
                break;
            case 'status':
                $applicationsQuery->orderBy('status', 'asc');
                break;
            case 'rating':
                $applicationsQuery->orderByRaw('demo_rating is null asc, demo_rating desc');
                break;
            case 'latest':
            default:
                $applicationsQuery->latest();
                break;
        }
        $applications = $applicationsQuery->paginate($limitApps, ['*'], 'apps_page')->withQueryString();

        // Count pending raw applicants that are under Admin screening
        $pendingAdminScreeningCount = Application::whereIn('vacancy_id', $allVacancyIds)
            ->where('status', 'applied')
            ->count();

        return view('admin.recruiter-dashboard', compact('institution', 'vacancies', 'applications', 'pendingAdminScreeningCount', 'limitVacs', 'limitApps'));
    }

    /**
     * Recruiter posts a new faculty vacancy.
     */
    public function postVacancy(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isInstitution()) {
            abort(403, 'Unauthorized.');
        }

        $institution = auth()->user()->institution;

        $request->validate([
            'title' => 'required|string|max:100',
            'subject' => 'required|string|max:50',
            'target_class' => 'required|string|max:50',
            'experience_required' => 'required|string|max:50',
            'location' => 'required|string|max:100',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|gt:salary_min',
            'description' => 'required|string',
            'deadline' => 'required|date|after:today',
        ]);

        Vacancy::create([
            'institution_id' => $institution->id,
            'title' => $request->title,
            'subject' => $request->subject,
            'target_class' => $request->target_class,
            'experience_required' => $request->experience_required,
            'location' => $request->location,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'status' => 'open'
        ]);

        return back()->with('success', 'New faculty vacancy successfully posted for admin review and candidate sourcing!');
    }

    /**
     * Recruiter shortlists a candidate profile and requests contact unlock from Admin.
     */
    public function requestUnlock(Application $application)
    {
        if (!auth()->check() || !auth()->user()->isInstitution()) {
            abort(403, 'Unauthorized.');
        }

        // Verify ownership
        $vacancy = $application->vacancy;
        if ($vacancy->institution_id !== auth()->user()->institution->id) {
            abort(403, 'Access denied.');
        }

        // Move status to shortlisted_by_institution (which triggers unlock request for admin)
        $application->status = 'shortlisted_by_institution';
        $application->save();

        // Trigger Notifications for all 3 categories of users
        \App\Models\Notification::send(
            $application->candidate_id,
            '🤝 Recruiter Shortlisted',
            "Exciting! The recruiter has shortlisted your profile for {$application->vacancy->title} and requested a Line Up. Awaiting Admin approval.",
            route('faculty.dashboard') . '#app-' . $application->id,
            'info'
        );

        \App\Models\Notification::send(
            $application->vacancy->institution->user_id,
            '🔑 Line Up Requested',
            "Your request to Line Up Candidate #{$application->candidate_id} has been submitted to Admin.",
            route('recruiter.dashboard') . '#app-' . $application->id,
            'success'
        );

        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $ad) {
            \App\Models\Notification::send(
                $ad->id,
                '🔑 Line Up Approval Required',
                "{$application->vacancy->institution->company_name} requested a Line Up for Candidate #{$application->candidate_id} for {$application->vacancy->title}. Please approve.",
                route('admin.settings') . '#app-' . $application->id,
                'alert'
            );
        }

        return back()->with('success', 'Candidate shortlisted! Line Up request submitted to Super-Admin.');
    }

    /**
     * Recruiter moves application forward to scheduling pipelines.
     */
    public function updatePipeline(Request $request, Application $application)
    {
        if (!auth()->check() || !auth()->user()->isInstitution()) {
            abort(403, 'Unauthorized.');
        }

        // Verify ownership
        $vacancy = $application->vacancy;
        if ($vacancy->institution_id !== auth()->user()->institution->id) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'status' => 'required|in:written_test,interview,demo,appointed,rejected',
            'demo_rating' => 'nullable|numeric|min:1|max:10',
        ]);

        $application->status = $request->status;
        if ($request->has('demo_rating') && $request->filled('demo_rating')) {
            $application->demo_rating = $request->demo_rating;
        }
        $application->save();

        $stageName = ucfirst(str_replace('_', ' ', $request->status));

        // Trigger Notifications for all 3 categories of users
        \App\Models\Notification::send(
            $application->candidate_id,
            '📊 Vetting Stage Updated',
            "Your placement funnel for {$application->vacancy->title} moved to: {$stageName}.",
            route('faculty.dashboard') . '#app-' . $application->id,
            'info'
        );

        \App\Models\Notification::send(
            $application->vacancy->institution->user_id,
            '📊 Funnel State Updated',
            "Updated Candidate #{$application->candidate_id} to: {$stageName}.",
            route('recruiter.dashboard') . '#app-' . $application->id,
            'success'
        );

        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $ad) {
            \App\Models\Notification::send(
                $ad->id,
                '📊 Vetting Funnel Updated',
                "{$application->candidate->name} funnel state updated to {$stageName} under {$application->vacancy->institution->company_name}.",
                route('admin.settings') . '#app-' . $application->id,
                'info'
            );
        }

        return back()->with('success', 'Candidate recruitment funnel state updated to: ' . $stageName);
    }

    /**
     * Show edit vacancy form for recruiter.
     */
    public function editVacancy(Vacancy $vacancy)
    {
        if (!auth()->check() || !auth()->user()->isInstitution()) {
            abort(403, 'Unauthorized.');
        }

        $institution = auth()->user()->institution;
        if ($vacancy->institution_id !== $institution->id) {
            abort(403, 'Access denied.');
        }

        return view('recruiter.edit-vacancy', compact('vacancy'));
    }

    /**
     * Update vacancy details by recruiter.
     */
    public function updateVacancy(Request $request, Vacancy $vacancy)
    {
        if (!auth()->check() || !auth()->user()->isInstitution()) {
            abort(403, 'Unauthorized.');
        }

        $institution = auth()->user()->institution;
        if ($vacancy->institution_id !== $institution->id) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'title' => 'required|string|max:100',
            'subject' => 'required|string|max:50',
            'target_class' => 'required|string|max:50',
            'experience_required' => 'required|string|max:50',
            'location' => 'required|string|max:100',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|gt:salary_min',
            'description' => 'required|string',
            'deadline' => 'required|date|after:today',
            'status' => 'required|in:open,closed',
        ]);

        $vacancy->update([
            'title' => $request->title,
            'subject' => $request->subject,
            'target_class' => $request->target_class,
            'experience_required' => $request->experience_required,
            'location' => $request->location,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'status' => $request->status,
        ]);

        return redirect()->route('recruiter.dashboard', ['tab' => 'my-vacancies'])->with('success', 'Vacancy updated successfully!');
    }
}
