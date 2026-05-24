<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

try {

    $app = require __DIR__ . '/../bootstrap/app.php';

    $app->run();

} catch (\Throwable $e) {

    echo '<pre>';

    echo "MESSAGE:\n";
    echo $e->getMessage();

    echo "\n\nFILE:\n";
    echo $e->getFile();

    echo "\n\nLINE:\n";
    echo $e->getLine();

    echo "\n\nTRACE:\n";
    echo $e->getTraceAsString();

    echo '</pre>';
}