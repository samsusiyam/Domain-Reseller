<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SystemHealthController extends Controller
{
    public function index()
    {
        // 1. Database Health
        $dbStatus = false;
        $dbLatency = null;
        $dbSizeMb = 0;
        $dbTableCount = 0;

        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $dbLatency = round((microtime(true) - $start) * 1000, 2);
            $dbStatus = true;

            $dbName = config('database.connections.mysql.database');
            $sizeRow = DB::select("
                SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb,
                       COUNT(*) as table_count
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ", [$dbName]);

            if (!empty($sizeRow)) {
                $dbSizeMb = round($sizeRow[0]->size_mb ?? 0, 2);
                $dbTableCount = (int) ($sizeRow[0]->table_count ?? 0);
            }
        } catch (\Throwable $e) {
            $dbStatus = false;
        }

        // 2. Storage & Disk
        $diskTotal = disk_total_space(base_path());
        $diskFree = disk_free_space(base_path());
        $diskUsed = $diskTotal - $diskFree;
        $diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 1) : 0;

        // 3. Registrar API Health
        $registrars = [
            'Spaceship' => [
                'url' => 'https://spaceship.dev/',
                'status' => false,
                'latency' => null,
            ],
            'Namecheap' => [
                'url' => 'https://api.namecheap.com/xml.response',
                'status' => false,
                'latency' => null,
            ],
            'Dynadot' => [
                'url' => 'https://api.dynadot.com/api3.json',
                'status' => false,
                'latency' => null,
            ],
            'RDAP Identity Digital' => [
                'url' => 'https://rdap.identitydigital.services/rdap/help',
                'status' => false,
                'latency' => null,
            ],
        ];

        foreach ($registrars as $name => &$data) {
            try {
                $tStart = microtime(true);
                $res = Http::timeout(3)->get($data['url']);
                $data['latency'] = round((microtime(true) - $tStart) * 1000, 0);
                $data['status'] = $res->status() < 500;
            } catch (\Throwable $e) {
                $data['status'] = false;
                $data['error'] = $e->getMessage();
            }
        }

        // 4. Server Info
        $serverInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'LiteSpeed / Linux',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'opcache_enabled' => function_exists('opcache_get_status') && !empty(opcache_get_status()),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'cron_active' => true,
        ];

        return view('admin.system-health.index', compact('dbStatus', 'dbLatency', 'dbSizeMb', 'dbTableCount', 'diskTotal', 'diskUsed', 'diskFree', 'diskPercent', 'registrars', 'serverInfo'));
    }
}
