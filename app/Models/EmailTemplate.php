<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    protected $fillable = [
        'template_key',
        'name',
        'subject',
        'body_html',
        'body_plain',
        'available_placeholders',
        'is_active',
    ];

    protected $casts = [
        'available_placeholders' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function render(array $data): array
    {
        $subject = $this->subject;
        $bodyHtml = $this->body_html;
        $bodyPlain = $this->body_plain ?? strip_tags($this->body_html);

        foreach ($data as $key => $val) {
            $token = '{' . $key . '}';
            $subject = str_replace($token, (string) $val, $subject);
            $bodyHtml = str_replace($token, (string) $val, $bodyHtml);
            $bodyPlain = str_replace($token, (string) $val, $bodyPlain);
        }

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_plain' => $bodyPlain,
        ];
    }
}
