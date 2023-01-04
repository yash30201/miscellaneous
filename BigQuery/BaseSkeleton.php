<?php
/**
 * This file is used to run php scripts for testing
 *
 * Place this file in tests/System/
 */

namespace Google\Cloud\BigQuery\Tests\System;

require __DIR__ . '/../../vendor/autoload.php';

use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\BigQuery\Dataset;
use Google\Cloud\BigQuery\Table;
use Google\Cloud\Core\Testing\System\DeletionQueue;

class Experiment
{
    /**
     * Project Id of the project to work upon
     * @var string
     */
    private static $projectId;

    /**
     * Dataset ID
     * @var string
     */
    private static $datasetId;

    /**
     * Table Id inside to dataset defined by datasetID above.
     * @var string
     */
    private static $tableId;

    /**
     * A BigQueryClient for the project with the above project ID.
     * @var BigQueryClient
     */
    private static $client;

    /**
     * Dataset instance
     * @var Dataset
     */
    private static $dataset;

    /**
     * Table instance
     * @var Table
     */
    private static $table;

    /**
     * The deletion queue object to delete the created resources
     * @var mixed
     */
    private static $deletionQueue;

    public static function initiateOnce()
    {
        self::setupQueue();

        self::$projectId = self::$projectId ?? getenv('GOOGLE_CLOUD_PROJECT');
        self::$datasetId = self::$datasetId ?? uniqid('dataset_');
        self::$tableId = self::$tableId ?? uniqid('table_');

        self::$client = self::$client ?? new BigQueryClient([
            'suppressKeyFileNotice' => true
        ]);
        self::$dataset = self::$dataset ?? self::createDataset(
            self::$client,
            self::$datasetId,
        );
        self::$table = self::$table ?? self::createTable(
            self::$dataset,
            self::$tableId
        );
    }

    public static function createDataset(
        BigQueryClient $client,
        string $datasetId,
        array $options = []
    ): Dataset {
        $dataset = $client->createDataset($datasetId, $options);

        self::$deletionQueue->add(function () use ($dataset) {
            $dataset->delete(['deleteContents' => true]);
        });

        echo PHP_EOL . '----------------------' . PHP_EOL;
        echo "Dataset created.\n";
        return $dataset;
    }

    public static function createTable(
        Dataset $dataset,
        string $tableId,
        array $options =[]
    ): Table {
        if (!isset($options['schema'])) {
            $options['schema']['fields'] = json_decode(
                file_get_contents(__DIR__ . '/data/table-schema.json'),
                true
            );
        }

        $table = $dataset->createTable($tableId, $options);
        self::$deletionQueue->add(function () use ($table) {
            $table->delete();
        });

        echo PHP_EOL . '----------------------' . PHP_EOL;
        echo "Table created.\n";
        return $table;
    }

    private static function setupQueue()
    {
        if (!self::$deletionQueue) {
            self::$deletionQueue = new DeletionQueue();
        }
    }

    public static function processQueue()
    {
        self::$deletionQueue->process();
    }
}


Experiment::initiateOnce();
