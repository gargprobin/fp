<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class NotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test default user notification settings.
     */
    public function test_default_notification_settings()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'candidate',
        ]);

        $settings = $user->getNotificationSettings();

        $this->assertTrue($settings['in_app']);
        $this->assertTrue($settings['whatsapp']);
        $this->assertTrue($settings['email']);
        $this->assertFalse($settings['dnd']);
        $this->assertEquals('22:00', $settings['dnd_start']);
        $this->assertEquals('08:00', $settings['dnd_end']);
    }

    /**
     * Test saving notification settings via web route.
     */
    public function test_save_notification_settings_via_route()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'candidate',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('notifications.save-settings'), [
            'in_app' => false,
            'whatsapp' => false,
            'email' => false,
            'dnd' => true,
            'dnd_start' => '23:00',
            'dnd_end' => '07:00',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'settings' => [
                'in_app' => false,
                'whatsapp' => false,
                'email' => false,
                'dnd' => true,
                'dnd_start' => '23:00',
                'dnd_end' => '07:00',
            ]
        ]);

        $user->refresh();
        $this->assertFalse($user->getNotificationSettings()['in_app']);
        $this->assertFalse($user->getNotificationSettings()['whatsapp']);
        $this->assertFalse($user->getNotificationSettings()['email']);
        $this->assertTrue($user->getNotificationSettings()['dnd']);
        $this->assertEquals('23:00', $user->getNotificationSettings()['dnd_start']);
        $this->assertEquals('07:00', $user->getNotificationSettings()['dnd_end']);
    }

    /**
     * Test that notifications are not stored in database if in-app alerts are off.
     */
    public function test_in_app_notification_bypass_when_disabled()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'candidate',
        ]);

        // Disable in-app notifications
        $user->notification_settings = array_merge($user->getNotificationSettings(), ['in_app' => false]);
        $user->save();

        $notification = Notification::send($user->id, 'Test Title', 'Test Message', null, 'info');

        // Check that notification is not persisted in the database
        $this->assertFalse($notification->exists);
        $this->assertDatabaseCount('notifications', 0);
    }

    /**
     * Test that notifications are stored in database if in-app alerts are on.
     */
    public function test_in_app_notification_saved_when_enabled()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'candidate',
        ]);

        $notification = Notification::send($user->id, 'Test Title', 'Test Message', null, 'info');

        $this->assertTrue($notification->exists);
        $this->assertDatabaseCount('notifications', 1);
    }

    /**
     * Test DND Quiet Hours behavior for WhatsApp dispatches.
     */
    public function test_whatsapp_dispatch_skipped_during_dnd_quiet_hours()
    {
        Log::shouldReceive('info')->withArgs(function ($message) {
            return str_contains($message, 'due to active DND Quiet Hours');
        })->once();

        // Standard Laravel Log checks could trigger other logs, so let's allow other calls
        Log::shouldReceive('info')->byDefault();
        Log::shouldReceive('warning')->byDefault();
        Log::shouldReceive('error')->byDefault();

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'candidate',
            'phone' => '9876543210',
        ]);

        // Set DND active
        $user->notification_settings = [
            'in_app' => true,
            'whatsapp' => true,
            'email' => true,
            'dnd' => true,
            'dnd_start' => now()->subHour()->format('H:i'),
            'dnd_end' => now()->addHour()->format('H:i'),
        ];
        $user->save();

        // Enable global WhatsApp setting
        SiteSetting::updateOrCreate(['key' => 'whatsapp_enabled'], ['value' => '1']);

        Notification::send($user->id, 'Test DND', 'Test Message');
    }
}
