<?php

namespace App\Services\Audit;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Record an audit activity event.
     */
    public function log(
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $properties = null,
        string $logName = 'default'
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'log_name' => $logName,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
