<?php
/**
 * This file is used to run php scripts for testing
 *
 * Place this file in tests/System/
 */

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\BigQuery\Dataset;
use Google\Cloud\BigQuery\Table;

class Experiment
{
    /**
     * Project Id of the project to work upon
     * @var string
     */
    private $projectId;

    /**
     * Dataset ID
     * @var string
     */
    private $datasetId;

    /**
     * Table Id inside to dataset defined by datasetID above.
     * @var string
     */
    private $tableId;

    /**
     * A BigQueryClient for the project with the above project ID.
     * @var BigQueryClient
     */
    private $client;

    /**
     * Dataset instance
     * @var Dataset
     */
    private $dataset;

    /**
     * Table instance
     * @var Table
     */
    private $table;

    /**
     * Deletion Queue
    */
    private $deletionQueue = [];

    public function __construct()
    {
        $this->initiateOnce();
    }

    public function __destruct()
    {
        $this->processDeletionQueueQueue();
    }

    public function tableDataList()
    {
        $options = ['selectedFields' => 'Name,Age'];
        $rows = $this->table->rows($options);
        $rows = iterator_to_array($rows);
        var_dump($rows);
    }

    private function initiateOnce()
    {
        $this->projectId = $this->projectId ?? getenv('GOOGLE_CLOUD_PROJECT');
        $this->datasetId = $this->datasetId ?? uniqid('dataset_');
        $this->tableId = $this->tableId ?? uniqid('table_');

        $this->client = $this->client ?? new BigQueryClient([
            'suppressKeyFileNotice' => true
        ]);
        $this->dataset = $this->dataset ?? $this->createDataset(
            $this->client,
            $this->datasetId,
        );
        $this->table = $this->table ?? $this->createTable(
            $this->dataset,
            $this->tableId
        );

        try {
            $this->insertData();
        } catch (\Throwable $th) {
            echo $th->getMessage() . PHP_EOL;
        }

    }

    private function createDataset(
        BigQueryClient $client,
        string $datasetId,
        array $options = []
    ): Dataset {
        $dataset = $client->createDataset($datasetId, $options);

        $this->deletionQueue[] = (function () use ($dataset) {
            $dataset->delete(['deleteContents' => true]);
        });

        echo PHP_EOL . '----------------------' . PHP_EOL;
        echo "Dataset created.\n";
        return $dataset;
    }

    private function createTable(
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
        $this->deletionQueue[] = (function () use ($table) {
            $table->delete();
        });

        echo PHP_EOL . '----------------------' . PHP_EOL;
        echo "Table created.\n";
        return $table;
    }


    private function processDeletionQueueQueue()
    {
        while(!empty($this->deletionQueue)) {
            $call = array_pop($this->deletionQueue);
            $call();
        }
    }
    private function insertData()
    {
        $data = $this->getDefaultRowData();
        $this->table->insertRows($data);

        echo PHP_EOL . '--------------------' . PHP_EOL;
        echo "Inserted Data\n";
    }

    private function getDefaultRowData()
    {
        $data = file_get_contents(__DIR__ . '/data/table-data.json');
        $data = json_decode($data, true);
        foreach($data as &$row) {
            $row = ['data' => $row];
        }
        return $data;
    }
}


$z = new Experiment();
$z->tableDataList();



