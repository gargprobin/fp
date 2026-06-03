<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\Institution;
use Symfony\Component\HttpFoundation\Response;

class DynamicThemeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Resolve Global Dynamic SaaS Settings
        if (\App\Models\SiteSetting::count() === 0) {
            \App\Models\SiteSetting::setVal('platform_name', 'Sourcing Portal');
            \App\Models\SiteSetting::setVal('platform_tagline', "Premium Faculty Placement & Recruitment Service");
            \App\Models\SiteSetting::setVal('support_email', 'support@sourcing.com');
        }

        if (!\App\Models\SiteSetting::where('key', 'whatsapp_api_url')->exists()) {
            \App\Models\SiteSetting::setVal('whatsapp_api_provider', 'wa.rj19web.com Official Gateway');
            \App\Models\SiteSetting::setVal('whatsapp_api_url', 'https://wa.rj19web.com/api/6e2f51c7-21b0-450b-9329-14955a8c79a/contact/send-message');
            \App\Models\SiteSetting::setVal('whatsapp_enabled', '1');
        }

        $siteSettings = [
            'platform_name' => \App\Models\SiteSetting::getVal('platform_name', 'Sourcing Portal'),
            'platform_tagline' => \App\Models\SiteSetting::getVal('platform_tagline', "Premium Faculty Placement & Recruitment Service"),
            'support_email' => \App\Models\SiteSetting::getVal('support_email', 'support@sourcing.com'),
        ];
        View::share('siteSettings', (object) $siteSettings);

        // 3. Resolve Real-Time Notifications
        $notifications = collect();
        $unreadNotificationsCount = 0;
        if (auth()->check()) {
            $notifications = \App\Models\Notification::where('user_id', auth()->id())
                ->latest()
                ->limit(10)
                ->get();
            $unreadNotificationsCount = \App\Models\Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->count();
        }
        View::share('notificationsList', $notifications);
        View::share('unreadNotificationsCount', $unreadNotificationsCount);

        // 2. Resolve User Theme Preference
        $theme = 'light';
        if (auth()->check()) {
            $theme = auth()->user()->theme_preference ?? 'light';
        } else {
            $theme = $request->cookie('theme_preference', 'light');
        }
        View::share('themePreference', $theme);

        // 2. Resolve Institution Branding (if route has vacancy or institution context)
        $institution = null;
        if ($request->route('vacancy')) {
            $vacancy = $request->route('vacancy');
            // If it's a route model binding instance or an ID
            if (is_numeric($vacancy)) {
                $vacancyModel = \App\Models\Vacancy::with('institution')->find($vacancy);
                $institution = $vacancyModel?->institution;
            } else if ($vacancy instanceof \App\Models\Vacancy) {
                $institution = $vacancy->institution;
            }
        } elseif ($request->route('institution')) {
            $instRoute = $request->route('institution');
            if (is_numeric($instRoute)) {
                $institution = Institution::find($instRoute);
            } else if ($instRoute instanceof Institution) {
                $institution = $instRoute;
            }
        }

        View::share('activeInstitution', $institution);

        return $next($request);
    }
}
