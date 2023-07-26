<?php
// https://github.com/grpc/grpc/issues/33259

require_once __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;

$client = new FirestoreClient();
$collection = $client->collection(uniqid());

function insertDocuments($client, $collection, $count)
{
    $bulkwriter = $client->bulkWriter();
    for ($i = 0; $i < $count; $i++) {
        $newDoc = $collection->newDocument();
        $bulkwriter->create($newDoc, [
            'foo' => 'bar',
            'index' => "{$i}"
        ]);
    }
    $bulkwriter->flush();
}

// insert 20K docs
const DOCS_ADDED = 20000;
insertDocuments($client, $collection, DOCS_ADDED);

// validate count equals 20K
$count = $collection->count();
print ("{$count} documents found the collection.".PHP_EOL);

function calcTimeElapsedInMillis($callable)
{
    $startTime = microtime(true)*1000.0;
    $callable();
    $endTime = microtime(true)*1000.0;
    $timeElapsedInMillis = $endTime - $startTime;
    return $timeElapsedInMillis;
}

function callGetDocuments($collection, $expectedCount)
{
    $documents = $collection->limit($expectedCount)->documents();
    $actualCount = count(iterator_to_array($documents));
    if ($actualCount != $expectedCount) {
        print("Found {$actualCount} expected {$expectedCount}".PHP_EOL);
    }
}

function callListDocuments($collection, $expectedCount)
{
    $documents = $collection->listDocuments([
        'pageSize' => $expectedCount,
        'resultLimit' => $expectedCount
    ]);
    $actualCount = count(iterator_to_array($documents));
    if ($actualCount != $expectedCount) {
        print("Found {$actualCount} expected {$expectedCount}".PHP_EOL);
    }
}

function runTests($collection, $testCaseCount)
{
    echo "| Documents Count | Fetch Seconds in documents() | Fetch Seconds listDocuments() |".PHP_EOL;
    echo "|-------------|---------------------|--------------------|".PHP_EOL;
    $testCases = generateTestCases($testCaseCount);
    foreach ($testCases as $docCount) {
        $timeElapsedGetDocs = calcTimeElapsedInMillis(function () use ($collection, $docCount) {
            return callGetDocuments($collection, $docCount);
        });
        $timeElapsedGetDocs = ceil($timeElapsedGetDocs / 1000.0);
        $timeElapsedListDocs = calcTimeElapsedInMillis(function () use ($collection, $docCount) {
            return callGetDocuments($collection, $docCount);
        });
        $timeElapsedListDocs = ceil($timeElapsedListDocs / 1000.0);
        print ("|{$docCount}|{$timeElapsedGetDocs}|{$timeElapsedListDocs}|".PHP_EOL);
    }
    echo "Test completed successfully.".PHP_EOL;
}

function generateTestCases($testCase)
{
    $testCases = [];
    for ($i = 0; $i < $testCase; $i++) {
        $testCases[] = DOCS_ADDED / pow(10, $i);
    }
    return $testCases;
}
runTests($collection, 4);
/*
20000 documents found the collection.
| Documents Count | Fetch Seconds in documents() | Fetch Seconds listDocuments() |
|-----------------|------------------------------|-------------------------------|
|20000            |5                             |5                              |
|2000             |1                             |1                              |
|200              |1                             |1                              |
|20               |1                             |1                              |
Test completed successfully.
*/
