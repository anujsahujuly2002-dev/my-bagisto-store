<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;

$root = dirname(__DIR__, 5);

require $root.'/vendor/autoload.php';

$app = require $root.'/bootstrap/app.php';

$queries = [];

$app->afterResolving('db', function ($db) use (&$queries) {
    $db->listen(function (QueryExecuted $query) use (&$queries) {
        $queries[] = ['connection' => $query->connectionName, 'sql' => $query->sql];
    });
});

$kernel = $app->make(Kernel::class);

$app->instance('request', Request::create('http://localhost/'));

$kernel->bootstrap();

$booted = $queries;

$statuses = [];

foreach (array_slice($argv, 1) as $url) {
    $response = $kernel->handle($request = Request::create($url));

    $statuses[$url] = $response->getStatusCode();

    $kernel->terminate($request, $response);
}

echo json_encode([
    'default' => $app['config']['database.default'],
    'booted' => $booted,
    'queries' => $queries,
    'statuses' => $statuses,
]);
