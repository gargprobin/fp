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
}
