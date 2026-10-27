<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
                'url' => 'https://spaceship.dev',
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

        // 5. Existing Backups
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $files = File::files($backupDir);
        $backups = [];
        foreach ($files as $file) {
            if (str_ends_with($file->getFilename(), '.sql.gz')) {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'size_mb'  => round($file->getSize() / 1024 / 1024, 2),
                    'created_at' => \Carbon\Carbon::createFromTimestamp($file->getMTime()),
                ];
            }
        }

        usort($backups, fn($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        return view('admin.system-health.index', compact(
            'dbStatus', 'dbLatency', 'dbSizeMb', 'dbTableCount',
            'diskTotal', 'diskUsed', 'diskFree', 'diskPercent',
            'registrars', 'serverInfo', 'backups'
        ));
    }

    public function createBackup()
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = 'backup-' . date('Y-m-d-His') . '.sql.gz';
        $filepath = $backupDir . '/' . $filename;

        try {
            $dbName = config('database.connections.mysql.database');
            $tables = DB::select('SHOW TABLES');
            $keyName = "Tables_in_{$dbName}";

            $gz = gzopen($filepath, 'w9');
            gzwrite($gz, "-- HostNibo Domain Hub Database Snapshot
");
            gzwrite($gz, "-- Timestamp: " . date('Y-m-d H:i:s') . "

");
            gzwrite($gz, "SET FOREIGN_KEY_CHECKS=0;

");

            foreach ($tables as $tableObj) {
                $table = $tableObj->$keyName ?? array_values((array)$tableObj)[0];

                $createTable = DB::select("SHOW CREATE TABLE ");
                $sql = $createTable[0]->{'Create Table'} ?? '';
                gzwrite($gz, "DROP TABLE IF EXISTS ;
");
                gzwrite($gz, $sql . ";

");

                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    foreach ($rows->chunk(300) as $chunk) {
                        $insertSql = "INSERT INTO  VALUES ";
                        $vals = [];
                        foreach ($chunk as $row) {
                            $escapedRow = array_map(function ($val) {
                                if (is_null($val)) return 'NULL';
                                return DB::getPdo()->quote($val);
                            }, (array)$row);
                            $vals[] = "(" . implode(', ', $escapedRow) . ")";
                        }
                        $insertSql .= implode(",
", $vals) . ";

";
                        gzwrite($gz, $insertSql);
                    }
                }
            }

            gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;
");
            gzclose($gz);

            $sizeMb = round(File::size($filepath) / 1024 / 1024, 2);
            return redirect()->route('admin.system-health')->with('success', "Database backup created successfully: {$filename} ({$sizeMb} MB)");
        } catch (\Throwable $e) {
            return redirect()->route('admin.system-health')->with('error', 'Failed to generate database dump: ' . $e->getMessage());
        }
    }

    public function downloadBackup(string $filename): BinaryFileResponse
    {
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.sql\.gz$/', $filename)) {
            abort(400, 'Invalid filename');
        }

        $filepath = storage_path('app/backups/' . $filename);
        if (!File::exists($filepath)) {
            abort(404, 'Backup file not found');
        }

        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/gzip',
        ]);
    }

    public function deleteBackup(string $filename)
    {
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.sql\.gz$/', $filename)) {
            abort(400, 'Invalid filename');
        }

        $filepath = storage_path('app/backups/' . $filename);
        if (File::exists($filepath)) {
            File::delete($filepath);
            return back()->with('success', "Backup {$filename} deleted.");
        }

        return back()->with('error', 'Backup file not found.');
    }
}
