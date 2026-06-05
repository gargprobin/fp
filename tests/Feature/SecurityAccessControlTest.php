<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vacancy;
use App\Models\Application;
use App\Models\Institution;
use App\Models\CandidateProfile;
use App\Models\Notification;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure directories exist for mock CVs
        if (!File::isDirectory(storage_path('app/public/cvs'))) {
            File::makeDirectory(storage_path('app/public/cvs'), 0777, true, true);
        }

        // Write a mock CV file
        File::put(storage_path('app/public/cvs/Test_Candidate_CV.pdf'), 'Dummy PDF content');
    }

    protected function tearDown(): void
    {
        // Clean up mock CV file
        if (File::exists(storage_path('app/public/cvs/Test_Candidate_CV.pdf'))) {
            File::delete(storage_path('app/public/cvs/Test_Candidate_CV.pdf'));
        }

        parent::tearDown();
    }

    /**
     * Test `/login-quick` is allowed in production environment.
     */
    public function test_quick_login_allowed_in_production()
    {
        $this->artisan('config:clear');
        
        // Mock production environment
        app()->detectEnvironment(fn() => 'production');

        $response = $this->get(route('login-quick', ['role' => 'candidate']));
        $response->assertRedirect(route('faculty.dashboard'));
    }

    /**
     * Test `/login-quick` is allowed in local/testing environment.
     */
    public function test_quick_login_allowed_in_non_production()
    {
        // Default environment is 'testing'
        $response = $this->get(route('login-quick', ['role' => 'candidate']));
        $response->assertRedirect(route('faculty.dashboard'));
    }

    /**
     * Test public CV routes require authentication.
     */
    public function test_cv_routes_require_authentication()
    {
        $responseRaw = $this->get(route('cv.raw', ['filename' => 'Test_Candidate_CV.pdf']));
        $responseRaw->assertStatus(403);

        $responseView = $this->get(route('cv.view', ['filename' => 'Test_Candidate_CV.pdf']));
        $responseView->assertStatus(403);
    }

    /**
     * Test candidates can view their own CV.
     */
    public function test_candidate_can_view_own_cv()
    {
        $candidate = User::factory()->create(['role' => 'candidate']);
        CandidateProfile::create([
            'user_id' => $candidate->id,
            'type' => 'academic',
            'cv_path' => 'cvs/Test_Candidate_CV.pdf',
            'highest_degree' => 'Postgraduate',
            'subjects_taught' => ['General'],
            'experience_years' => 2
        ]);

        $this->actingAs($candidate);

        $responseRaw = $this->get(route('cv.raw', ['filename' => 'Test_Candidate_CV.pdf']));
        $responseRaw->assertStatus(200);

        $responseView = $this->get(route('cv.view', ['filename' => 'Test_Candidate_CV.pdf']));
        $responseView->assertStatus(200);
    }

    /**
     * Test recruiters are blocked from viewing candidate CVs by default.
     */
    public function test_recruiter_blocked_from_unapplied_candidate_cv()
    {
        $candidate = User::factory()->create(['role' => 'candidate']);
        CandidateProfile::create([
            'user_id' => $candidate->id,
            'type' => 'academic',
            'cv_path' => 'cvs/Test_Candidate_CV.pdf',
            'highest_degree' => 'Postgraduate',
            'subjects_taught' => ['General'],
            'experience_years' => 2
        ]);

        $recruiter = User::factory()->create(['role' => 'institution']);
        Institution::create([
            'user_id' => $recruiter->id,
            'company_name' => 'Recruiter College',
            'type' => 'school',
            'location' => 'Kota'
        ]);

        $this->actingAs($recruiter);

        $response = $this->get(route('cv.view', ['filename' => 'Test_Candidate_CV.pdf']));
        $response->assertStatus(403);
    }

    /**
     * Test recruiter access allowed after application unlock.
     */
    public function test_recruiter_access_allowed_only_when_unlocked()
    {
        $candidate = User::factory()->create(['role' => 'candidate']);
        CandidateProfile::create([
            'user_id' => $candidate->id,
            'type' => 'academic',
            'cv_path' => 'cvs/Test_Candidate_CV.pdf',
            'highest_degree' => 'Postgraduate',
            'subjects_taught' => ['General'],
            'experience_years' => 2
        ]);

        $recruiter = User::factory()->create(['role' => 'institution']);
        $institution = Institution::create([
            'user_id' => $recruiter->id,
            'company_name' => 'Recruiter College',
            'type' => 'school',
            'location' => 'Kota'
        ]);

        $vacancy = Vacancy::create([
            'institution_id' => $institution->id,
            'title' => 'Physics Teacher',
            'description' => 'Desc',
            'subject' => 'Physics',
            'target_class' => '12',
            'experience_required' => '2 years',
            'location' => 'Kota',
            'deadline' => now()->addDays(10)->toDateString(),
            'status' => 'open'
        ]);

        $application = Application::create([
            'candidate_id' => $candidate->id,
            'vacancy_id' => $vacancy->id,
            'cover_letter' => 'Letter',
            'status' => 'applied'
        ]);

        $this->actingAs($recruiter);

        // Blocked when status is 'applied' (masked)
        $response = $this->get(route('cv.view', ['filename' => 'Test_Candidate_CV.pdf']));
        $response->assertStatus(403);

        // Allowed when status is 'unlocked'
        $application->update(['status' => 'unlocked']);
        $responseAllowed = $this->get(route('cv.view', ['filename' => 'Test_Candidate_CV.pdf']));
        $responseAllowed->assertStatus(200);
    }

    /**
     * Test Open Redirection block on notification read.
     */
    public function test_open_redirection_block()
    {
        $user = User::factory()->create(['role' => 'candidate']);
        
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Test',
            'message' => 'Msg',
            'type' => 'info',
            'action_url' => 'https://malicious-external-site.com/steal',
            'is_read' => false
        ]);

        $this->actingAs($user);

        $response = $this->get(route('notifications.read', ['notification' => $notification->id]));
        $response->assertRedirect(route('home'));
    }

    /**
     * Test notification unique candidate lookup (Resolving name clash PII leak).
     */
    public function test_notification_candidate_unique_resolution_via_url()
    {
        // Fake HTTP client to assert payloads and prevent real API calls
        Http::fake();

        // Create two candidates with exact same name
        $candidate1 = User::factory()->create(['name' => 'Amit Verma', 'role' => 'candidate', 'phone' => '919999999991']);
        CandidateProfile::create(['user_id' => $candidate1->id, 'type' => 'academic', 'cv_path' => 'cvs/cv1.pdf', 'highest_degree' => 'PhD', 'subjects_taught' => ['Physics'], 'experience_years' => 5]);

        $candidate2 = User::factory()->create(['name' => 'Amit Verma', 'role' => 'candidate', 'phone' => '919999999992']);
        CandidateProfile::create(['user_id' => $candidate2->id, 'type' => 'academic', 'cv_path' => 'cvs/cv2.pdf', 'highest_degree' => 'MTech', 'subjects_taught' => ['Chemistry'], 'experience_years' => 2]);

        $recruiter = User::factory()->create(['role' => 'institution', 'phone' => '919999999993']);
        $institution = Institution::create(['user_id' => $recruiter->id, 'company_name' => 'Coaching', 'type' => 'coaching_center', 'location' => 'Kota']);

        $vacancy = Vacancy::create([
            'institution_id' => $institution->id,
            'title' => 'Teacher',
            'description' => 'Desc',
            'subject' => 'Physics',
            'target_class' => '12',
            'experience_required' => '2 years',
            'location' => 'Kota',
            'deadline' => now()->addDays(10)->toDateString(),
            'status' => 'open'
        ]);

        // Application is for candidate2
        $application = Application::create([
            'candidate_id' => $candidate2->id,
            'vacancy_id' => $vacancy->id,
            'cover_letter' => 'Letter',
            'status' => 'applied'
        ]);

        // Enable whatsapp logging and configure gateway provider
        SiteSetting::setVal('whatsapp_enabled', '1');
        SiteSetting::setVal('whatsapp_event_lineup_approved_recruiter', '1');
        SiteSetting::setVal('whatsapp_api_url', 'https://wa.rj19web.com/api/contact/send-template-message');

        // Trigger Notification::send.
        // Even though candidate 1 and 2 share the same name, passing the action URL with #app-{id} must resolve candidate 2 uniquely
        Notification::send(
            $recruiter->id,
            '🟢 Line Up Approved!',
            "Admin approved Line Up for Amit Verma for {$vacancy->title}. Contact details unlocked!",
            route('recruiter.dashboard') . '#app-' . $application->id,
            'success'
        );

        // Assert HTTP payload sent contains candidate 2 details (field_4 is candidate email, field_5 is candidate phone)
        Http::assertSent(function (\Illuminate\Http\Client\Request $request) use ($candidate2) {
            $data = $request->data();
            return isset($data['field_4']) && $data['field_4'] === $candidate2->email
                && isset($data['field_5']) && $data['field_5'] === $candidate2->phone;
        });
    }
}
