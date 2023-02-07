<?php
/**
 * Deleting all the collections
 */
require 'vendor/autoload.php';

use Google\Cloud\Core\Timestamp;
use Google\Cloud\Firestore\V1\FirestoreClient;

$firestore = new FirestoreClient();
$projectId = 'yashsahu-dev-test';
$databaseId = '(default)';
$database = $firestore->databaseRootName($projectId, $databaseId);
try {
    $parent = $firestore->documentRootName($projectId, $databaseId);
    // Read all responses until the stream is complete
    $readTime = new Timestamp(new \DateTime);
    $pagedResponse = $firestore->listCollectionIds($parent);
    foreach ($pagedResponse->iterateAllElements() as $collectionId) {
        $documentsPagedResponse = $firestore->listDocuments($parent, $collectionId);
        foreach($documentsPagedResponse->iterateAllElements() as $document){
            $name = $document->getName();
            $firestore->deleteDocument($name);
        }
    }
} finally {
    echo "Error\n";
    $firestore->close();
}
