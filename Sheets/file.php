<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\CopySheetToAnotherSpreadsheetRequest;
$client = new Client();
$client->setApplicationName('Google Sheets API');
$client->setScopes([Sheets::SPREADSHEETS]);
$client->setAccessType('offline');
$path = '/usr/local/google/home/yashsahu/work/service-accounts/yashsahu-dev-test-c30e742b554d.json';
$client->setAuthConfig($path);

$service = new Sheets($client);
$spreadsheetId = '1EcFr1xU-WNcOJ-SJFQwxbwWUVg4Ltt6Z9BrXReel8vs';
$spreadsheet = $service->spreadsheets->get($spreadsheetId);
$sheetIdToExport = '734384082';
$destinationSheetId = '1Q1n1wn6agHE6flR-JTThzkjqK_wDzlu-5idJzWitZQg';
$copySheetRequest = new CopySheetToAnotherSpreadsheetRequest();
$copySheetRequest->setDestinationSpreadsheetId($destinationSheetId);
$destinationSheet = $service->spreadsheets_sheets->copyTo(
    $spreadsheetId,
    $sheetIdToExport,
    $copySheetRequest
);
