<?php

namespace App\DTOs;

class DnsRecordDTO
{
    public function __construct(
        public ?string $id = null,
        public string $type = 'A',
        public string $name = '@',
        public string $content = '',
        public ?int $priority = null,
        public int $ttl = 3600
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'content' => $this->content,
            'priority' => $this->priority,
            'ttl' => $this->ttl,
        ];
    }
}
