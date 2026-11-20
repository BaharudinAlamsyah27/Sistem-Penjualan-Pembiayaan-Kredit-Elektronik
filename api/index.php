<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

try {
    // Pastikan folder storage sementara di /tmp tersedia (Vercel filesystem bersifat read-only)
    $tmpStorage = '/tmp/storage';
    foreach ([
        $tmpStorage . '/app/public',
        $tmpStorage . '/framework/cache/data',
        $tmpStorage . '/framework/sessions',
        $tmpStorage . '/framework/testing',
        $tmpStorage . '/framework/views',
        $tmpStorage . '/logs',
    ] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    require __DIR__ . '/../vendor/autoload.php';

    /** @var Application $app */
    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath($tmpStorage);

    // Baca env dari $_ENV / $_SERVER / getenv() karena php.ini serverless sering memakai variables_order=GPCS
    $dbConnection = $_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: '';
    $dbPath = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '/tmp/database.sqlite';
    $readyFlag = '/tmp/.nexaerp_db_ready';

    if ($dbConnection === 'sqlite' && !file_exists($readyFlag)) {
        $lockHandle = fopen('/tmp/.nexaerp_db.lock', 'c');
        if ($lockHandle && flock($lockHandle, LOCK_EX)) {
            if (!file_exists($readyFlag)) {
                if (!file_exists($dbPath)) {
                    touch($dbPath);
                }
                $app->make(Kernel::class)->call('migrate', ['--force' => true, '--seed' => true]);
                touch($readyFlag);
            }
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    error_log('NexaERP Vercel Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo 'Application Error: ' . htmlspecialchars($e->getMessage());
}
