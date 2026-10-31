<?php

namespace App\Jobs;

use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        protected string $recipientEmail,
        protected string $templateKey,
        protected array $templateData = [],
        protected ?int $userId = null
    ) {}

    public function handle(): void
    {
        // 1. Auto-detect and fix swapped arguments ($templateKey, $recipientEmail)
        if (!str_contains($this->recipientEmail, '@') && str_contains($this->templateKey, '@')) {
            $temp = $this->recipientEmail;
            $this->recipientEmail = $this->templateKey;
            $this->templateKey = $temp;
        }

        // 2. Normalize template key aliases
        $templateKey = match ($this->templateKey) {
            'domain_expiring_reminder' => 'domain_expiring',
            'domain_renewed_confirmation' => 'domain_renewed',
            'domain_registered_confirmation' => 'domain_registered',
            default => $this->templateKey,
        };

        $template = EmailTemplate::where('template_key', $templateKey)->where('is_active', true)->first();
        if (!$template) {
            \Illuminate\Support\Facades\Log::warning("SendEmailJob: Template [{$templateKey}] not found or inactive.");
            return;
        }

        // Normalize template placeholders (support both {client_name}/{user_name}, {days_remain}/{days_left}, etc.)
        $data = array_merge([
            'company_name' => config('app.name', 'HostNibo Domain Hub'),
        ], $this->templateData);

        if (isset($data['client_name']) && !isset($data['user_name'])) {
            $data['user_name'] = $data['client_name'];
        }
        if (isset($data['days_remain']) && !isset($data['days_left'])) {
            $data['days_left'] = $data['days_remain'];
        }
        if (isset($data['expiry_date']) && !isset($data['expires_at'])) {
            $data['expires_at'] = $data['expiry_date'];
        }
        if (isset($data['renewal_url']) && !isset($data['renew_url'])) {
            $data['renew_url'] = $data['renewal_url'];
        }

        $rendered = $template->render($data);

        $status = 'sent';
        $errorMessage = null;

        try {
            Mail::html($rendered['body_html'], function ($message) use ($rendered) {
                $message->to($this->recipientEmail)
                    ->subject($rendered['subject']);
            });
        } catch (\Throwable $e) {
            $status = 'failed';
            $errorMessage = $e->getMessage();
            \Illuminate\Support\Facades\Log::error("SendEmailJob failed to {$this->recipientEmail}: " . $e->getMessage());
        }

        try {
            EmailLog::create([
                'user_id' => $this->userId,
                'recipient_email' => $this->recipientEmail,
                'template_key' => $templateKey,
                'subject' => $rendered['subject'],
                'body' => $rendered['body_html'],
                'status' => $status,
                'error_message' => $errorMessage,
                'sent_at' => ($status === 'sent') ? now() : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Log creation fallback
        }
    }
}
