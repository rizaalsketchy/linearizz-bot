<?php
// Autoload untuk Guzzle dan dependensinya
spl_autoload_register(function ($class) {
    $prefix = 'GuzzleHttp\\';
    $baseDir = __DIR__ . '/guzzlehttp/guzzle/src/';
    
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Tambahkan autoload manual untuk dependensi lain jika diperlukan
