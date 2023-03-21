<?php

/*

Link : https://github.com/googleapis/google-api-php-client/issues/2393

$mime_email = '. . . . .From: "Info" <info@company.com>......';
$objGMail = new Google_Service_Gmail($client);
$mime = rtrim(strtr(base64_encode($mime_email), '+/', '-_'), '=');
$gmail_msg = new Google_Service_Gmail_Message();
$gmail_msg->setRaw($mime);
$objGMail->users_messages->send('me', $gmail_msg);
 */

require __DIR__ . '/../../vendor/autoload.php';

use Google\Client;
use Google\Service\Gmail\SendAs;
use Google\Service\Sheets;
use Google\Service\Drive;
use Google\Service\Calendar;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

class GoogleApi
{
    private $client;
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
        string $applicationName,
        string|array $serviceScope,
        string $accessType = 'offline'
    ) {
        $this->client = new Client();
        $this->client->setApplicationName($applicationName);
        $this->client->setScopes($serviceScope);
        $this->client->setAccessType($accessType);
        $this->client->setAuthConfig(__DIR__ . '/../../secret/gmail_oauth_client_secret.json');
        $this->client->setRedirectUri('http://localhost');
        // $devKey = getenv('DEVELOPER_KEY');
        // $this->client->setDeveloperKey($devKey);
        // $this->client->setSubject('yashsahu@google.com');

        $accessToken = null;

        if (is_null($accessToken)) {
            $authUrl = $this->client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
        }
        // Exchange authorization code for an access token.
        $this->client->setAccessToken($accessToken);
    }

    public function runCode()
    {

        $rawMessageString = "From: <yashsahu.test.002@gmail.com>\r\n";
        $rawMessageString .= "To: <dankyuso@gmail.com>\r\n";
        $rawMessageString .= 'Subject: =?utf-8?B?' . base64_encode('Hi everyone') . "?=\r\n";
        $rawMessageString .= "MIME-Version: 1.0\r\n";
        $rawMessageString .= "Content-Type: text/html; charset=utf-8\r\n";
        $rawMessageString .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        $gmail = new Gmail($this->client);
        $mime = rtrim(strtr(base64_encode($rawMessageString), '+/', '-_'), '=');
        $gmailMessage = new Message();
        $gmailMessage->setRaw($mime);
        $gmail->users_messages->send('me', $gmailMessage);
        echo 'DONE' . PHP_EOL;
    }

    public function listAlias()
    {
        $gmail = new Gmail($this->client);
        $sendAsList = $gmail->users_settings_sendAs
            ->listUsersSettingsSendAs('me')
            ->getSendAs();
        foreach ($sendAsList as $sendAs) {
            echo 'Alias Name: ' . $sendAs->getDisplayName() . PHP_EOL;
            echo 'Email: ' . $sendAs->getSendAsEmail() . PHP_EOL;
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
    // 'https://www.googleapis.com/auth/drive',
    // 'https://www.googleapis.com/auth/spreadsheets',
    // 'https://www.googleapis.com/auth/calendar',
    'https://mail.google.com/'
];
$api = new GoogleApi(
    'Your Application Name',
    $scopes
);
// $api->listAlias();
$api->runCode();
