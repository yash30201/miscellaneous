<?php

/**
 * This file is used to run php scripts for testing
 *
 * Place this file in tests/System/
 */

require __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\BigQuery\Storage\V1\BigQueryReadClient;
use Google\Cloud\BigQuery\Storage\V1\DataFormat;
use Google\Cloud\BigQuery\Storage\V1\ReadSession;
use Google\Cloud\BigQuery\Storage\V1\ReadSession\TableModifiers;
use Google\Cloud\BigQuery\Storage\V1\ReadSession\TableReadOptions;
use Google\Protobuf\Timestamp;

$client = new BigQueryReadClient();


$project = $client->projectName('bigquery-public-data');

$table = $client->tableName('bigquery-public-data', 'usa_names', 'usa_1910_current');

$snapshotMillis = null;
$readOptions = new TableReadOptions();
$readOptions->setRowRestriction('state = "WA"');
$readOptions->setSelectedFields(['name', 'number', 'state']);

$readSession = new ReadSession();
$readSession->setTable($table)
    ->setDataFormat(DataFormat::AVRO)
    ->setReadOptions($readOptions);


if ($snapshotMillis != null) {
    $timestamp = new Timestamp();
    $timestamp->setSeconds($snapshotMillis / 1000);
    $timestamp->setNanos((int)($snapshotMillis % 1000) * 1000000);
    $tableModifier = new TableModifiers();
    $tableModifier->setSnapshotTime($timestamp);
    $readSession->setTableModifiers($tableModifier);
}

$session = $client->createReadSession(
    $project,
    $readSession,
    [
        'maxStreamCount' => 1
    ]
);

$stream = $client->readRows([
    'readStream' => $session->getStreams()[0]->getName()
]);

foreach ($stream->readAll() as $response) {
    printf(
        'Discovered %s rows in response.' . PHP_EOL,
        $response->getRowCount()
    );
}
