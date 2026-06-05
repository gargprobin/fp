<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\RefereeController;
use App\Http\Controllers\LoginDemoController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RecruiterController;
use App\Http\Controllers\FacultyController;

// 1. Home Landing Page (Same to Same c:\jobs)
use App\Models\Vacancy;
use Illuminate\Http\Request;

Route::get('/', function (Request $request) {
    if (Vacancy::count() === 0) {
        // Trigger auto-seeding in VacancyController
        app(App\Http\Controllers\VacancyController::class)->index($request);
    }

    $query = Vacancy::with('institution.user')->open();

    // Apply Search & Filters
    if ($request->filled('subject')) {
        $query->filterBySubject($request->subject);
    }
    if ($request->filled('location')) {
        $query->filterByLocation($request->location);
    }
    if ($request->filled('category')) {
        $category = $request->category;
        if ($category !== 'all') {
            $query->where(function($q) use ($category) {
                $q->where('subject', 'like', "%{$category}%")
                  ->orWhere('title', 'like', "%{$category}%")
                  ->orWhere('description', 'like', "%{$category}%");
            });
        }
    }

    // Apply Sort
    $sort = $request->input('sort', 'latest');
    if ($sort === 'salary_high') {
        $query->orderBy('salary_max', 'desc');
    } elseif ($sort === 'deadline_soon') {
        $query->orderBy('deadline', 'asc');
    } else {
        $query->latest();
    }

    $allowedLimits = [10, 25, 50, 100, 500];
    $limit = intval($request->input('per_page', session('per_page_public_vacs', 25)));
    if (!in_array($limit, $allowedLimits)) $limit = 25;
    session(['per_page_public_vacs' => $limit]);

    $vacancies = $query->paginate($limit)->withQueryString();

    return view('welcome', compact('vacancies', 'limit'));
})->name('home');


// 2. Demo Auth Routes
Route::get('/login', function () {
    return redirect()->route('login-demo');
})->name('login');
Route::get('/login-demo', [LoginDemoController::class, 'show'])->name('login-demo');
Route::post('/login-demo', [LoginDemoController::class, 'submitLogin'])->name('login.submit');
Route::get('/login-quick', [LoginDemoController::class, 'login'])->name('login-quick');
Route::get('/logout', [LoginDemoController::class, 'logout'])->name('logout');
Route::get('/signup', [LoginDemoController::class, 'showSignUp'])->name('signup');
Route::post('/signup', [LoginDemoController::class, 'signUp'])->name('signup.submit');

// 3. Dynamic Theme Route
Route::get('/set-theme', [ThemeController::class, 'setTheme'])->name('set-theme');

// 4. Job Board & Applications
Route::get('/vacancies', function (Request $request) {
    return redirect()->route('home', $request->query());
})->name('vacancies.index');
Route::get('/vacancy/{vacancy}', [VacancyController::class, 'show'])->name('vacancies.show');
Route::post('/vacancy/{vacancy}/apply', [VacancyController::class, 'apply'])->name('vacancies.apply');

// 4.5 CV File Viewer Routes (Inline with system UI)
Route::get('/raw-cv/{filename}', function ($filename) {
    $path = storage_path('app/public/cvs/' . $filename);
    if (!\Illuminate\Support\Facades\File::exists($path)) {
        abort(404, 'CV file not found.');
    }
    return response()->file($path);
})->name('cv.raw');

Route::get('/view-cv/{filename}', function ($filename) {
    $path = storage_path('app/public/cvs/' . $filename);
    if (!\Illuminate\Support\Facades\File::exists($path)) {
        abort(404, 'CV file not found.');
    }
    return view('cv-viewer', compact('filename'));
})->name('cv.view');


// 5. Secure Signed Routes for Confidential Recommendation Letters
Route::get('/referee/upload/{token}', [RefereeController::class, 'showForm'])->name('referee.upload-form');
Route::post('/referee/upload/{token}', [RefereeController::class, 'submit'])->name('referee.submit');
Route::get('/referee/success/{token}', [RefereeController::class, 'success'])->name('referee.success');

Route::get('/refresh-csrf', function() {
    return response()->json(['token' => csrf_token()]);
});

Route::get('/realtime/check', function() {
    $lastNotificationId = (int) request()->input('last_notification_id', 0);
    $lastVacancyId = (int) request()->input('last_vacancy_id', 0);

    $hash = '';
    $notification = null;
    $vacancy = null;
    
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin()) {
            // Admin Hash
            $hash = md5(\App\Models\User::count() . '-' . \App\Models\Vacancy::count() . '-' . \App\Models\Application::count());
        } elseif ($user->isInstitution()) {
            // Recruiter Hash
            $inst = $user->institution;
            if ($inst) {
                $vacs = \App\Models\Vacancy::where('institution_id', $inst->id)->pluck('id');
                $hash = md5(
                    \App\Models\Vacancy::where('institution_id', $inst->id)->count() . '-' .
                    \App\Models\Application::whereIn('vacancy_id', $vacs)->count() . '-' .
                    implode('-', \App\Models\Application::whereIn('vacancy_id', $vacs)->pluck('status')->toArray())
                );
            }
        } elseif ($user->isCandidate()) {
            // Candidate Hash
            $hash = md5(
                \App\Models\Application::where('candidate_id', $user->id)->count() . '-' .
                implode('-', \App\Models\Application::where('candidate_id', $user->id)->pluck('status')->toArray())
            );
        }

        // Check for new notification if parameter is provided and greater than 0
        if ($lastNotificationId > 0 && request()->has('last_notification_id')) {
            $newNotification = \App\Models\Notification::where('user_id', $user->id)
                ->where('id', '>', $lastNotificationId)
                ->latest()
                ->first();
            if ($newNotification) {
                $notification = [
                    'id' => $newNotification->id,
                    'title' => $newNotification->title,
                    'message' => $newNotification->message,
                    'type' => $newNotification->type,
                    'created_at_human' => '(' . $newNotification->created_at->format('h:i:s A, d M y ') . ')'
                ];
            }
        }
    } else {
        // Guest Hash (Welcome page openings count)
        $hash = md5(\App\Models\Vacancy::open()->count());
    }

    // Check for new vacancy (applicable to candidates, admins and guest users)
    if ($lastVacancyId > 0 && request()->has('last_vacancy_id') && (!auth()->check() || auth()->user()->isCandidate() || auth()->user()->isAdmin())) {
        $newVacancy = \App\Models\Vacancy::open()
            ->where('id', '>', $lastVacancyId)
            ->with('institution')
            ->latest()
            ->first();

        if ($newVacancy) {
            $vacancy = [
                'id' => $newVacancy->id,
                'title' => $newVacancy->title,
                'company' => $newVacancy->institution ? $newVacancy->institution->company_name : 'Confidential Partner Brand'
            ];
        }
    }

    return response()->json([
        'hash' => $hash,
        'notification' => $notification,
        'vacancy' => $vacancy
    ]);
})->name('realtime.check');


// 6. Admin Panel settings (SaaS customization)

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/settings', [AdminController::class, 'index'])->name('admin.settings');
    Route::post('/admin/settings', [AdminController::class, 'update'])->name('admin.settings.update');
    Route::post('/admin/clear-cache', [AdminController::class, 'clearCache'])->name('admin.clear-cache');
    Route::post('/admin/storage-link', function() {
        if (!function_exists('symlink')) {
            return back()->with('error', '❌ symlink() function is disabled on this server. Storage files will be served via fallback router.');
        }
        try {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            if (str_contains(strtolower($output), 'fail') || str_contains(strtolower($output), 'could not') || str_contains(strtolower($output), 'error')) {
                return back()->with('error', '❌ Failed to create symbolic link. Command Output: ' . trim($output) . ' (Note: Files are still served securely via fallback router)');
            }
            
            $linkPath = public_path('storage');
            if (!file_exists($linkPath) && !is_link($linkPath)) {
                return back()->with('error', '❌ Artisan command completed, but symbolic link does not exist on filesystem. (Note: Files are still served securely via fallback router)');
            }
            
            return back()->with('success', '🔗 Storage symbolic link created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', '❌ Failed to create link: ' . $e->getMessage() . ' (Note: Files are still served securely via fallback router)');
        }
    })->name('admin.storage-link');

    // Impersonate Routes
    Route::post('/admin/impersonate/{id}', [AdminController::class, 'impersonate'])->name('admin.impersonate');

    // CRUD & Restore Routes
    Route::post('/admin/user/store', [AdminController::class, 'storeUser'])->name('admin.user.store');
    Route::post('/admin/user/{id}/update', [AdminController::class, 'updateUser'])->name('admin.user.update');
    Route::delete('/admin/user/{id}', [AdminController::class, 'deleteUser'])->name('admin.user.delete');
    Route::post('/admin/user/{id}/restore', [AdminController::class, 'restoreUser'])->name('admin.user.restore');

    Route::post('/admin/user/{id}/toggle-verification', [AdminController::class, 'toggleVerification'])->name('admin.user.toggle-verification');
    Route::post('/admin/vacancy/store', [AdminController::class, 'storeVacancy'])->name('admin.vacancy.store');
    Route::post('/admin/vacancy/{id}/update', [AdminController::class, 'updateVacancy'])->name('admin.vacancy.update');
    Route::post('/admin/vacancy/{id}/toggle-status', [AdminController::class, 'toggleVacancyStatus'])->name('admin.vacancy.toggle-status');
    Route::delete('/admin/vacancy/{id}', [AdminController::class, 'deleteVacancy'])->name('admin.vacancy.delete');
    Route::post('/admin/vacancy/{id}/restore', [AdminController::class, 'restoreVacancy'])->name('admin.vacancy.restore');

    // WhatsApp Sandbox
    Route::post('/admin/whatsapp/test-send', [AdminController::class, 'sendTestWhatsApp'])->name('admin.whatsapp.test-send');

    // Admin Advanced Faculty Sourcing Pipeline Moderation
    Route::post('/admin/application/{id}/shortlist', [AdminController::class, 'shortlistApplication'])->name('admin.application.shortlist');
    Route::post('/admin/application/{id}/unlock-approve', [AdminController::class, 'unlockApprove'])->name('admin.application.unlock-approve');
    Route::delete('/admin/application/{id}', [AdminController::class, 'deleteApplication'])->name('admin.application.delete');
    Route::post('/admin/application/{id}/restore', [AdminController::class, 'restoreApplication'])->name('admin.application.restore');
});

// 7. Recruiter Dashboard & Funnel Pipeline Management

Route::middleware(['auth', 'role:institution'])->group(function () {
    Route::get('/recruiter/dashboard', [RecruiterController::class, 'dashboard'])->name('recruiter.dashboard');
    Route::post('/recruiter/vacancy', [RecruiterController::class, 'postVacancy'])->name('recruiter.post-vacancy');
    Route::get('/recruiter/vacancy/{vacancy}/edit', [RecruiterController::class, 'editVacancy'])->name('recruiter.vacancy.edit');
    Route::post('/recruiter/vacancy/{vacancy}/update', [RecruiterController::class, 'updateVacancy'])->name('recruiter.vacancy.update');
    Route::post('/recruiter/application/{application}/unlock', [RecruiterController::class, 'requestUnlock'])->name('recruiter.request-unlock');
    Route::post('/recruiter/application/{application}/pipeline', [RecruiterController::class, 'updatePipeline'])->name('recruiter.update-pipeline');
    Route::post('/recruiter/profile', [RecruiterController::class, 'updateProfile'])->name('recruiter.profile.update');
});

// 9. Real-Time Notification Center routes
Route::middleware(['auth'])->group(function () {
    Route::post('/admin/impersonate-leave', [AdminController::class, 'leaveImpersonate'])->name('admin.impersonate-leave');
    Route::get('/admin/impersonate-leave', function () {
        return redirect()->route('home');
    });

    Route::post('/notifications/read-all', function() {
        \App\Models\Notification::where('user_id', auth()->id())->update(['is_read' => true]);
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'All notifications marked as read.');
    })->name('notifications.read-all');

    Route::post('/notifications/{notification}/toggle-read', function(\App\Models\Notification $notification) {
        if ($notification->user_id === auth()->id()) {
            $notification->is_read = !$notification->is_read;
            $notification->save();
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'is_read' => $notification->is_read]);
            }
        }
        return back();
    })->name('notifications.toggle-read');

    Route::post('/notifications/settings', function() {
        $user = auth()->user();
        $settings = request()->validate([
            'in_app' => 'required|boolean',
            'whatsapp' => 'required|boolean',
            'email' => 'required|boolean',
            'dnd' => 'required|boolean',
            'dnd_start' => 'required|string|max:5',
            'dnd_end' => 'required|string|max:5',
        ]);
        $user->notification_settings = $settings;
        $user->save();
        return response()->json(['success' => true, 'settings' => $user->getNotificationSettings()]);
    })->name('notifications.save-settings');

    Route::get('/notifications/{notification}/read', function(\App\Models\Notification $notification) {
        if ($notification->user_id === auth()->id()) {
            $notification->is_read = true;
            $notification->save();
            if ($notification->action_url) {
                return redirect($notification->action_url);
            }
        }
        return back();
    })->name('notifications.read');

    // 10. Faculty Candidate Dashboard route
    Route::middleware(['role:candidate'])->group(function () {
        Route::get('/faculty/dashboard', [FacultyController::class, 'dashboard'])->name('faculty.dashboard');
        Route::get('/faculty/profile', [FacultyController::class, 'editProfile'])->name('faculty.profile');
        Route::post('/faculty/profile', [FacultyController::class, 'updateProfile'])->name('faculty.profile.update');
    });
});



