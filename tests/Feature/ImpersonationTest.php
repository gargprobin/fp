<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $candidateUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $this->candidateUser = User::factory()->create([
            'name' => 'Candidate User',
            'email' => 'candidate@example.com',
            'role' => 'candidate',
        ]);
    }

    public function test_admin_can_impersonate_and_leave_impersonation()
    {
        // 1. Act as admin
        $this->actingAs($this->adminUser);

        // 2. Impersonate candidate
        $response = $this->post(route('admin.impersonate', ['id' => $this->candidateUser->id]));
        $response->assertRedirect(route('faculty.dashboard'));

        // Check that auth user is now candidate, and impersonate_admin_id is set
        $this->assertEquals($this->candidateUser->id, auth()->id());
        $this->assertEquals($this->adminUser->id, session('impersonate_admin_id'));

        // 3. Leave impersonation
        $response = $this->post(route('admin.impersonate-leave'));
        $response->assertRedirect(route('admin.settings'));

        // Check that auth user is back to admin, and session variable is cleared
        $this->assertEquals($this->adminUser->id, auth()->id());
        $this->assertFalse(session()->has('impersonate_admin_id'));
    }

    public function test_guest_cannot_impersonate_leave()
    {
        $response = $this->post(route('admin.impersonate-leave'));
        // If route('login') is not defined, this might throw an exception.
        $response->assertRedirect(route('login'));
    }
}
