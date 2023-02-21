<?php
/**
 * Delete all collections
 */
require 'vendor/autoload.php';
use Google\Cloud\Firestore\FirestoreClient;
function data_delete_collection()
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient();
    $collections = iterator_to_array($db->collections());
    $collectionsCount = 0;
    $documentsCount = 0;
    $batchSize = 10;
    foreach($collections as $collection) {
        $documents = $collection->limit($batchSize)->documents();
        while (!$documents->isEmpty()) {
            foreach ($documents as $document) {
                $document->reference()->delete();
                ++$documentsCount;
            }
            $documents = $collection->limit($batchSize)->documents();
        }
        ++$collectionsCount;
    }
    echo "Deleted {$collectionsCount} collections having {$documentsCount} documents\n";
}

data_delete_collection();
