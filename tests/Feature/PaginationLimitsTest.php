<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Institution;
use App\Models\Vacancy;
use App\Models\Application;
use App\Models\CandidateProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_vacancies_index_row_limit_and_memory_retention()
    {
        // Create 30 vacancies
        $inst = Institution::create([
            'user_id' => User::factory()->create(['role' => 'institution'])->id,
            'company_name' => 'Test Institution',
            'type' => 'coaching_center',
            'location' => 'Kota',
        ]);

        for ($i = 0; $i < 30; $i++) {
            Vacancy::create([
                'institution_id' => $inst->id,
                'title' => "Vacancy Title {$i}",
                'subject' => 'Physics',
                'target_class' => 'Class 12',
                'experience_required' => '3 years',
                'location' => 'Kota',
                'salary_min' => 1000000,
                'salary_max' => 1500000,
                'description' => 'Test description',
                'deadline' => now()->addDays(10)->toDateString(),
            ]);
        }

        // 1. Initial request with default row limit (should be 25)
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $this->assertEquals(25, session('per_page_public_vacs'));
        $response->assertSee('Showing 1-25/30');

        // 2. Request with custom row limit = 10
        $response = $this->get(route('home', ['per_page' => 10]));
        $response->assertStatus(200);
        $this->assertEquals(10, session('per_page_public_vacs'));
        $response->assertSee('Showing 1-10/30');

        // 3. Request without per_page parameter (should retain 10 from session)
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $this->assertEquals(10, session('per_page_public_vacs'));
        $response->assertSee('Showing 1-10/30');

        // 4. Request with invalid row limit (should fallback to 25)
        $response = $this->get(route('home', ['per_page' => 999]));
        $response->assertStatus(200);
        $this->assertEquals(25, session('per_page_public_vacs'));
        $response->assertSee('Showing 1-25/30');
    }

    public function test_faculty_dashboard_row_limit_and_memory_retention()
    {
        $candidateUser = User::create([
            'name' => 'Prof. Verma',
            'email' => 'verma@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $candidateUser->role = 'candidate';
        $candidateUser->save();

        CandidateProfile::create([
            'user_id' => $candidateUser->id,
            'type' => 'coaching',
            'current_designation' => 'Physics Faculty',
            'highest_degree' => 'M.Tech',
            'subjects_taught' => ['Physics'],
            'experience_summary' => 'Teacher',
            'experience_years' => 5,
            'current_ctc' => 1000000,
            'expected_ctc' => 1200000,
            'preferred_location' => 'Kota'
        ]);

        $inst = Institution::create([
            'user_id' => User::factory()->create(['role' => 'institution'])->id,
            'company_name' => 'Test Institution',
            'type' => 'coaching_center',
            'location' => 'Kota',
        ]);

        $v = Vacancy::create([
            'institution_id' => $inst->id,
            'title' => 'Physics Teacher',
            'subject' => 'Physics',
            'target_class' => 'Class 12',
            'experience_required' => '3 years',
            'location' => 'Kota',
            'salary_min' => 1000000,
            'salary_max' => 1500000,
            'description' => 'Test description',
            'deadline' => now()->addDays(10)->toDateString(),
        ]);

        // Create 30 applications
        for ($i = 0; $i < 30; $i++) {
            Application::create([
                'candidate_id' => $candidateUser->id,
                'vacancy_id' => $v->id,
                'status' => 'applied',
                'cover_letter' => "Cover letter {$i}",
            ]);
        }

        $this->actingAs($candidateUser);

        // 1. Initial request with default row limit (should be 25)
        $response = $this->get(route('faculty.dashboard'));
        $response->assertStatus(200);
        $this->assertEquals(25, session('per_page_faculty_apps'));
        $response->assertSee('Showing 1-25/30');

        // 2. Request with custom limit = 10
        $response = $this->get(route('faculty.dashboard', ['per_page_applications' => 10]));
        $response->assertStatus(200);
        $this->assertEquals(10, session('per_page_faculty_apps'));
        $response->assertSee('Showing 1-10/30');

        // 3. Request without param should retain session = 10
        $response = $this->get(route('faculty.dashboard'));
        $response->assertStatus(200);
        $this->assertEquals(10, session('per_page_faculty_apps'));
        $response->assertSee('Showing 1-10/30');
    }

    public function test_recruiter_dashboard_row_limit_and_memory_retention()
    {
        $recruiterUser = User::create([
            'name' => 'Recruiter Brand',
            'email' => 'brand@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $recruiterUser->role = 'institution';
        $recruiterUser->save();

        $institution = Institution::create([
            'user_id' => $recruiterUser->id,
            'company_name' => 'Recruiter Corp',
            'type' => 'coaching_center',
            'location' => 'Kota',
        ]);

        // Create 30 vacancies
        for ($i = 0; $i < 30; $i++) {
            Vacancy::create([
                'institution_id' => $institution->id,
                'title' => "Vacancy Title {$i}",
                'subject' => 'Physics',
                'target_class' => 'Class 12',
                'experience_required' => '3 years',
                'location' => 'Kota',
                'salary_min' => 1000000,
                'salary_max' => 1500000,
                'description' => 'Test description',
                'deadline' => now()->addDays(10)->toDateString(),
            ]);
        }

        // Sourced pipeline applications
        $v = Vacancy::where('institution_id', $institution->id)->first();
        for ($i = 0; $i < 30; $i++) {
            $cand = User::create([
                'name' => "Candidate {$i}",
                'email' => "cand{$i}@gmail.com",
                'password' => bcrypt('password'),
            ]);
            $cand->role = 'candidate';
            $cand->save();

            CandidateProfile::create([
                'user_id' => $cand->id,
                'type' => 'coaching',
                'current_designation' => 'Teacher',
                'highest_degree' => 'M.Sc',
                'subjects_taught' => ['Physics'],
                'experience_summary' => 'Teacher',
                'experience_years' => 5,
                'current_ctc' => 800000,
                'expected_ctc' => 1200000,
                'preferred_location' => 'Kota'
            ]);

            Application::create([
                'candidate_id' => $cand->id,
                'vacancy_id' => $v->id,
                'status' => 'shortlisted_by_admin',
                'cover_letter' => "Cover letter {$i}",
            ]);
        }

        $this->actingAs($recruiterUser);

        // Test vacancies pagination row limits
        // 1. Initial request with default row limit (should be 25)
        $response = $this->get(route('recruiter.dashboard', ['tab' => 'my-vacancies']));
        $response->assertStatus(200);
        $this->assertEquals(25, session('per_page_recruiter_vacs'));
        $response->assertSee('Showing 1-25/30');

        // 2. Change vacancies row limit to 10
        $response = $this->get(route('recruiter.dashboard', ['per_page_vacs' => 10]));
        $response->assertStatus(200);
        $this->assertEquals(10, session('per_page_recruiter_vacs'));
        $response->assertSee('Showing 1-10/30');

        // Test apps pagination row limits
        // 1. Initial request with default row limit (should be 25)
        $response = $this->get(route('recruiter.dashboard', ['tab' => 'pipeline']));
        $response->assertStatus(200);
        $this->assertEquals(25, session('per_page_recruiter_apps'));
        $response->assertSee('Showing 1-25/30');

        // 2. Change apps row limit to 10
        $response = $this->get(route('recruiter.dashboard', ['per_page_apps' => 10]));
        $response->assertStatus(200);
        $this->assertEquals(10, session('per_page_recruiter_apps'));
        $response->assertSee('Showing 1-10/30');
    }

    public function test_admin_settings_dashboard_row_limits_retention()
    {
        $adminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $adminUser->role = 'admin';
        $adminUser->save();

        $this->actingAs($adminUser);

        // Create 30 regular users
        for ($i = 0; $i < 30; $i++) {
            User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@gmail.com",
                'password' => bcrypt('password'),
                'role' => 'candidate',
            ]);
        }

        // Test admin users pagination limits
        $response = $this->get(route('admin.settings', ['tab' => 'users']));
        $response->assertStatus(200);
        $this->assertEquals(25, session('per_page_users'));
        // Wait, showing count in users tab is 31 total (including admin)
        $response->assertSee('Showing 1-25/31');

        $response = $this->get(route('admin.settings', ['per_page_users' => 10, 'tab' => 'users']));
        $response->assertStatus(200);
        $this->assertEquals(10, session('per_page_users'));
        $response->assertSee('Showing 1-10/31');
    }
}
