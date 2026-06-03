<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Institution;
use App\Models\Vacancy;
use App\Models\Application;
use App\Models\CandidateProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyPlacementPipelineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the entire business pipeline loop step-by-step.
     */
    public function test_complete_faculty_placement_vetting_pipeline_flow()
    {
        // 1. Create our mock roles (Admin, Recruiter, Faculty Candidate)
        $admin = new User([
            'name' => 'Platform Admin',
            'email' => 'admin@sourcing.com',
            'password' => bcrypt('password'),
        ]);
        $admin->role = 'admin';
        $admin->save();

        $recruiterUser = new User([
            'name' => 'Dr. Vidyarthi',
            'email' => 'recruiter@sourcing.com',
            'password' => bcrypt('password'),
        ]);
        $recruiterUser->role = 'institution';
        $recruiterUser->save();

        $institution = Institution::create([
            'user_id' => $recruiterUser->id,
            'company_name' => 'Vidyapeeth Coaching Classes',
            'type' => 'coaching_center',
            'website' => 'https://vidyapeeth.in',
            'description' => 'Kota prep classes.',
            'location' => 'Kota, Rajasthan',
            'branding_settings' => ['primary_color' => '#16a34a', 'accent_color' => '#22c55e']
        ]);

        $candidateUser = new User([
            'name' => 'Prof. Amit Verma',
            'email' => 'faculty@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $candidateUser->role = 'candidate';
        $candidateUser->save();

        $candidateProfile = CandidateProfile::create([
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

        // 2. Step 1: Recruiter posts a vacancy
        $this->actingAs($recruiterUser);
        $response = $this->post(route('recruiter.post-vacancy'), [
            'title' => 'Senior IIT-JEE Physics Faculty',
            'subject' => 'Physics',
            'target_class' => 'IIT-JEE Advanced',
            'experience_required' => '5 years',
            'location' => 'Kota, Rajasthan',
            'salary_min' => 1200000,
            'salary_max' => 1800000,
            'description' => 'Need physics HOD to teach mechanics.',
            'deadline' => now()->addDays(10)->toDateString()
        ]);
        $response->assertStatus(302); // Redirect back
        $this->assertDatabaseCount('vacancies', 1);
        $vacancy = Vacancy::first();

        // 3. Step 2: Faculty views vacancy (Vessel Institution Details are HIDDEN for Candidate/Guest)
        $this->actingAs($candidateUser);
        $response = $this->get(route('vacancies.show', $vacancy->id));
        $response->assertStatus(200);
        $response->assertSee('Confidential Premium Education Client');
        $response->assertDontSee('Vidyapeeth Coaching Classes');

        // 4. Step 3: Faculty applies for the vacancy
        $response = $this->post(route('vacancies.apply', $vacancy->id), [
            'cover_letter' => 'Interested in this Physics job.',
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseCount('applications', 1);
        $application = Application::first();
        $this->assertEquals('applied', $application->status);

        // 5. Step 4-5: Admin reviews & shortlists applicant (Updates state to shortlisted_by_admin)
        $this->actingAs($admin);
        $response = $this->post(route('admin.application.shortlist', $application->id));
        $response->assertStatus(302);
        $application->refresh();
        $this->assertEquals('shortlisted_by_admin', $application->status);

        // 6. Step 6: Recruiter views shortlisted masked CV (Contact Details are HIDDEN)
        $this->actingAs($recruiterUser);
        $response = $this->get(route('recruiter.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Personal Contact Details Masked');
        $response->assertDontSee('Prof. Amit Verma');
        $response->assertDontSee('faculty@gmail.com');

        // 7. Step 7: Recruiter shortlists masked CV & requests contact unlock
        $response = $this->post(route('recruiter.request-unlock', $application->id));
        $response->assertStatus(302);
        $application->refresh();
        $this->assertEquals('shortlisted_by_institution', $application->status);

        // 8. Step 8: Admin approves the unlock request -> reveals contact details
        $this->actingAs($admin);
        $response = $this->post(route('admin.application.unlock-approve', $application->id));
        $response->assertStatus(302);
        $application->refresh();
        $this->assertEquals('unlocked', $application->status);

        // 9. Step 8b: Recruiter views fully unmasked contact details
        $this->actingAs($recruiterUser);
        $response = $this->get(route('recruiter.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Prof. Amit Verma');
        $response->assertSee('faculty@gmail.com');

        // 10. Step 9: Recruiter progresses vetting pipelines (Written Test, Interview, Demo)
        $response = $this->post(route('recruiter.update-pipeline', $application->id), [
            'status' => 'written_test'
        ]);
        $response->assertStatus(302);
        $application->refresh();
        $this->assertEquals('written_test', $application->status);

        $response = $this->post(route('recruiter.update-pipeline', $application->id), [
            'status' => 'demo',
            'demo_rating' => 9.2
        ]);
        $response->assertStatus(302);
        $application->refresh();
        $this->assertEquals('demo', $application->status);
        $this->assertEquals(9.2, $application->demo_rating);

        // 11. Step 10: Recruiter appoints candidate -> complete recruitment!
        $response = $this->post(route('recruiter.update-pipeline', $application->id), [
            'status' => 'appointed'
        ]);
        $response->assertStatus(302);
        $application->refresh();
        $this->assertEquals('appointed', $application->status);
    }

    /**
     * Test the user registration (sign up) flows.
     */
    public function test_user_sign_up_flow()
    {
        // A. Candidate Registration
        $response = $this->get(route('signup'));
        $response->assertStatus(200);

        $response = $this->post(route('signup.submit'), [
            'name' => 'Dr. Jane Doe',
            'email' => 'jane.doe@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'candidate',
            'phone' => '9999988888',
            'current_designation' => 'Associate Professor',
            'highest_degree' => 'Ph.D. in Chemistry',
            'subjects_taught' => 'Organic Chemistry, Biochemistry',
            'experience_years' => 10,
            'current_ctc' => 1800000,
            'expected_ctc' => 2200000,
            'preferred_location' => 'Bangalore',
            'experience_summary' => 'Ex-BITS faculty with 10 years experience.',
        ]);

        $response->assertRedirect(route('faculty.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'jane.doe@gmail.com',
            'role' => 'candidate',
            'phone' => '9999988888',
        ]);
        $this->assertDatabaseHas('candidate_profiles', [
            'current_designation' => 'Associate Professor',
            'highest_degree' => 'Ph.D. in Chemistry'
        ]);

        // B. Recruiter / Institution Registration
        $response = $this->post(route('signup.submit'), [
            'name' => 'Dean Apex',
            'email' => 'dean@apex-academy.edu',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'institution',
            'phone' => '9876543210',
            'company_name' => 'Apex Academy',
            'type' => 'school',
            'website' => 'https://apex-academy.edu',
            'location' => 'Pune, Maharashtra',
            'description' => 'A premier CBSE boarding school.',
        ]);

        $response->assertRedirect(route('recruiter.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'dean@apex-academy.edu',
            'role' => 'institution',
            'phone' => '9876543210',
        ]);
        $this->assertDatabaseHas('institutions', [
            'company_name' => 'Apex Academy',
            'type' => 'school'
        ]);
    }
}
