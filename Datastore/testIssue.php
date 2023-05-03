<?php

require __DIR__ . '/vendor/autoload.php';
use Google\Cloud\Datastore\DatastoreClient;

$datastore = new DatastoreClient([
    'transport' => 'rest'
]);

$query = $datastore->query()
    ->kind('General')
    ->filter('number', '>', 2)
    ->filter('number', '>', 5)
    ->filter('title', '=', 'card-5')
    ->limit(400);

$result = $datastore->runQuery($query);

foreach ($result as $entity) {
    var_dump($entity['number']);
}

