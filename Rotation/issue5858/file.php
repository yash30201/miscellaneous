<?php

use Google\ApiCore\ApiException;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\Table;
use Google\Cloud\Bigtable\Admin\V2\Table\View;
use Google\Cloud\Bigtable\BigtableClient;

/**
 * Issue link: https://github.com/googleapis/google-cloud-php/issues/5858
 */

require __DIR__ . '/../../vendor/autoload.php';

class Test
{
    private $projectId;
    private $instanceId = 'alpha01';
    private $clusterId = 'asia-south1-a';
    private $instanceAdminClient;
    private $tableAdminClient;
    private $bigtableClient;
    private $tableId;
    public function __construct()
    {
        $this->bigtableClient = new BigtableClient();
        $this->tableAdminClient = new BigtableTableAdminClient();
        $this->instanceAdminClient = new BigtableInstanceAdminClient();
        $this->projectId = getenv('GOOGLE_PROJECT_ID');
        $this->tableId = 'table01';
        $this->createTable();
    }

    public function createTable()
    {
        $instanceName = $this->instanceAdminClient->instanceName($this->projectId, $this->instanceId);
        $tableName = $this->tableAdminClient->tableName($this->projectId, $this->instanceId, $this->tableId);

        // Check whether table exists in an instance.
        // Create table if it does not exists.
        $table = new Table();
        printf('Creating a Table : %s' . PHP_EOL, $this->tableId);

        $columns = ['cfam1' => new ColumnFamily()];
        $table = (new Table())->setColumnFamilies($columns);

        try {
            $this->tableAdminClient->getTable($tableName, ['view' => View::NAME_ONLY]);
            printf('Table %s already exists' . PHP_EOL, $this->tableId);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                printf('Creating the %s table' . PHP_EOL, $this->tableId);

                $this->tableAdminClient->createtable(
                    $instanceName,
                    $this->tableId,
                    $table
                );
                printf('Created table %s' . PHP_EOL, $this->tableId);
            } else {
                throw $e;
            }
        }
    }

}
