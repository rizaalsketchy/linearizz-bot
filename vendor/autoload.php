<?php
// Autoload untuk Guzzle dan dependensinya
spl_autoload_register(function ($class) {
    // Konfigurasi autoload untuk Guzzle
    $prefixes = [
        'GuzzleHttp\\' => __DIR__ . '/guzzlehttp/guzzle/src/',
        'Psr\\Http\\Client\\' => __DIR__ . '/psr/http-client/src/',
        'Psr\\Http\\Message\\' => __DIR__ . '/psr/http-message/src/',
        'ralouphie\\' => __DIR__ . '/ralouphie/'
    ];
    
    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($prefix, $class, strlen($prefix)) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});
