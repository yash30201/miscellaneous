<?php
/**
 * Checking if we can pass core timestamp to autogenerated firestore
 * client library
 */
require 'vendor/autoload.php';

use Google\Cloud\Core\Timestamp;
use Google\Cloud\Firestore\V1\FirestoreClient;

$firestore = new FirestoreClient();
$projectId = 'yashsahu-dev-test';
$databaseId = '(default)';
$database = $firestore->databaseRootName($projectId, $databaseId);
try {
    $documents = [$firestore->documentPathName($projectId, $databaseId,'readtime-639313eba2f72/tempdoc-639313eba30a6')];
    // Read all responses until the stream is complete
    $readTime = new Timestamp(new \DateTime);
    $stream = $firestore->batchGetDocuments($database, $documents, ['readTime' => $readTime]);
    foreach ($stream->readAll() as $element) {
        echo "Great\n";
    }
} finally {
    echo "Error\n";
    $firestore->close();
}