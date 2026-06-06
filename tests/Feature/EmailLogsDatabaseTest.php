<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\EmailLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailLogsDatabaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that sending an email creates a log entry in the database.
     */
    public function test_email_sent_event_creates_database_log()
    {
        // Dispatch mail
        Mail::raw("Hello, this is a test email body content.", function($message) {
            $message->to('user@test.com')
                    ->subject("Test Email Subject Line");
        });

        // Assert database log was created
        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => 'user@test.com',
            'subject' => 'Test Email Subject Line',
            'status' => 'sent',
        ]);

        $log = EmailLog::first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Hello, this is a test email body content', $log->body);
    }

    /**
     * Test that admin can view and filter email logs in settings dashboard.
     */
    public function test_admin_settings_displays_and_filters_email_logs()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Create dummy logs
        EmailLog::create([
            'recipient_email' => 'alpha@test.com',
            'subject' => 'Subject Alpha',
            'body' => 'Body Alpha content',
            'status' => 'sent',
        ]);

        EmailLog::create([
            'recipient_email' => 'beta@test.com',
            'subject' => 'Subject Beta',
            'body' => 'Body Beta content',
            'status' => 'sent',
        ]);

        // Load page
        $response = $this->get(route('admin.settings', ['tab' => 'whatsapp']));
        $response->assertStatus(200);
        $response->assertViewHas('emailSentLogs');

        // Filter search
        $responseFiltered = $this->get(route('admin.settings', [
            'tab' => 'whatsapp',
            'search_email_log' => 'Beta'
        ]));
        $responseFiltered->assertStatus(200);
        
        $logs = $responseFiltered->viewData('emailSentLogs');
        $this->assertEquals(1, $logs->total());
        $this->assertEquals('beta@test.com', $logs->first()->recipient_email);
    }

    /**
     * Test that sending notifications dispatches emails and creates database logs.
     */
    public function test_sending_notifications_dispatches_emails()
    {
        $candidate = User::factory()->create([
            'role' => 'candidate',
            'name' => 'John Doe',
            'email' => 'john@test.com',
        ]);

        // Trigger notification
        \App\Models\Notification::send(
            $candidate->id,
            '📥 Application Submitted',
            'You have applied for Mathematics Professor Position.',
            url('/'),
            'success'
        );

        // Assert that the email log was captured
        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => 'john@test.com',
            'subject' => '📥 Application Submitted: Mathematics Professor Position',
            'status' => 'sent',
        ]);

        $log = EmailLog::where('recipient_email', 'john@test.com')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Mathematics Professor Position', $log->body);
        $this->assertStringContainsString('John Doe', $log->body);
    }
}
