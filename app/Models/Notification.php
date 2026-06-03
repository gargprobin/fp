<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /* ----------------------------------------------------------------
     | Relationships
     * ---------------------------------------------------------------- */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ================================================================
     | CORE WHATSAPP DISPATCHER
     |
     | Inspired by RO Project (ro_whatsapp_helper.php) pattern:
     |   - Single clean method: dispatchWhatsApp($phone, $template, $vars[])
     |   - Variables passed as simple ordered array → auto-mapped to field_1, field_2...
     |   - Variable sanitization (no newlines/tabs in template params)
     |   - FlowHora (rj19web.com) auto-detection
     |   - Token extracted from URL if not stored separately
     * ================================================================ */

    /**
     * Core WhatsApp dispatcher.
     * Sends a registered template message via the FlowHora / Meta gateway.
     *
     * @param  string  $phone        Phone number (10 or 12 digit, any format)
     * @param  string  $templateName Registered template name on gateway
     * @param  array   $variables    Ordered array → maps to {{1}}, {{2}}... / field_1, field_2...
     * @param  User    $user         User model (for contact info & logging)
     * @return bool
     */
    public static function dispatchWhatsApp(string $phone, string $templateName, array $variables, User $user): bool
    {
        // ── 1. Retrieve Settings ──────────────────────────────────────
        $apiUrl = SiteSetting::getVal('whatsapp_api_url');
        $token = SiteSetting::getVal('whatsapp_api_key');

        if (empty($apiUrl)) {
            Log::warning('WhatsApp: API URL not configured.');
            return false;
        }

        // ── 2. Clean Phone Number ─────────────────────────────────────
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;   // Add India country code
        }
        if (strlen($cleanPhone) < 10) {
            Log::warning("WhatsApp: Invalid phone number [{$phone}], skipping dispatch.");
            return false;
        }

        // ── 3. Sanitize Variables (RO pattern) ────────────────────────
        $cleanVars = [];
        foreach ($variables as $val) {
            $str = (string) $val;
            $str = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $str);
            $str = preg_replace('/\s+/', ' ', $str);
            $cleanVars[] = trim($str);
        }

        // ── 4. Determine if FlowHora Gateway ──────────────────────────
        $isFlowHora = str_contains($apiUrl, 'rj19web.com')
                   || str_contains($apiUrl, 'flowhora')
                   || str_contains($apiUrl, 'contact/send-template-message');

        // ── 5. Build Payload & Headers ────────────────────────────────
        if ($isFlowHora) {
            // Auto-correct endpoint from send-message to send-template-message for FlowHora
            if (str_contains($apiUrl, 'contact/send-message') && !str_contains($apiUrl, 'contact/send-template-message')) {
                $apiUrl = str_replace('contact/send-message', 'contact/send-template-message', $apiUrl);
            }

            $payload = [
                'phone_number'      => $cleanPhone,
                'template_name'     => $templateName,
                'template_language' => 'en',
            ];

            // Map variables to field_1, field_2, field_3, etc.
            if (!empty($cleanVars)) {
                foreach ($cleanVars as $index => $val) {
                    $payload['field_' . ($index + 1)] = (string)$val;
                }
            }

            // Auto-append token if not already present in the URL query string
            $apiUrlWithToken = $apiUrl;
            if (!empty($token) && !str_contains($apiUrl, 'token=')) {
                $connector = (str_contains($apiUrl, '?') === false) ? '?' : '&';
                $apiUrlWithToken = $apiUrl . $connector . 'token=' . $token;
            }

            $headers = [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json'
            ];
        } else {
            // Official Meta Cloud API nested payload format
            $payload = [
                'messaging_product' => 'whatsapp',
                'to'                => $cleanPhone,
                'type'              => 'template',
                'template'          => [
                    'name'     => $templateName,
                    'language' => ['code' => 'en'],
                ],
            ];
            if (!empty($cleanVars)) {
                $parameters = array_map(fn($v) => ['type' => 'text', 'text' => $v], $cleanVars);
                $payload['template']['components'] = [['type' => 'body', 'parameters' => $parameters]];
            }

            $apiUrlWithToken = $apiUrl;
            $headers = [
                'Content-Type' => 'application/json'
            ];
        }

        // ── 6. HTTP Request ───────────────────────────────────────────
        $debugLog = ['target_url' => $apiUrlWithToken, 'payload' => $payload];
        try {
            // withoutVerifying() resolves SSL certificate issues on local XAMPP environments
            $client = Http::withoutVerifying()->withHeaders($headers);

            // Set Bearer Token for Meta Cloud API if token is provided
            if (!$isFlowHora && !empty($token)) {
                $client = $client->withToken($token);
            }

            $startTime   = microtime(true);
            $response    = $client->post($apiUrlWithToken, $payload);
            $duration    = round((microtime(true) - $startTime) * 1000, 2);

            $debugLog['http_status']   = $response->status();
            $debugLog['response_body'] = $response->body();
            $debugLog['duration_ms']   = $duration;

            if ($response->successful()) {
                Log::info("WhatsApp Dispatch ✅ to {$cleanPhone} [{$templateName}]", $debugLog);
                return true;
            }

            Log::error("WhatsApp Gateway Error ❌ to {$cleanPhone} [{$templateName}]", $debugLog);
            return false;

        } catch (\Exception $e) {
            $debugLog['exception'] = $e->getMessage();
            Log::error("WhatsApp Connection Failed 🚫 for {$cleanPhone}", $debugLog);
            return false;
        }
    }

    /* ================================================================
     | TEMPLATE HELPERS — 6 Registered Templates
     |
     | Each method fetches the data it needs and calls dispatchWhatsApp()
     | with a clean ordered variables array (just like RO project).
     * ================================================================ */

    /**
     * Template: fp_candidate_applied
     * Recipient: Faculty Candidate — when they submit a job application.
     * Variables: {{1}} candidate name, {{2}} vacancy title, {{3}} dashboard URL, {{4}} platform name
     */
    public static function notifyCandidateApplied(User $user, string $vacancyTitle): bool
    {
        return self::dispatchWhatsApp($user->phone, 'fp_candidate_applied', [
            $user->name,                          // {{1}} Candidate Name
            $vacancyTitle,                        // {{2}} Vacancy Title
            url('/faculty/dashboard'),            // {{3}} Candidate Dashboard URL (application timeline)
            SiteSetting::getVal('platform_name', 'Sourcing Portal'), // {{4}} Platform Name
        ], $user);
    }

    /**
     * Template: fp_recruiter_sourced
     * Recipient: Recruiter / Institution — when a new candidate applies to their vacancy.
     * Variables: {{1}} recruiter name, {{2}} vacancy title, {{3}} degree, {{4}} experience,
     *            {{5}} subject, {{6}} dashboard URL, {{7}} platform name
     */
    public static function notifyRecruiterSourced(User $recruiter, string $vacancyTitle): bool
    {
        return self::dispatchWhatsApp($recruiter->phone, 'fp_recruiter_sourced', [
            $recruiter->name,                              // {{1}} Recruiter Name
            $vacancyTitle,                                 // {{2}} Vacancy Title
            'Postgraduate',                                // {{3}} Degree (masked for privacy)
            '3+ years',                                    // {{4}} Experience (masked)
            'Faculty',                                     // {{5}} Subject (masked)
            url('/recruiter/dashboard'),                   // {{6}} Recruiter Dashboard URL (sourced candidates list)
            SiteSetting::getVal('platform_name', 'Sourcing Portal'), // {{7}} Platform Name
        ], $recruiter);
    }

    /**
     * Template: fp_candidate_lined_up
     * Recipient: Faculty Candidate — when recruiter shortlists & requests Line Up.
     * Variables: {{1}} candidate name, {{2}} vacancy title, {{3}} company name,
     *            {{4}} dashboard URL, {{5}} platform name
     */
    public static function notifyCandidateLinedUp(User $candidate, string $vacancyTitle, string $companyName): bool
    {
        return self::dispatchWhatsApp($candidate->phone, 'fp_candidate_lined_up', [
            $candidate->name,                              // {{1}} Candidate Name
            $vacancyTitle,                                 // {{2}} Vacancy Title
            $companyName,                                  // {{3}} Company Name
            url('/faculty/dashboard'),                     // {{4}} Candidate Dashboard URL (lineup status)
            SiteSetting::getVal('platform_name', 'Sourcing Portal'), // {{5}} Platform Name
        ], $candidate);
    }

    /**
     * Template: fp_lineup_approved_candidate
     * Recipient: Faculty Candidate — when Admin approves Line Up & unlocks contact.
     * Variables: {{1}} candidate name, {{2}} company name, {{3}} vacancy title,
     *            {{4}} website URL, {{5}} dashboard URL, {{6}} platform name
     */
    public static function notifyLineUpApprovedCandidate(User $candidate, string $companyName, string $vacancyTitle): bool
    {
        return self::dispatchWhatsApp($candidate->phone, 'fp_lineup_approved_candidate', [
            $candidate->name,                              // {{1}} Candidate Name
            $companyName,                                  // {{2}} Company Name
            $vacancyTitle,                                 // {{3}} Vacancy Title
            url('/'),                                      // {{4}} Website Homepage URL
            url('/faculty/dashboard'),                     // {{5}} Candidate Dashboard (contact unlock details)
            SiteSetting::getVal('platform_name', 'Sourcing Portal'), // {{6}} Platform Name
        ], $candidate);
    }

    /**
     * Template: fp_lineup_approved_recruiter
     * Recipient: Recruiter / Institution — when Admin approves Line Up (contact details unlocked).
     * Variables: {{1}} recruiter name, {{2}} vacancy title, {{3}} candidate name,
     *            {{4}} candidate email, {{5}} candidate phone, {{6}} dashboard URL, {{7}} platform name
     */
    public static function notifyLineUpApprovedRecruiter(User $recruiter, User $candidate, string $vacancyTitle): bool
    {
        return self::dispatchWhatsApp($recruiter->phone, 'fp_lineup_approved_recruiter', [
            $recruiter->name,                              // {{1}} Recruiter Name
            $vacancyTitle,                                 // {{2}} Vacancy Title
            $candidate->name,                              // {{3}} Candidate Name
            $candidate->email,                             // {{4}} Candidate Email
            $candidate->phone ?? 'N/A',                   // {{5}} Candidate Phone
            url('/recruiter/dashboard'),                   // {{6}} Recruiter Dashboard (unlocked candidate contact)
            SiteSetting::getVal('platform_name', 'Sourcing Portal'), // {{7}} Platform Name
        ], $recruiter);
    }

    /**
     * Template: fp_pipeline_stage_updated
     * Recipient: Faculty Candidate — when recruiter moves vetting stage.
     * Variables: {{1}} candidate name, {{2}} vacancy title, {{3}} stage name,
     *            {{4}} dashboard URL, {{5}} platform name
     */
    public static function notifyPipelineStageUpdated(User $candidate, string $vacancyTitle, string $stageName): bool
    {
        return self::dispatchWhatsApp($candidate->phone, 'fp_pipeline_stage_updated', [
            $candidate->name,                              // {{1}} Candidate Name
            $vacancyTitle,                                 // {{2}} Vacancy Title
            $stageName,                                    // {{3}} Stage Name (e.g. Interview, Demo)
            url('/faculty/dashboard'),                     // {{4}} Candidate Dashboard (vetting timeline)
            SiteSetting::getVal('platform_name', 'Sourcing Portal'), // {{5}} Platform Name
        ], $candidate);
    }

    /* ================================================================
     | MAIN send() METHOD
     |
     | Creates DB notification record + routes to correct WhatsApp
     | template helper based on notification title.
     | Also updates the sent-messages log in site settings.
     * ================================================================ */

    /**
     * Create a notification record and dispatch a WhatsApp message.
     */
    public static function send($userId, $title, $message, $url = null, $type = 'info', $candidateId = null)
    {
        $user = User::find($userId);
        $inAppEnabled = true;
        if ($user) {
            $settings = $user->getNotificationSettings();
            $inAppEnabled = $settings['in_app'] ?? true;
        }

        // ── 1. Save notification to DB (if in-app enabled) ────────────────
        if ($inAppEnabled) {
            $notification = self::create([
                'user_id'    => $userId,
                'title'      => $title,
                'message'    => $message,
                'action_url' => $url,
                'type'       => $type,
                'is_read'    => false,
            ]);
        } else {
            $notification = new self([
                'user_id'    => $userId,
                'title'      => $title,
                'message'    => $message,
                'action_url' => $url,
                'type'       => $type,
                'is_read'    => true,
            ]);
        }

        // ── 2. WhatsApp dispatch (if enabled) ─────────────────────────
        $whatsappEnabled = SiteSetting::getVal('whatsapp_enabled', '1') === '1';

        if (!$whatsappEnabled || !$user) {
            return $notification;
        }

        $settings = $user->getNotificationSettings();

        // Verify User's WhatsApp Notification Preference
        if (!($settings['whatsapp'] ?? true)) {
            Log::info("WhatsApp: Skipped for user {$userId} due to WhatsApp notification preference disabled.");
            return $notification;
        }

        // Verify DND (Do Not Disturb) Quiet Hours
        if ($settings['dnd'] ?? false) {
            $now = now()->format('H:i');
            $start = $settings['dnd_start'] ?? '22:00';
            $end = $settings['dnd_end'] ?? '08:00';

            $inDnd = false;
            if ($start <= $end) {
                $inDnd = ($now >= $start && $now <= $end);
            } else {
                $inDnd = ($now >= $start || $now <= $end);
            }

            if ($inDnd) {
                Log::info("WhatsApp: Skipped for user {$userId} due to active DND Quiet Hours ({$start} to {$end}).");
                return $notification;
            }
        }

        // Resolve phone — fallback to dummy if not set
        $rawPhone = $user->phone ?? '';
        if (empty(preg_replace('/[^0-9]/', '', $rawPhone))) {
            Log::info("WhatsApp: No valid phone for user [{$userId}], skipping.");
            return $notification;
        }

        // ── 3. Route to correct template helper ───────────────────────
        $sent = false;

        if ($title === '📥 Application Submitted') {
            if (preg_match('/You have applied for (.+?)\./', $message, $m)) {
                $sent = self::notifyCandidateApplied($user, $m[1]);
            }

        } elseif ($title === '📥 New Sourced Applicant') {
            if (preg_match('/A new candidate has applied for (.+?)\./', $message, $m)) {
                $sent = self::notifyRecruiterSourced($user, $m[1]);
            }

        } elseif ($title === '🤝 Recruiter Shortlisted') {
            if (preg_match('/shortlisted your profile for (.+?) and requested/', $message, $m)) {
                // Get company name from recruiter's institution
                $companyName = 'Sourcing Institution';
                $sent = self::notifyCandidateLinedUp($user, $m[1], $companyName);
            }

        } elseif ($title === '🟢 Line Up Approved!' && $user->role === 'candidate') {
            if (preg_match('/You are Lined Up with (.+?) for (.+?)\. Coaching/', $message, $m)) {
                $sent = self::notifyLineUpApprovedCandidate($user, $m[1], $m[2]);
            }

        } elseif ($title === '🟢 Line Up Approved!' && $user->role === 'institution') {
            $candidateUser = $candidateId ? User::find($candidateId) : null;
            if (preg_match('/Admin approved Line Up for (.+?) for (.+?)\. Contact/', $message, $m)) {
                if (!$candidateUser) {
                    $candidateUser = User::where('name', $m[1])->first();
                }
                if ($candidateUser) {
                    $sent = self::notifyLineUpApprovedRecruiter($user, $candidateUser, $m[2]);
                }
            }

        } elseif ($title === '📊 Vetting Stage Updated') {
            if (preg_match('/placement funnel for (.+?) moved to: (.+?)\./', $message, $m)) {
                $sent = self::notifyPipelineStageUpdated($user, $m[1], $m[2]);
            }
        }

        // ── 4. Update sent-messages log in database ──────────────────
        \App\Models\WhatsappLog::create([
            'recipient_name'  => $user->name,
            'recipient_phone' => $rawPhone,
            'recipient_role'  => $user->role,
            'title'           => $title,
            'message'         => $message,
            'status'          => $sent ? '✅ Sent' : '⚠️ No Template Match / Skipped',
        ]);

        return $notification;
    }
}
