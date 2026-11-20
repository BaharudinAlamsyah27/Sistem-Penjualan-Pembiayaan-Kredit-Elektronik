<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

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

// Inisialisasi SQLite sementara di /tmp dengan file lock agar aman dari request paralel
$dbPath = getenv('DB_DATABASE') ?: '/tmp/database.sqlite';
$readyFlag = '/tmp/.nexaerp_db_ready';

if ((getenv('DB_CONNECTION') ?: '') === 'sqlite' && !file_exists($readyFlag)) {
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
