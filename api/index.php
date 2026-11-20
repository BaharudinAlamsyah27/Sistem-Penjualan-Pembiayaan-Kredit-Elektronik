<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

// Siapkan SQLite sementara di /tmp dengan file lock agar aman dari request paralel (mis. / dan /favicon.png)
$dbPath = getenv('DB_DATABASE') ?: '/tmp/database.sqlite';
$readyFlag = '/tmp/.nexaerp_db_ready';

if ((getenv('DB_CONNECTION') ?: '') === 'sqlite' && !file_exists($readyFlag)) {
    $lockHandle = fopen('/tmp/.nexaerp_db.lock', 'c');
    if ($lockHandle && flock($lockHandle, LOCK_EX)) {
        if (!file_exists($readyFlag)) {
            if (!file_exists($dbPath)) {
                touch($dbPath);
            }

            require __DIR__ . '/../vendor/autoload.php';
            /** @var Application $app */
            $app = require __DIR__ . '/../bootstrap/app.php';
            $kernel = $app->make(Kernel::class);
            $kernel->call('migrate', ['--force' => true, '--seed' => true]);

            touch($readyFlag);
        }
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
    }
}

// Entrypoint untuk Vercel Serverless Function (meneruskan ke public/index.php)
require __DIR__ . '/../public/index.php';
