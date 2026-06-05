<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that helper writes log entries successfully.
     */
    public function test_activity_log_write_helper_stores_in_db()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $log = ActivityLog::write('test_action', 'This is a test audit description.');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'test_action',
            'description' => 'This is a test audit description.',
        ]);
        
        $this->assertEquals($admin->id, $log->user->id);
    }

    /**
     * Test that index loads successfully and includes required settings and logs variables.
     */
    public function test_settings_index_loads_with_required_variables()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        ActivityLog::write('test_action', 'Some action description');

        $response = $this->get(route('admin.settings'));

        $response->assertStatus(200);
        $response->assertViewHas('activityLogs');
        $response->assertViewHas('monthlyStats');
        $response->assertViewHas('popularSubjects');
        $response->assertViewHas('mailMailer');
    }

    /**
     * Test that updating email configurations logs the action correctly.
     */
    public function test_updating_email_settings_records_activity_log()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post(route('admin.settings.update'), [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.testserver.com',
            'mail_port' => '587',
            'mail_encryption' => 'tls',
            'mail_username' => 'testuser',
            'mail_password' => 'secret_pass',
            'mail_from_address' => 'noreply@test.com',
            'mail_from_name' => 'Testing Gateway',
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('site_settings', [
            'key' => 'mail_mailer',
            'value' => 'smtp',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'update_email_settings',
        ]);
    }

    /**
     * Test that deleting a user creates an activity log entry.
     */
    public function test_deleting_user_creates_activity_log()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $targetUser = User::factory()->create(['role' => 'candidate']);

        $response = $this->delete(route('admin.user.delete', $targetUser->id));

        $response->assertStatus(302);
        $this->assertSoftDeleted('users', ['id' => $targetUser->id]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'delete_user',
        ]);
    }

    public function test_sending_test_email_dispatches_mail_and_logs_action()
    {
        \Illuminate\Support\Facades\Event::fake([
            \Illuminate\Mail\Events\MessageSending::class
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post(route('admin.email.test-send'), [
            'email_address' => 'recipient@test.com',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        
        \Illuminate\Support\Facades\Event::assertDispatched(\Illuminate\Mail\Events\MessageSending::class);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'send_test_email',
        ]);
    }
}
