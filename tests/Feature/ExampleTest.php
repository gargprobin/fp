<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test searching vacancies by subject and location.
     */
    public function test_search_vacancies_by_subject_and_location(): void
    {
        $recruiterUser = \App\Models\User::create([
            'name' => 'Recruiter User',
            'email' => 'recruiter_search@example.com',
            'password' => bcrypt('password'),
            'role' => 'institution'
        ]);

        $institution = \App\Models\Institution::create([
            'user_id' => $recruiterUser->id,
            'company_name' => 'Test Institution LLC',
            'type' => 'coaching_center',
            'website' => 'https://test.com',
            'description' => 'Test',
            'location' => 'Kota',
        ]);

        // Create Vacancy 1
        \App\Models\Vacancy::create([
            'institution_id' => $institution->id,
            'title' => 'Chemistry Expert Teacher',
            'subject' => 'Chemistry',
            'target_class' => 'IIT-JEE',
            'experience_required' => '3 years',
            'location' => 'Kota, Rajasthan',
            'salary_min' => 50000,
            'salary_max' => 80000,
            'deadline' => now()->addDays(5)->toDateString(),
            'status' => 'open',
            'description' => 'Chemistry teacher description',
        ]);

        // Create Vacancy 2
        \App\Models\Vacancy::create([
            'institution_id' => $institution->id,
            'title' => 'Physics HOD',
            'subject' => 'Physics',
            'target_class' => 'IIT-JEE',
            'experience_required' => '5 years',
            'location' => 'Bangalore, Karnataka',
            'salary_min' => 100000,
            'salary_max' => 150000,
            'deadline' => now()->addDays(5)->toDateString(),
            'status' => 'open',
            'description' => 'Physics teacher description',
        ]);

        // Search for Physics
        $response = $this->get('/?subject=Physics');
        $response->assertStatus(200);
        $response->assertSee('Physics HOD');
        $response->assertDontSee('Chemistry Expert Teacher');

        // Search for Kota location
        $response = $this->get('/?location=Kota');
        $response->assertStatus(200);
        $response->assertSee('Chemistry Expert Teacher');
        $response->assertDontSee('Physics HOD');
    }
}

