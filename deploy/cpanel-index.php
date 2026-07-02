<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$basePath = '/home/grupo17sc/proyecto2_app';
$publicPath = '/home/grupo17sc/proyecto2';
$logFile = $basePath.'/storage/logs/cpanel-bootstrap.log';

file_put_contents(
    $logFile,
    '['.date('Y-m-d H:i:s').'] ENTER index.php '
    .'METHOD='.($_SERVER['REQUEST_METHOD'] ?? '')
    .' REQUEST_URI='.($_SERVER['REQUEST_URI'] ?? '')
    .' SCRIPT_NAME='.($_SERVER['SCRIPT_NAME'] ?? '')
    .' PHP_SELF='.($_SERVER['PHP_SELF'] ?? '')
    .' SCRIPT_FILENAME='.($_SERVER['SCRIPT_FILENAME'] ?? '')
    .PHP_EOL,
    FILE_APPEND
);

try {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $logFile);
    error_reporting(E_ALL);

    $baseUri = '/inf513/grupo17sc/proyecto2';

    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
        $query = '';

        if (($pos = strpos($uri, '?')) !== false) {
            $query = substr($uri, $pos);
            $uri = substr($uri, 0, $pos);
        }

        if ($uri === $baseUri || $uri === $baseUri.'/') {
            $_SERVER['REQUEST_URI'] = '/'.$query;
            $_SERVER['PATH_INFO'] = '/';
            $_SERVER['ORIG_PATH_INFO'] = '/';
        } elseif (strpos($uri, $baseUri.'/') === 0) {
            $path = substr($uri, strlen($baseUri));

            if ($path === '') {
                $path = '/';
            }

            $_SERVER['REQUEST_URI'] = $path.$query;
            $_SERVER['PATH_INFO'] = $path;
            $_SERVER['ORIG_PATH_INFO'] = $path;
        }
    }

    $_SERVER['SCRIPT_NAME'] = $baseUri.'/index.php';
    $_SERVER['PHP_SELF'] = $baseUri.'/index.php';
    $_SERVER['SCRIPT_FILENAME'] = $publicPath.'/index.php';
    $_SERVER['DOCUMENT_ROOT'] = dirname($publicPath);

    if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
        require $maintenance;
    }

    require $basePath.'/vendor/autoload.php';

    $app = require_once $basePath.'/bootstrap/app.php';

    $app->bind('path.public', function () use ($publicPath) {
        return $publicPath;
    });

    $kernel = $app->make(Kernel::class);

    $response = $kernel->handle(
        $request = Request::capture()
    )->send();

    $kernel->terminate($request, $response);
} catch (Throwable $e) {
    file_put_contents(
        $logFile,
        '['.date('Y-m-d H:i:s').'] ERROR '
        .$e::class.': '.$e->getMessage()
        .' in '.$e->getFile().':'.$e->getLine()
        .PHP_EOL.$e->getTraceAsString()
        .PHP_EOL.PHP_EOL,
        FILE_APPEND
    );

    http_response_code(500);
    echo 'Error interno. Revisar storage/logs/cpanel-bootstrap.log';
}
