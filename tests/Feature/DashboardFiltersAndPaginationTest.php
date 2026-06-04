<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Institution;
use App\Models\Vacancy;
use App\Models\Application;
use App\Models\CandidateProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFiltersAndPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_dashboard_search_and_sorting()
    {
        $candidateUser = User::create([
            'name' => 'Prof. Amit Verma',
            'email' => 'faculty@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $candidateUser->role = 'candidate';
        $candidateUser->save();

        CandidateProfile::create([
            'user_id' => $candidateUser->id,
            'type' => 'coaching',
            'current_designation' => 'Senior Physics Faculty',
            'highest_degree' => 'M.Tech IIT',
            'subjects_taught' => ['Physics'],
            'experience_summary' => 'Ex-Allen teacher',
            'experience_years' => 8,
            'current_ctc' => 1500000,
            'expected_ctc' => 2000000,
            'preferred_location' => 'Kota'
        ]);

        $instA = Institution::create([
            'user_id' => User::factory()->create(['role' => 'institution'])->id,
            'company_name' => 'Alpha Coaching',
            'type' => 'coaching_center',
            'location' => 'Kota',
        ]);

        $instB = Institution::create([
            'user_id' => User::factory()->create(['role' => 'institution'])->id,
            'company_name' => 'Beta Classes',
            'type' => 'coaching_center',
            'location' => 'Kota',
        ]);

        // Vacancy 1
        $v1 = Vacancy::create([
            'institution_id' => $instA->id,
            'title' => 'Senior Physics HOD',
            'subject' => 'Physics',
            'target_class' => 'IIT-JEE',
            'experience_required' => '5 years',
            'location' => 'Kota',
            'salary_min' => 1000000,
            'salary_max' => 2000000,
            'description' => 'Need physics HOD to teach mechanics.',
            'deadline' => now()->addDays(5)->toDateString(),
        ]);

        // Vacancy 2
        $v2 = Vacancy::create([
            'institution_id' => $instB->id,
            'title' => 'Chemistry Teacher',
            'subject' => 'Chemistry',
            'target_class' => 'NEET',
            'experience_required' => '5 years',
            'location' => 'Kota',
            'salary_min' => 1000000,
            'salary_max' => 2000000,
            'description' => 'Need physics HOD to teach mechanics.',
            'deadline' => now()->addDays(5)->toDateString(),
        ]);

        // Vacancy 3
        $v3 = Vacancy::create([
            'institution_id' => $instB->id,
            'title' => 'Junior Physics Coach',
            'subject' => 'Physics',
            'target_class' => 'IIT-JEE',
            'experience_required' => '2 years',
            'location' => 'Kota',
            'salary_min' => 500000,
            'salary_max' => 1000000,
            'description' => 'Need physics HOD to teach mechanics.',
            'deadline' => now()->addDays(5)->toDateString(),
        ]);

        // Create applications with statuses that can be ordered
        $app1 = Application::create([
            'candidate_id' => $candidateUser->id,
            'vacancy_id' => $v1->id,
            'status' => 'applied',
            'cover_letter' => 'Physics candidate',
        ]);

        $app2 = Application::create([
            'candidate_id' => $candidateUser->id,
            'vacancy_id' => $v2->id,
            'status' => 'shortlisted_by_admin',
            'cover_letter' => 'Chemistry candidate',
        ]);

        $app3 = Application::create([
            'candidate_id' => $candidateUser->id,
            'vacancy_id' => $v3->id,
            'status' => 'rejected',
            'cover_letter' => 'Physics junior candidate',
        ]);

        $this->actingAs($candidateUser);

        // 1. Test search vacancy title/subject
        $response = $this->get(route('faculty.dashboard', ['search_app' => 'Physics']));
        $response->assertStatus(200);
        $response->assertSee('Senior Physics HOD');
        $response->assertSee('Junior Physics Coach');
        $response->assertDontSee('Chemistry Teacher');

        // 2. Test search company name
        $response = $this->get(route('faculty.dashboard', ['search_app' => 'Beta']));
        $response->assertStatus(200);
        $response->assertSee('Chemistry Teacher');
        $response->assertSee('Junior Physics Coach');
        $response->assertDontSee('Senior Physics HOD');

        // 3. Test sorting by status_asc
        $response = $this->get(route('faculty.dashboard', ['sort_app' => 'status_asc']));
        $response->assertStatus(200);
        
        // 4. Test pagination works
        // Let's add 8 more applications to make it 11 total (page size is 10)
        for ($i = 0; $i < 8; $i++) {
            Application::create([
                'candidate_id' => $candidateUser->id,
                'vacancy_id' => $v1->id,
                'status' => 'applied',
                'cover_letter' => 'Extra application ' . $i,
            ]);
        }

        $response = $this->get(route('faculty.dashboard', ['apps_page' => 2, 'per_page_applications' => 10]));
        $response->assertStatus(200);
        $response->assertSee('Showing 11-11/11');
    }

    public function test_recruiter_dashboard_vacancies_search_sorting_and_pagination()
    {
        $recruiterUser = User::create([
            'name' => 'Dr. Vidyarthi',
            'email' => 'recruiter@sourcing.com',
            'password' => bcrypt('password'),
        ]);
        $recruiterUser->role = 'institution';
        $recruiterUser->save();

        $institution = Institution::create([
            'user_id' => $recruiterUser->id,
            'company_name' => 'Vidyapeeth Classes',
            'type' => 'coaching_center',
            'location' => 'Kota',
        ]);

        // Create 12 vacancies
        for ($i = 1; $i <= 12; $i++) {
            Vacancy::create([
                'institution_id' => $institution->id,
                'title' => "JEE Physics Vacancy {$i}",
                'subject' => $i % 2 === 0 ? 'Physics' : 'Chemistry',
                'target_class' => $i % 3 === 0 ? 'Class 10' : 'Class 12',
                'experience_required' => '3 years',
                'location' => 'Kota',
                'salary_min' => 1000000,
                'salary_max' => 1500000,
                'description' => 'Need physics HOD to teach mechanics.',
                'deadline' => now()->addDays(10)->toDateString(),
            ]);
        }

        $this->actingAs($recruiterUser);

        // 1. Search by subject
        $response = $this->get(route('recruiter.dashboard', ['search_vac' => 'Chemistry', 'per_page_vacs' => 10]));
        $response->assertStatus(200);
        $response->assertSee('Showing 1-6/6');

        // 2. Search by target_class
        $response = $this->get(route('recruiter.dashboard', ['search_vac' => 'Class 10', 'per_page_vacs' => 10]));
        $response->assertStatus(200);
        $response->assertSee('Showing 1-4/4');

        // 3. Test sorting title A-Z
        $response = $this->get(route('recruiter.dashboard', ['sort_vac' => 'title', 'per_page_vacs' => 10]));
        $response->assertStatus(200);

        // 4. Test pagination using vacs_page=2
        $response = $this->get(route('recruiter.dashboard', ['vacs_page' => 2, 'per_page_vacs' => 10]));
        $response->assertStatus(200);
        $response->assertSee('Showing 11-12/12');
    }

    public function test_recruiter_dashboard_pipeline_search_sorting_and_pagination()
    {
        $recruiterUser = User::create([
            'name' => 'Dr. Vidyarthi',
            'email' => 'recruiter@sourcing.com',
            'password' => bcrypt('password'),
        ]);
        $recruiterUser->role = 'institution';
        $recruiterUser->save();

        $institution = Institution::create([
            'user_id' => $recruiterUser->id,
            'company_name' => 'Vidyapeeth Classes',
            'type' => 'coaching_center',
            'location' => 'Kota',
        ]);

        $v = Vacancy::create([
            'institution_id' => $institution->id,
            'title' => 'Biology HOD',
            'subject' => 'Biology',
            'target_class' => 'NEET',
            'experience_required' => '8 years',
            'location' => 'Kota',
            'salary_min' => 1500000,
            'salary_max' => 2500000,
            'description' => 'Biology description',
            'deadline' => now()->addDays(10)->toDateString(),
        ]);

        // Create 12 candidate users, profiles and applications
        for ($i = 1; $i <= 12; $i++) {
            $cand = User::create([
                'name' => "Candidate Name {$i}",
                'email' => "candidate{$i}@gmail.com",
                'password' => bcrypt('password'),
            ]);
            $cand->role = 'candidate';
            $cand->save();

            CandidateProfile::create([
                'user_id' => $cand->id,
                'type' => 'coaching',
                'current_designation' => 'Biology Teacher',
                'highest_degree' => 'M.Sc',
                'subjects_taught' => ['Biology'],
                'experience_summary' => 'Teacher',
                'experience_years' => 5,
                'current_ctc' => 800000,
                'expected_ctc' => 1200000,
                'preferred_location' => 'Kota'
            ]);

            // Create application (must be shortlisted by admin to appear in pipeline)
            Application::create([
                'candidate_id' => $cand->id,
                'vacancy_id' => $v->id,
                'status' => 'shortlisted_by_admin',
                'cover_letter' => "Cover letter {$i}",
                'demo_rating' => $i
            ]);
        }

        $this->actingAs($recruiterUser);

        // 1. Search candidate name
        $response = $this->get(route('recruiter.dashboard', ['search_app' => 'Candidate Name 5', 'per_page_apps' => 10]));
        $response->assertStatus(200);
        $response->assertSee('Candidate Name 5');

        // 2. Search vacancy title
        $response = $this->get(route('recruiter.dashboard', ['search_app' => 'Biology', 'per_page_apps' => 10]));
        $response->assertStatus(200);
        $response->assertSee('Showing 1-10/12');

        // 3. Test rating sorting (descending)
        $response = $this->get(route('recruiter.dashboard', ['sort_app' => 'rating', 'per_page_apps' => 10]));
        $response->assertStatus(200);

        // 4. Test pagination using apps_page=2
        $response = $this->get(route('recruiter.dashboard', ['apps_page' => 2, 'per_page_apps' => 10]));
        $response->assertStatus(200);
        $response->assertSee('Showing 11-12/12');
    }
}
