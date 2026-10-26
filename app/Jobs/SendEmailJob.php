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
        $template = EmailTemplate::where('template_key', $this->templateKey)->where('is_active', true)->first();
        if (!$template) {
            return;
        }

        $rendered = $template->render(array_merge([
            'company_name' => config('app.name', 'DomainReseller'),
        ], $this->templateData));

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
        }

        EmailLog::create([
            'user_id' => $this->userId,
            'recipient_email' => $this->recipientEmail,
            'template_key' => $this->templateKey,
            'subject' => $rendered['subject'],
            'body' => $rendered['body_html'],
            'status' => $status,
            'error_message' => $errorMessage,
            'sent_at' => ($status === 'sent') ? now() : null,
            'created_at' => now(),
        ]);
    }
}
