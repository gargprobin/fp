<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (class_exists(\App\Models\SiteSetting::class) && \Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
            $mailer = \App\Models\SiteSetting::getVal('mail_mailer', 'smtp');
            config([
                'mail.default' => $mailer,
                'mail.mailers.smtp.host' => \App\Models\SiteSetting::getVal('mail_host', config('mail.mailers.smtp.host')),
                'mail.mailers.smtp.port' => \App\Models\SiteSetting::getVal('mail_port', config('mail.mailers.smtp.port')),
                'mail.mailers.smtp.encryption' => \App\Models\SiteSetting::getVal('mail_encryption', config('mail.mailers.smtp.encryption')),
                'mail.mailers.smtp.username' => \App\Models\SiteSetting::getVal('mail_username', config('mail.mailers.smtp.username')),
                'mail.mailers.smtp.password' => \App\Models\SiteSetting::getVal('mail_password', config('mail.mailers.smtp.password')),
                'mail.from.address' => \App\Models\SiteSetting::getVal('mail_from_address', config('mail.from.address')),
                'mail.from.name' => \App\Models\SiteSetting::getVal('mail_from_name', config('mail.from.name')),
                'services.mailgun.domain' => \App\Models\SiteSetting::getVal('mailgun_domain', config('services.mailgun.domain')),
                'services.mailgun.secret' => \App\Models\SiteSetting::getVal('mailgun_secret', config('services.mailgun.secret')),
                'services.mailgun.endpoint' => \App\Models\SiteSetting::getVal('mailgun_endpoint', config('services.mailgun.endpoint')),
            ]);
        }

        // Register Global Email Logger
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Mail\Events\MessageSent::class,
            function (\Illuminate\Mail\Events\MessageSent $event) {
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('email_logs')) {
                        $message = $event->message;
                        
                        // Extract recipients
                        $to = collect($message->getTo() ?? [])
                            ->map(fn($address) => $address->getAddress())
                            ->implode(', ');
                        
                        $subject = $message->getSubject() ?? '(No Subject)';
                        
                        // Extract body content (HTML or Text)
                        $body = $message->getHtmlBody() ?: $message->getTextBody();
                        
                        \App\Models\EmailLog::create([
                            'recipient_email' => $to ?: '(Unknown)',
                            'subject' => $subject,
                            'body' => $body ?: '(Empty Body)',
                            'status' => 'sent',
                        ]);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to log sent email: ' . $e->getMessage());
                }
            }
        );
    }
}
