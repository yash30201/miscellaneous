<?php
/**
 * Link to issue for which this script is for:
 * https://github.com/googleapis/google-api-php-client/issues/2335
 */
require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Drive;
use Google\Service\Calendar;

class GoogleApi
{
    private $drive;
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
        $this->drive = new Drive($client);
    }

    public function getAllfiles()
    {
        $optParams = array(
            'pageSize' => 10,
            'fields' => 'nextPageToken, files(id, name)'
        );
        $files = $this->drive->files->listFiles($optParams)->getFiles();

        if(count($files) == 0) {
            print("No files found.\n");
        } else {
            print("Files:\n");
            foreach ($files as $file) {
                printf("%s (%s)\n", $file->getName(), $file->getId());
            }
        }
    }

    public function getAllFilesInFolder(string $sharedDriveId, string $folderId)
    {
        $files = [];
        $pageToken = null;
        do {
            $optParams = [
                'pageSize' => 20,
                // 'q' => "mimeType='application/vnd.google-apps.folder' and '{$folderId}' in parents and trashed=false",
                'q' => "'{$folderId}' in parents and trashed=false",
                // 'q' => "mimeType='application/vnd.google-apps.folder'",
                'corpora' => 'drive',
                'driveId' => $sharedDriveId,
                'includeItemsFromAllDrives' => true,
                'supportsAllDrives' => true,
                'pageToken' => $pageToken
            ];
            $response = $this->drive->files->listFiles($optParams);
            $pageToken = $response->getNextPageToken();
            $response = $response->getFiles();
            foreach ($response as &$file) {
                $newFile = [$file->getName(), $file->getId()];
                $file = $newFile;
            }
            array_push($files, $response);
        } while ($pageToken != null);

        print_r($files);
    }

    public function uploadBasic()
    {
        try {
            $fileMetadata = new Drive\DriveFile(array(
            'name' => 'file.txt'));
            $content = file_get_contents('data/file.txt');
            $file = $this->drive->files->create($fileMetadata, array(
                'data' => $content,
                'mimeType' => 'text/plain',
                'uploadType' => 'multipart',
                'fields' => 'id'));
            printf("File ID: %s\n", $file->id);
            return $file->id;
        } catch(Exception $e) {
            echo "Error Message: ".$e;
        }

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
$client->getAllFilesInFolder('0ACB98cgilm9HUk9PVA','10ESH8QRJ1i_FIiU_u_EFWJWCM-69YnKU');
