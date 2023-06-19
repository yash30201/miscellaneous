<?php

// https://github.com/googleapis/google-api-php-client/issues/2451

require __DIR__ . '/../vendor/autoload.php';

use Google\Client;
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
        string $applicationName ,
        string|array $serviceScope,
        string $accessType = 'offline'
    )
    {
        $this->client = new Client();
        $this->client->setApplicationName($applicationName);
        $this->client->setScopes($serviceScope);
        $this->client->setAccessType($accessType);
        $this->client->setAuthConfig(__DIR__ . '/../../secret/gmail_oauth_client_secret.json');
        $this->client->setRedirectUri('http://localhost');

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

    function getGmail()
    {
        $gmail = new Gmail($this->client);
        return $gmail;
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
$client = new GoogleApi(
    'Gmail_Client',
    $scopes
);

$gmail = $client->getGmail();
// $gmail = new Gmail('YOUR_GOOGLE_API_CLIENT_OBJECT');
$messageClient = $gmail->users_messages;
$rawMessageString = "From: <SENDER_EMAIL_ADDRESS>\<r\n";
$rawMessageString .= "To: RECEIPIENT_EMAIL_ADDRESSESS\r<\n";
$rawMessageString .= 'Subject: =?utf-8?B?' . base64_encode('Hi everyone') . "?=\r\n";
$rawMessageString .= "MIME-Version: 1.0\r\n";
$rawMessageString .= "Content-Type: text/html; charset=utf-8\r\n";
$rawMessageString .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
$mime = rtrim(strtr(base64_encode($rawMessageString), '+/', '-_'), '=');
$gmailMessage = new Message();
$gmailMessage->setRaw($mime);
$gmail->users_messages->send('me', $gmailMessage);
echo 'DONE' . PHP_EOL;


