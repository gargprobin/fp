<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WhatsappLog;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappLogsDatabaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test timezone is set to Indian Standard Time (Asia/Kolkata).
     */
    public function test_timezone_configuration()
    {
        $this->assertEquals('Asia/Kolkata', config('app.timezone'));
    }

    /**
     * Test that sending a notification logs the WhatsApp broadcast in the database.
     */
    public function test_notification_sends_and_creates_database_log()
    {
        // Enforce WhatsApp is enabled in site settings
        SiteSetting::setVal('whatsapp_enabled', '1');

        $candidate = new User([
            'name' => 'Candidate User Test',
            'email' => 'cand_test@example.com',
            'password' => bcrypt('password'),
            'phone' => '919876543210',
        ]);
        $candidate->role = 'candidate';
        $candidate->save();

        // Count should be 0 initially
        $this->assertEquals(0, WhatsappLog::count());

        // Dispatch a template notification
        \App\Models\Notification::send(
            $candidate->id,
            '📥 Application Submitted',
            'You have applied for Physics Teacher. Your profile is sent for Admin screening.',
            '/'
        );

        // Verification
        $this->assertEquals(1, WhatsappLog::count());
        $log = WhatsappLog::first();
        $this->assertEquals('Candidate User Test', $log->recipient_name);
        $this->assertEquals('919876543210', $log->recipient_phone);
        $this->assertEquals('candidate', $log->recipient_role);
        $this->assertEquals('📥 Application Submitted', $log->title);
        $this->assertStringContainsString('Physics Teacher', $log->message);
    }

    /**
     * Test legacy JSON logs are auto-ported to the database if table is empty.
     */
    public function test_legacy_logs_are_ported_to_database_automatically()
    {
        // Add fake logs to site settings
        $fakeLogs = [
            [
                'recipient_name' => 'Legacy Recipient',
                'recipient_phone' => '919876543211',
                'recipient_role' => 'recruiter',
                'title' => '📥 New Sourced Applicant',
                'message' => 'Legacy test message content.',
                'timestamp' => '2026-06-03 22:15:00',
                'status' => '✅ Sent'
            ]
        ];
        SiteSetting::setVal('whatsapp_sent_logs', json_encode($fakeLogs));

        // Create admin and log in
        $admin = new User([
            'name' => 'Admin User',
            'email' => 'admin_test_log@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->role = 'admin';
        $admin->save();

        $this->actingAs($admin);

        // Count is 0 before visit
        $this->assertEquals(0, WhatsappLog::count());

        // Visit settings page
        $response = $this->get(route('admin.settings'));
        $response->assertStatus(200);

        // Verify log was ported
        $this->assertEquals(1, WhatsappLog::count());
        $log = WhatsappLog::first();
        $this->assertEquals('Legacy Recipient', $log->recipient_name);
        $this->assertEquals('919876543211', $log->recipient_phone);
        $this->assertEquals('recruiter', $log->recipient_role);
        $this->assertEquals('📥 New Sourced Applicant', $log->title);
        $this->assertEquals('Legacy test message content.', $log->message);
        $this->assertEquals('✅ Sent', $log->status);
        $this->assertEquals('2026-06-03 22:15:00', $log->created_at->toDateTimeString());
    }

    /**
     * Test admin filters (search, sort, pagination) return correct logs on index route.
     */
    public function test_admin_whatsapp_logs_filters()
    {
        // Seed some logs
        WhatsappLog::create([
            'recipient_name' => 'Alice Dev',
            'recipient_phone' => '910000000001',
            'recipient_role' => 'candidate',
            'title' => 'Vetting Shortlist',
            'message' => 'Profile match shortlist message.',
            'status' => '✅ Sent',
        ]);

        WhatsappLog::create([
            'recipient_name' => 'Bob Recruiter',
            'recipient_phone' => '910000000002',
            'recipient_role' => 'institution',
            'title' => 'Recruiter Alert',
            'message' => 'Hiring pipeline update.',
            'status' => '⚠️ Skipped',
        ]);

        $admin = new User([
            'name' => 'Admin User 2',
            'email' => 'admin_test_log2@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->role = 'admin';
        $admin->save();

        $this->actingAs($admin);

        // 1. Check default listing
        $response = $this->get(route('admin.settings') . '?tab=whatsapp');
        $response->assertStatus(200);
        $response->assertSee('Alice Dev');
        $response->assertSee('Bob Recruiter');

        // 2. Check search filters works
        $response = $this->get(route('admin.settings') . '?tab=whatsapp&search_wap=Alice');
        $response->assertStatus(200);
        $response->assertSee('Alice Dev');
        $response->assertDontSee('Bob Recruiter');

        // 3. Check sort oldest
        $response = $this->get(route('admin.settings') . '?tab=whatsapp&sort_wap=oldest');
        $response->assertStatus(200);
        $logs = $response->viewData('whatsappSentLogs');
        $this->assertEquals('Alice Dev', $logs->first()->recipient_name);
    }

    /**
     * Test notification sending falls back to legacy JSON logging when the whatsapp_logs table does not exist.
     */
    public function test_notification_falls_back_to_legacy_logging_when_table_is_missing()
    {
        // Drop the whatsapp_logs table to simulate production environment
        \Illuminate\Support\Facades\Schema::dropIfExists('whatsapp_logs');

        // Enforce WhatsApp is enabled in site settings
        SiteSetting::setVal('whatsapp_enabled', '1');
        SiteSetting::setVal('whatsapp_sent_logs', json_encode([])); // Start with empty logs

        $candidate = new User([
            'name' => 'Candidate Fallback Test',
            'email' => 'fallback_test@example.com',
            'password' => bcrypt('password'),
            'phone' => '919876543212',
        ]);
        $candidate->role = 'candidate';
        $candidate->save();

        // Dispatch a template notification
        \App\Models\Notification::send(
            $candidate->id,
            '📥 Application Submitted',
            'You have applied for Chemistry Teacher. Your profile is sent for Admin screening.',
            '/'
        );

        // Verify that the table remains missing and no table logs were written
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('whatsapp_logs'));

        // Verify fallback: JSON logs in site settings contains the entry
        $rawLogs = SiteSetting::getVal('whatsapp_sent_logs', '[]');
        $logs = json_decode($rawLogs, true);
        $this->assertCount(1, $logs);
        $this->assertEquals('Candidate Fallback Test', $logs[0]['recipient_name']);
        $this->assertEquals('919876543212', $logs[0]['recipient_phone']);
        $this->assertEquals('📥 Application Submitted', $logs[0]['title']);
        $this->assertStringContainsString('Chemistry Teacher', $logs[0]['message']);
        
        // Let's also verify that the admin settings page loads without error and uses manual paginator
        $admin = new User([
            'name' => 'Admin User Fallback',
            'email' => 'admin_fallback@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->role = 'admin';
        $admin->save();

        $this->actingAs($admin);
        
        $response = $this->get(route('admin.settings') . '?tab=whatsapp');
        $response->assertStatus(200);
        $response->assertSee('Candidate Fallback Test');
    }

    /**
     * Test that disabling a specific event trigger prevents sending and logging.
     */
    public function test_event_triggers_granular_control()
    {
        SiteSetting::setVal('whatsapp_enabled', '1');
        
        $candidate = new User([
            'name' => 'Candidate Event Control Test',
            'email' => 'event_ctrl@example.com',
            'password' => bcrypt('password'),
            'phone' => '919876543213',
        ]);
        $candidate->role = 'candidate';
        $candidate->save();

        // 1. Disable "Candidate Applied" event trigger
        SiteSetting::setVal('whatsapp_event_candidate_applied', '0');

        $this->assertEquals(0, WhatsappLog::count());

        // Dispatch notification
        \App\Models\Notification::send(
            $candidate->id,
            '📥 Application Submitted',
            'You have applied for English Teacher. Your profile is sent for Admin screening.',
            '/'
        );

        // Verification: Log count should STILL be 0 because it was skipped
        $this->assertEquals(0, WhatsappLog::count());

        // 2. Enable "Candidate Applied" event trigger
        SiteSetting::setVal('whatsapp_event_candidate_applied', '1');

        // Dispatch notification again
        \App\Models\Notification::send(
            $candidate->id,
            '📥 Application Submitted',
            'You have applied for English Teacher. Your profile is sent for Admin screening.',
            '/'
        );

        // Verification: Log count should now be 1
        $this->assertEquals(1, WhatsappLog::count());
    }

    /**
     * Test that any notifications with unmatched templates are completely omitted from logging.
     */
    public function test_no_template_match_not_logged()
    {
        SiteSetting::setVal('whatsapp_enabled', '1');

        $candidate = new User([
            'name' => 'Candidate Unmatched Test',
            'email' => 'unmatched@example.com',
            'password' => bcrypt('password'),
            'phone' => '919876543214',
        ]);
        $candidate->role = 'candidate';
        $candidate->save();

        $this->assertEquals(0, WhatsappLog::count());

        // Dispatch unmatched notification (different title / message structure)
        \App\Models\Notification::send(
            $candidate->id,
            'General Info Alert',
            'This is a general system notification that does not match any WhatsApp templates.',
            '/'
        );

        // Verification: Log count should remain 0
        $this->assertEquals(0, WhatsappLog::count());
    }
}
