<?php

use Google\Cloud\Firestore\Filter;
use Google\Cloud\Firestore\FirestoreClient;

require_once __DIR__ . '/../vendor/autoload.php';

class God
{
    private FirestoreClient $client;

    function __construct()
    {
        $this->client = new FirestoreClient();
    }

    function solve()
    {
        // Create Collection
        $collectionName = 'prices';
        $collection = $this->client->collection($collectionName);

        $collectionSize = 100;
        for ($i = 0 ; $i < $collectionSize ; ++$i) {
            $value = rand(1, 1000);
            $collection->add([
                'value' => $value
            ]);
        }

        $query = $collection->where(Filter::field('value', '<', 50));

        printf('Documents are: ');
        foreach ($query->documents() as $document) {
            echo $document->data()['value'] .', ';
        }
        echo PHP_EOL;

        $countResult = $query->count();
        echo "Count: $countResult\n";

        $sumResult = $query->sum();
        echo "Sum: $sumResult\n";

        $avgResult = $query->avg();
        echo "Avg: $avgResult\n";



        // Deleting the collection and documents
        foreach ($collection->documents() as $document) {
            $document->reference()->delete();
        }
    }
}

$god = new God();
$god->solve();

