<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThemeController extends Controller
{
    /**
     * Set user theme preference (saves in user model if logged in, or in cookie).
     */
    public function setTheme(Request $request)
    {
        $theme = $request->query('theme', 'light');
        
        // Validate theme
        if (!in_array($theme, [
            'light', 'dark', 'sepia', 'academic_blue', 'forest_emerald', 
            'vintage_plum', 'sunset_amber', 'ocean_breeze', 'crimson_ruby', 
            'nord_minimal', 'unacademy_mint', 'cyberpunk_neon', 'dracula_vampire',
            'rosegold_romance', 'coffee_latte', 'slate_graphite', 'royal_amethyst',
            'mint_green', 'mac_glass_light', 'mac_glass_dark', 'mac_glass_aurora'
        ])) {
            $theme = 'light';
        }

        if (auth()->check()) {
            $user = auth()->user();
            $user->theme_preference = $theme;
            $user->save();
        }

        // Back with cookie valid for 1 year
        return back()->cookie('theme_preference', $theme, 60 * 24 * 365);
    }
}
