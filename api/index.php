<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

// Jika memakai SQLite sementara di /tmp (mode demo Vercel), siapkan DB otomatis saat cold start
$dbPath = getenv('DB_DATABASE') ?: '/tmp/database.sqlite';
if ((getenv('DB_CONNECTION') ?: '') === 'sqlite' && !file_exists($dbPath)) {
    touch($dbPath);

    require __DIR__ . '/../vendor/autoload.php';
    /** @var Application $app */
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Kernel::class);
    $kernel->call('migrate', ['--force' => true, '--seed' => true]);
}

// Entrypoint untuk Vercel Serverless Function (meneruskan ke public/index.php)
require __DIR__ . '/../public/index.php';
