<?php

use Google\ApiCore\ApiException;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\CreateInstanceRequest;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Cloud\Bigtable\Admin\V2\Instance\Type as InstanceType;
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;
use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\Cloud\Bigtable\Admin\V2\Table;
use Google\Cloud\Bigtable\BigtableClient;
use Google\Cloud\Bigtable\Mutations;
use Google\Cloud\Bigtable\V2\RowFilter;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * PHP library documentation page:
 *      https://cloud.google.com/bigtable/docs/reference/libraries#client-libraries-install-php
 */


class Hello
{
    public $instanceAdminClient;
    public $tableAdminClient;
    public $dataClient;

    public $projectId;

    public function __construct()
    {
        $this->instanceAdminClient = new BigtableInstanceAdminClient();
        $this->tableAdminClient = new BigtableTableAdminClient();
        $this->dataClient = new BigtableClient();
        $this->projectId = getenv('GOOGLE_CLOUD_PROJECT');
    }

    public function createInstance(
        bool $toDelete = true,
        string $instanceId = 'dummy-instance',
        string $projectId = 'yashsahu-dev-test',
        string $clusterId = 'dummy-cluster',
        string $locationId = 'asia-south1-a'
    ) {
        if ($toDelete) {
            $instanceId = uniqid('instance-');
            $clusterId = uniqid('cluster-');
        }
        $projectName = $this->instanceAdminClient->projectName($projectId);
        $instanceName = $this->instanceAdminClient->instanceName($projectId, $instanceId);

        $initialServeNodes = 2;
        $storageType = StorageType::SSD;
        $instanceType = InstanceType::PRODUCTION;
        $labels = ['prod-label' => 'instance_delta'];

        $instance = new Instance();
        $instance->setDisplayName($instanceId)
            ->setLabels($labels)
            ->setType($instanceType);

        $cluster = new Cluster();
        $location = $this->instanceAdminClient->locationName($projectId, $locationId);
        $cluster->setDefaultStorageType($storageType)
            ->setLocation($location)
            ->setServeNodes($initialServeNodes);
        $clusters = [$clusterId => $cluster];

        try {
            $this->instanceAdminClient->getInstance($instanceName);
            printf('Instance %s already exisits.' . PHP_EOL, $instanceId);
        } catch (ApiException $err) {
            if ($err->getStatus() === 'NOT_FOUND') {
                printf('Creating an Instance: %s' . PHP_EOL, $instanceId);
                $operationResponse = $this->instanceAdminClient->createInstance(
                    $projectName,
                    $instanceId,
                    $instance,
                    $clusters
                );
                $this->processOperation($operationResponse);
                if ($toDelete) {
                    sleep(2);
                    printf('Now deleting this instance' . PHP_EOL);
                    $this->deleteInstance($this->projectId, $instanceId);
                }
            } else {
                throw $err;
            }
        }
    }

    public function createTable(
        string $instanceId,
        string $tableId
    ) {
        $instanceName = $this->instanceAdminClient->instanceName($this->projectId, $instanceId);
        $tableName = $this->tableAdminClient->tableName($this->projectId, $instanceId, $tableId);

        try {
            $table = $this->tableAdminClient->getTable($tableName);
            printf("Table $tableId already exists."  .PHP_EOL);
        } catch (ApiException $e) {
            if ($e->getStatus() == "NOT_FOUND") {
                printf('Creating a Table: %s' . PHP_EOL, $tableId);
                $table = new Table();
                $table = $this->tableAdminClient->createTable($instanceName, $tableId, $table);

                $columnFamily = new ColumnFamily();
                $columnModification = new Modification();
                $columnModification->setId('family1');
                $columnModification->setCreate($columnFamily);
                $this->tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);

                printf('Created Table %s' . PHP_EOL, $tableId);
            } else {
                printf("Failed creating table.\n Error message:");
                echo $e->getMessage() . PHP_EOL;
            }
        }
    }

    public function insertData($instanceId, $tableId)
    {
        $table = $this->dataClient->table($instanceId, $tableId);
        printf('Writing some data to the table.' . PHP_EOL);
        $values = ['Alpha', 'Beta', 'Gamma'];
        $columnFamily = 'family1';
        $columnName = 'greeks';
        $mutations = [];
        foreach ($values as $i => $value) {
            $rowKey = sprintf('value%s', $i);
            $rowMutation = new Mutations();
            $rowMutation->upsert($columnFamily, $columnName, $value, time() * 1000000);
            $mutations[$rowKey] = $rowMutation;
        }
        $table->mutateRows($mutations);
    }

    public function insertHugeData($instanceId, $tableId, $howMany = 10, $columnName = 'greeks')
    {
        $table = $this->dataClient->table($instanceId, $tableId);
        printf('Writing %s data elements to the table.' . PHP_EOL, $howMany);
        $columnFamilie = 'family1';
        $mutations = [];
        for ($i = 0 ; $i < $howMany ; $i++) {
            $rowKey = sprintf('%s%s', $columnName, $i);
            $rowMutation = new Mutations();
            $rowMutation->upsert($columnFamilie, $columnName, 'value' . time() * 1000000);
            $mutations[$rowKey] = $rowMutation;
        }
        $table->mutateRows($mutations);
    }

    public function deleteAllInstances($projectId = 'yashsahu-dev-test')
    {
        $counter = 0;
        $projectName = $this->instanceAdminClient->projectName($projectId);
        $instances = $this->instanceAdminClient
            ->listInstances($projectName)
            ->getInstances()
            ->getIterator();
        foreach ($instances as $instance) {
            $instanceInfo = $this->instanceAdminClient->parseName($instance->getName());
            $this->deleteInstance($this->projectId, $instanceInfo['instance']);
            ++$counter;
        }
        printf('Deleted %d instances' . PHP_EOL, $counter);
    }

    public function getARowsLatestValue($instanceId, $tableId, $rowKey)
    {
        printf('Getting a single greeting by row key.' . PHP_EOL);

        // Only retrieve the most recent version of the cell.
        $rowFilter = (new RowFilter())->setCellsPerColumnLimitFilter(1);

        $column = 'greeks';
        $columnFamilyId = 'family1';

        $row = $this->dataClient->table($instanceId, $tableId)->readRow($rowKey, [
            'rowFilter' => $rowFilter
        ]);
        printf('Value of row is: %s' . PHP_EOL, $row[$columnFamilyId][$column][0]['value']);
    }

    public function readRowRanges($instanceId, $tableId, $columnName, $startKey, $endKey, $flag = true)
    {
        $table = $this->dataClient->table($instanceId, $tableId);
        $rows = $table->readRows([
            'rowRanges' => [
                [
                    'startKeyClosed' => $startKey,
                    'endKeyClosed' => $endKey
                ]
            ],
        ]);
        if ($flag) {
            $data = iterator_to_array($rows);
            foreach ($data as $key => $row) {
                // printf('Value of row %s is: %s' . PHP_EOL,$key, $row['family1'][$columnName][0]['value']);
            }
        } else {
            $counter = 0;
            foreach ($rows as $key => $row) {
                $counter++;
            }
            printf('Fetched %d rows' . PHP_EOL, $counter);
        }

    }

    public function customReadRows($instanceId, $tableId, $columnName)
    {
        $table = $this->dataClient->table($instanceId, $tableId);
$id = uniqid();
$rows = $table->readRows([
    'rowRanges' => [
        [
            'startKeyOpen' => $id . '0',
            'endKeyOpen' => $id . '1',
        ]
    ]
]);
        $data = iterator_to_array($rows);
        foreach ($data as $key => $row) {
            printf('Value of row %s is: %s' . PHP_EOL, $key, $row['family1'][$columnName][0]['value']);
        }
    }

    public function deleteInstance(
        string $projectId,
        string $instanceId
    ): void {
        $instanceName = $this->instanceAdminClient->instanceName($projectId, $instanceId);

        printf('Deleting Instance' . PHP_EOL);
        try {
            $this->instanceAdminClient->deleteInstance($instanceName);
            printf('Deleted Instance: %s.' . PHP_EOL, $instanceId);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                printf('Instance %s does not exists.' . PHP_EOL, $instanceId);
            } else {
                throw $e;
            }
        }
    }

    private function processOperation($operationResponse)
    {
        $operationResponse->pollUntilComplete();
        if(!$operationResponse->operationSucceeded()) {
            print('Error: ' . $operationResponse->getError()->getMessage());
        } else {
            printf('Operation succeded' . PHP_EOL);
        }
    }
}

$hello = new Hello();
$instanceId = 'france';
$tableId = 'paris';

// CREATE INSTANCE
// $hello->createInstance(false, 'france');

// DELETE ALL INSTANCES
// $hello->deleteAllInstances();

// CREATE TABLE
// $hello->createTable($instanceId, $tableId);

// INSERT DATA INTO TABLE
// $hello->insertData($instanceId, $tableId);

// INSERT bgi dummy data into TABLE
// $hello->insertHugeData($instanceId, $tableId, 10, 'ex');

// GET LATEST ROW VALUE
// $hello->getARowsLatestValue($instanceId, $tableId, 'value1');

// READ ROW RANGES
// $hello->readRowRanges($instanceId, $tableId, 'ex', 'ex100300', 'ex100320', true);

// CUSTOM READ ROWS
$hello->customReadRows($instanceId, $tableId, 'ex');
