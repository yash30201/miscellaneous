<?php

/**
 *
 */
require __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Datastore\DatastoreClient;

// For using emulator, uncomment the following line
// CHECK
// putenv('DATASTORE_EMULATOR_HOST=localhost:8900');

class Test
{
    private $datastore;
    const NIGHTLY_PROJECT='hw-cloudv1-sdk';

    /**
     * This is my implementiation of Deletion queue
     *
     * To use that, we can add things to deletion queue like this:-
     * $this->deletionQueue[] = (function () use ($table) {
     *     $table->delete();
     * });
     *
     *
     * @var array
     */
    private $deletionQueue = [];

    private $entityCreationCount;

    public function __construct()
    {
        $this->initiateOnce();
    }

    public function __destruct()
    {
        $this->processDeletionQueueQueue();
        echo PHP_EOL;
    }

    /**
     * This method will create a task with key as kind and some random id
     * generated at the backend.
     */
    public function getDummyEntity($kind = 'General')
    {
        if (!array_key_exists($kind, $this->entityCreationCount)) {
            $this->entityCreationCount[$kind] = 1;
        }
        $key = $this->getKey($kind, $this->entityCreationCount[$kind]);
        $task = $this->datastore->entity($key, [
            'number' => $this->entityCreationCount[$kind],
            'title' => 'card-' . (string)($this->entityCreationCount[$kind]),
            'flag' => (($this->entityCreationCount[$kind] & 1 )? true : false)
        ]);
        $this->entityCreationCount[$kind]++;
        return $task;
    }

    public function getDummyEntities(int $count = 1, $kind = 'General')
    {
        $entities = [];
        while ($count--) {
            $entities[] = $this->getDummyEntity($kind);
        }
        return $entities;
    }

    public function getKeysFromEntities($entities)
    {
        $keys = [];
        foreach($entities as $entity) {
            $keys[] = $entity->key();
        }
        return $keys;
    }

    public function upsertEntity($task, bool $toDelete = true)
    {
        // Overrides if exists
        $this->datastore->upsert($task);
        if ($toDelete) {

            $key = $task->key();
            $this->deletionQueue[] = (function () use ($key) {
                $this->datastore->delete($key);
            });
        }
    }

    public function upsertEntities($entities, bool $toDelete = true)
    {
        $count = 0;
        // Overrides if exists
        foreach($entities as $entity) {
            $this->upsertEntity($entity, $toDelete);
            ++$count;
        }
        echo "Upserted {$count} entities.";
    }

    public function insertEntity($task, bool $toDelete = true)
    {
        // Inserts only if doesn't exists.
        $this->datastore->insert($task);
        $key = $task->key();
        if ($toDelete) {
            $this->deletionQueue[] = (function () use ($key) {
                $this->datastore->delete($key);
            });
        }
    }

    public function insertEntities($entities, bool $toDelete = true)
    {
        $count = 0;
        foreach($entities as $entity) {
            $this->insertEntity($entity, $toDelete);
            ++$count;
        }
        echo "Inserted {$count} entities.";

        // You can also do insertBatch()
        // $this->datastore->insertBatch($entities);
    }

    public function getKey($kind, $id)
    {
        $key = $this->datastore->key($kind, (string)$id);
        return $key;
    }

    public function getEntity($key)
    {
        // Implemented in Entity trait
        $entity = $this->datastore->lookup($key);
        return $entity;
    }

    public function getEntities($keys)
    {
        // Implemented in Entity trait
        $entites = $this->datastore->lookupBatch($keys);
        return $entites;
    }

    public function getEntityData($key)
    {
        // Implemented in Entity trait
        $entity = $this->datastore->lookup($key);
        return $entity->get();
    }

    public function updateEntity($entity)
    {
        $this->datastore->update($entity);
    }

    public function deleteEntity($key)
    {
        $this->datastore->delete($key);
    }

    public function query1() {
        // $query = $this->datastore->query()
        // ->kind('General')
        // ->setFilter(new CompositeFilter('AND', [
        //     new PropertyFilter('number', '>', 2),
        //     new PropertyFilter('number', '<', 8)
        // ]));

        $query = $this->datastore->query()
            ->kind('General')
            ->filter('number', '>', 2)
            ->filter('number', '<', 8);

        // $query = $this->datastore->gqlQuery(
        //     'SELECT * FROM General WHERE number > 2 AND number < 8',
        //     [
        //         'allowLiterals' => true
        //     ]
        // );

        $results = $this->datastore->runQuery($query);
        foreach($results as $entity) {
           echo PHP_EOL . $entity['number'];
        }
    }

    private function initiateOnce()
    {
        echo PHP_EOL;
        // $this->datastore = new DatastoreClient([
        //     'projectId' => self::NIGHTLY_PROJECT,
        //     'namespaceId' => uniqid(
        //         '007-yash-'
        //     )
        // ]);
        $this->datastore = new DatastoreClient();
        $this->entityCreationCount['General'] = 1;
    }

    private function processDeletionQueueQueue()
    {
        while (!empty($this->deletionQueue)) {
            $call = array_pop($this->deletionQueue);
            $call();
        }
    }
}

$test = new Test();

/**
 * Entity
 */

// // Creating an entity
// $task = $test->getDummyEntity('Hello');
// $test->insertEntity($task);

// // Retreiving the entity
// $key = $test->getKey('Hello', 1);
// $entityData = $test->getEntityData($key);
// print_r($entityData);

// // Updating the entity
// $entity = $test->getEntity($key);
// $entity['title'] .= '-Updated';
// $test->updateEntity($entity);
// $entityData = $test->getEntityData($key);
// print_r($entityData);


// // Deleting the entity
// $test->deleteEntity($key);

/**
 * Batch operations
 */

// Inserting multiple entities
// $entities = $test->getDummyEntities(10);
// $test->insertEntities($entities, false);
// $keys = $test->getKeysFromEntities($entities);
// $entities = $test->getEntities($keys);
// foreach($entities['found'] as $entity) {
//     print_r($entity->get());
// }

//Query
$test->query1();
