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


$project = $client->projectName('yashsahu-dev-test');

$table = $client->tableName('bigquery-public-data', 'usa_names', 'usa_1910_current');

$snapshotMillis = null;
$readOptions = new TableReadOptions();
$readOptions->setSelectedFields(['name', 'number', 'state']);
$readOptions->setRowRestriction('state = "WA"');

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

$stream = $client->readRows($session->getStreams()[0]->getName());

$schema = '';
foreach ($stream->readAll() as $response) {
    $data = $response->getAvroRows()->getSerializedBinaryRows();
    if ($response->hasAvroSchema()) {
        $schema = $response->getAvroSchema()->getSchema();
    }
    // printf("Rows Count: %d\n", $response->getRowCount());
    $avroSchema = AvroSchema::parse($schema);
    $readIO = new \AvroStringIO($data);
    $datumReader = new \AvroIODatumReader($avroSchema);
    $record = [];
    while (!$readIO->is_eof()) {
        $record[] = $datumReader->read(new \AvroIOBinaryDecoder($readIO));
    }
    printf(
        "Matched: %s\n",
        count($record) == $response->getRowCount() ? 'True' : 'False'
    );
    // break;
}
