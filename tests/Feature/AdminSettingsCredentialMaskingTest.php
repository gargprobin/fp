<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsCredentialMaskingTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an Admin user
        $this->adminUser = new User([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->adminUser->role = 'admin';
        $this->adminUser->save();
    }

    /**
     * Test that WhatsApp API settings are masked when viewed.
     */
    public function test_whatsapp_credentials_are_masked_in_settings_view()
    {
        // Set real credentials in DB
        SiteSetting::setVal('whatsapp_api_url', 'https://api.whatsapp.com/v1/send?token=my-secret-token');
        SiteSetting::setVal('whatsapp_api_key', 'my-secret-key-123');

        $this->actingAs($this->adminUser);

        $response = $this->get(route('admin.settings'));

        $response->assertStatus(200);
        $response->assertViewHas('whatsappApiUrl', '********************');
        $response->assertViewHas('whatsappApiKey', '********************');
    }

    /**
     * Test that saving settings with masked values does not overwrite the actual values in database.
     */
    public function test_masked_values_do_not_overwrite_actual_values()
    {
        // Set real credentials in DB
        $realUrl = 'https://api.whatsapp.com/v1/send?token=my-secret-token';
        $realKey = 'my-secret-key-123';
        SiteSetting::setVal('whatsapp_api_url', $realUrl);
        SiteSetting::setVal('whatsapp_api_key', $realKey);
        SiteSetting::setVal('whatsapp_api_provider', 'Meta Cloud API');

        $this->actingAs($this->adminUser);

        // Submit form with masked values
        $response = $this->post(route('admin.settings.update'), [
            'whatsapp_api_provider' => 'Meta Cloud API',
            'whatsapp_api_url' => '********************',
            'whatsapp_api_key' => '********************',
            'whatsapp_enabled' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check db: the values must STILL be the original real ones
        $this->assertEquals($realUrl, SiteSetting::getVal('whatsapp_api_url'));
        $this->assertEquals($realKey, SiteSetting::getVal('whatsapp_api_key'));
    }

    /**
     * Test that saving settings with custom asterisks/bullet mask also does not overwrite.
     */
    public function test_custom_asterisk_or_bullet_masks_do_not_overwrite()
    {
        // Set real credentials in DB
        $realUrl = 'https://api.whatsapp.com/v1/send?token=my-secret-token';
        $realKey = 'my-secret-key-123';
        SiteSetting::setVal('whatsapp_api_url', $realUrl);
        SiteSetting::setVal('whatsapp_api_key', $realKey);
        SiteSetting::setVal('whatsapp_api_provider', 'Meta Cloud API');

        $this->actingAs($this->adminUser);

        // Submit form with custom masks (e.g. 14 asterisks as requested in user prompt, or bullet points)
        $response = $this->post(route('admin.settings.update'), [
            'whatsapp_api_provider' => 'Meta Cloud API',
            'whatsapp_api_url' => '**************', // 14 asterisks
            'whatsapp_api_key' => '••••••••••••••••••••', // bullets
            'whatsapp_enabled' => '1',
        ]);

        $response->assertRedirect();

        // Check db: the values must STILL be the original real ones
        $this->assertEquals($realUrl, SiteSetting::getVal('whatsapp_api_url'));
        $this->assertEquals($realKey, SiteSetting::getVal('whatsapp_api_key'));
    }

    /**
     * Test that entering a new URL/key correctly updates the database.
     */
    public function test_new_values_are_correctly_saved_to_database()
    {
        SiteSetting::setVal('whatsapp_api_url', 'https://api.whatsapp.com/v1/send?token=my-secret-token');
        SiteSetting::setVal('whatsapp_api_key', 'my-secret-key-123');

        $this->actingAs($this->adminUser);

        $newUrl = 'https://wa.rj19web.com/api/send';
        $newKey = 'new-secret-key-789';

        $response = $this->post(route('admin.settings.update'), [
            'whatsapp_api_provider' => 'wa.rj19web.com Official Gateway',
            'whatsapp_api_url' => $newUrl,
            'whatsapp_api_key' => $newKey,
            'whatsapp_enabled' => '1',
        ]);

        $response->assertRedirect();

        // Check db: the values must be updated to the new ones
        $this->assertEquals($newUrl, SiteSetting::getVal('whatsapp_api_url'));
        $this->assertEquals($newKey, SiteSetting::getVal('whatsapp_api_key'));
    }

    /**
     * Test that clearing the fields (sending empty string) correctly removes them.
     */
    public function test_clearing_values_saves_empty_strings()
    {
        SiteSetting::setVal('whatsapp_api_url', 'https://api.whatsapp.com/v1/send?token=my-secret-token');
        SiteSetting::setVal('whatsapp_api_key', 'my-secret-key-123');

        $this->actingAs($this->adminUser);

        $response = $this->post(route('admin.settings.update'), [
            'whatsapp_api_provider' => 'wa.rj19web.com Official Gateway',
            'whatsapp_api_url' => '',
            'whatsapp_api_key' => '',
            'whatsapp_enabled' => '1',
        ]);

        $response->assertRedirect();

        // Check db: the values must be null/empty
        $this->assertEmpty(SiteSetting::getVal('whatsapp_api_url'));
        $this->assertEmpty(SiteSetting::getVal('whatsapp_api_key'));
    }
}
