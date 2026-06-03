<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\RefereeController;
use App\Http\Controllers\LoginDemoController;

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

    $vacancies = $query->latest()->get();

    return view('welcome', compact('vacancies'));
})->name('home');


// 2. Demo Auth Routes
Route::get('/login-demo', [LoginDemoController::class, 'show'])->name('login-demo');
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

// 5. Secure Signed Routes for Confidential Recommendation Letters
Route::get('/referee/upload/{token}', [RefereeController::class, 'showForm'])->name('referee.upload-form');
Route::post('/referee/upload/{token}', [RefereeController::class, 'submit'])->name('referee.submit');
Route::get('/referee/success/{token}', [RefereeController::class, 'success'])->name('referee.success');

Route::get('/refresh-csrf', function() {
    return response()->json(['token' => csrf_token()]);
});

// 6. Admin Panel settings (SaaS customization)
use App\Http\Controllers\AdminController;
Route::get('/admin/settings', [AdminController::class, 'index'])->name('admin.settings');
Route::post('/admin/settings', [AdminController::class, 'update'])->name('admin.settings.update');
Route::post('/admin/clear-cache', [AdminController::class, 'clearCache'])->name('admin.clear-cache');

// Impersonate Routes
Route::post('/admin/impersonate/{id}', [AdminController::class, 'impersonate'])->name('admin.impersonate');
Route::post('/admin/impersonate-leave', [AdminController::class, 'leaveImpersonate'])->name('admin.impersonate-leave');

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

// 7. Recruiter Dashboard & Funnel Pipeline Management
use App\Http\Controllers\RecruiterController;
Route::get('/recruiter/dashboard', [RecruiterController::class, 'dashboard'])->name('recruiter.dashboard');
Route::post('/recruiter/vacancy', [RecruiterController::class, 'postVacancy'])->name('recruiter.post-vacancy');
Route::post('/recruiter/application/{application}/unlock', [RecruiterController::class, 'requestUnlock'])->name('recruiter.request-unlock');
Route::post('/recruiter/application/{application}/pipeline', [RecruiterController::class, 'updatePipeline'])->name('recruiter.update-pipeline');

// 8. Admin Advanced Faculty Sourcing Pipeline Moderation
Route::post('/admin/application/{id}/shortlist', [AdminController::class, 'shortlistApplication'])->name('admin.application.shortlist');
Route::post('/admin/application/{id}/unlock-approve', [AdminController::class, 'unlockApprove'])->name('admin.application.unlock-approve');
Route::delete('/admin/application/{id}', [AdminController::class, 'deleteApplication'])->name('admin.application.delete');
Route::post('/admin/application/{id}/restore', [AdminController::class, 'restoreApplication'])->name('admin.application.restore');

// 9. Real-Time Notification Center routes
Route::post('/notifications/read-all', function() {
    if (auth()->check()) {
        \App\Models\Notification::where('user_id', auth()->id())->update(['is_read' => true]);
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }
    }
    return back()->with('success', 'All notifications marked as read.');
})->name('notifications.read-all');

Route::post('/notifications/{notification}/toggle-read', function(\App\Models\Notification $notification) {
    if (auth()->check() && $notification->user_id === auth()->id()) {
        $notification->is_read = !$notification->is_read;
        $notification->save();
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'is_read' => $notification->is_read]);
        }
    }
    return back();
})->name('notifications.toggle-read');

Route::post('/notifications/settings', function() {
    if (auth()->check()) {
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
    }
    return response()->json(['error' => 'Unauthorized'], 403);
})->name('notifications.save-settings');

Route::get('/notifications/{notification}/read', function(\App\Models\Notification $notification) {
    if (auth()->check() && $notification->user_id === auth()->id()) {
        $notification->is_read = true;
        $notification->save();
        if ($notification->action_url) {
            return redirect($notification->action_url);
        }
    }
    return back();
})->name('notifications.read');

// 10. Faculty Candidate Dashboard route
use App\Http\Controllers\FacultyController;
Route::get('/faculty/dashboard', [FacultyController::class, 'dashboard'])->name('faculty.dashboard');

