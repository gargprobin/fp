<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemePreferenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest can set premium theme preference.
     */
    public function test_guest_can_set_premium_theme_via_cookie(): void
    {
        // Set to a premium theme
        $response = $this->get('/set-theme?theme=mac_glass_aurora');
        $response->assertStatus(302);
        $response->assertCookie('theme_preference', 'mac_glass_aurora');

        // Set to another premium theme
        $response = $this->get('/set-theme?theme=dracula_vampire');
        $response->assertStatus(302);
        $response->assertCookie('theme_preference', 'dracula_vampire');
    }

    /**
     * Test invalid theme name falls back to light.
     */
    public function test_invalid_theme_preference_falls_back_to_light(): void
    {
        $response = $this->get('/set-theme?theme=super_custom_invalid_theme');
        $response->assertStatus(302);
        $response->assertCookie('theme_preference', 'light');
    }

    /**
     * Test authenticated user can set and persist theme preference in database.
     */
    public function test_authenticated_user_persists_theme_preference(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/set-theme?theme=mac_glass_dark');
        $response->assertStatus(302);
        $response->assertCookie('theme_preference', 'mac_glass_dark');

        // Assert database persistence
        $user->refresh();
        $this->assertEquals('mac_glass_dark', $user->theme_preference);
    }
}
