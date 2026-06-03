<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vacancy;
use App\Models\Application;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealtimeSyncTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest user realtime check returns correct hash of open vacancies.
     */
    public function test_guest_realtime_check_returns_vacancies_count_hash()
    {
        $response = $this->get(route('realtime.check'));
        $response->assertStatus(200);
        $initialHash = $response->json('hash');
        $this->assertNotEmpty($initialHash);

        // Post a vacancy
        $recruiterUser = new User([
            'name' => 'Recruiter User',
            'email' => 'recruiter_test@example.com',
            'password' => bcrypt('password'),
        ]);
        $recruiterUser->role = 'institution';
        $recruiterUser->save();

        $institution = Institution::create([
            'user_id' => $recruiterUser->id,
            'company_name' => 'Test Institution LLC',
            'type' => 'coaching_center',
            'location' => 'Kota',
        ]);

        $vacancy = Vacancy::create([
            'institution_id' => $institution->id,
            'title' => 'Chemistry Teacher',
            'subject' => 'Chemistry',
            'target_class' => 'IIT-JEE',
            'experience_required' => '3 years',
            'location' => 'Kota',
            'salary_min' => 50000,
            'salary_max' => 80000,
            'deadline' => now()->addDays(5)->toDateString(),
            'status' => 'open',
            'description' => 'Test description',
        ]);

        // Re-check
        $response = $this->get(route('realtime.check'));
        $newHash = $response->json('hash');
        
        $this->assertNotEquals($initialHash, $newHash);
    }

    /**
     * Test admin user realtime check returns hash of overall data stats.
     */
    public function test_admin_realtime_check_hashes_correctly()
    {
        $admin = new User([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->role = 'admin';
        $admin->save();

        $this->actingAs($admin);

        $response = $this->get(route('realtime.check'));
        $response->assertStatus(200);
        $hash1 = $response->json('hash');

        // Create a user
        $user = new User([
            'name' => 'Test Candidate',
            'email' => 'candidate_test@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->role = 'candidate';
        $user->save();

        $response = $this->get(route('realtime.check'));
        $hash2 = $response->json('hash');

        $this->assertNotEquals($hash1, $hash2);
    }

    /**
     * Test candidate user realtime check hashes their specific application statuses.
     */
    public function test_candidate_realtime_check_hashes_correctly()
    {
        $candidate = new User([
            'name' => 'Candidate User',
            'email' => 'candidate_t@example.com',
            'password' => bcrypt('password'),
        ]);
        $candidate->role = 'candidate';
        $candidate->save();

        $this->actingAs($candidate);

        $response = $this->get(route('realtime.check'));
        $response->assertStatus(200);
        $hash1 = $response->json('hash');

        // Create application
        $recruiter = new User([
            'name' => 'Recruiter',
            'email' => 'rec@example.com',
            'password' => bcrypt('password'),
        ]);
        $recruiter->role = 'institution';
        $recruiter->save();

        $institution = Institution::create([
            'user_id' => $recruiter->id,
            'company_name' => 'Test LLC',
            'type' => 'school',
            'location' => 'Kota',
        ]);

        $vacancy = Vacancy::create([
            'institution_id' => $institution->id,
            'title' => 'Math Teacher',
            'subject' => 'Math',
            'target_class' => 'CBSE 12',
            'experience_required' => '2 years',
            'location' => 'Kota',
            'salary_min' => 40000,
            'salary_max' => 60000,
            'deadline' => now()->addDays(5)->toDateString(),
            'status' => 'open',
            'description' => 'Test',
        ]);

        $app = Application::create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => $candidate->id,
            'cover_letter' => 'Hello',
            'status' => 'applied',
        ]);

        $response = $this->get(route('realtime.check'));
        $hash2 = $response->json('hash');
        $this->assertNotEquals($hash1, $hash2);

        // Change application status
        $app->status = 'shortlisted_by_admin';
        $app->save();

        $response = $this->get(route('realtime.check'));
        $hash3 = $response->json('hash');
        $this->assertNotEquals($hash2, $hash3);
    }
}
