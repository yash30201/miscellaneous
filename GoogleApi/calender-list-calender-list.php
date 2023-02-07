<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Drive;
use Google\Service\Calendar;

class GoogleApi
{
    private $calender;
    /**
     * Construct the the custom google API client
     * The default parameters explanation can be found here:
     * https://developers.google.com/identity/protocols/oauth2/web-server#obtainingaccesstokens
     *
     * @param string $applicationName The application name for this client.
     * @param string|array $serviceScope The service scope of this client. It
     *  can be a single scope or an array of scopes. The list of all scopes can
     *  found here: https://developers.google.com/identity/protocols/oauth2/scopes
     * @param string $accessType [Optional]
     */
    function __construct(
        string $applicationName ,
        string|array $serviceScope,
        string $accessType = 'offline'
    )
    {
        $client = new Client();
        $client->setApplicationName($applicationName);
        $client->setScopes($serviceScope);
        $client->setAccessType($accessType);
        $client->useApplicationDefaultCredentials();
        $this->calender = new Calendar($client);
    }

    public function getListCalenderList()
    {
        $calenderList = $this->calender->calendarList->listCalendarList();
        echo "Done\n";
    }
}


/**
 * Dev guides
 *
 * Calender:
 *  https://developers.google.com/calendar/api/guides/overview
 *  Go to the use the calender API section
 *
 * Sheets:
 *  https://developers.google.com/sheets/api/guides/concepts
 *
 * Drive:
 *  https://developers.google.com/drive/api/guides/about-sdk
 */
$scopes = [
    'https://www.googleapis.com/auth/drive',
    'https://www.googleapis.com/auth/spreadsheets',
    'https://www.googleapis.com/auth/calendar',
];
$client = new GoogleApi(
    'Your Application Name',
    $scopes
);

$client->getListCalenderList();
