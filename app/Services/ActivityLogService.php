<?php

namespace App\Services;

use App\Contracts\ActivityLogger;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class ActivityLogService implements ActivityLogger
{
    /**
     * Mencatat aktivitas ke dalam log
     *
     * @param string $action
     * @param string $description
     * @param array $properties
     * @return void
     */
    public function log($action, $description, array $properties = [])
    {
        Log::info("Logging activity: $action - $description", $properties);

        ActivityLog::create([
            'admin_id' => auth('admin')?->id,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip()

        ]);
    }
}
