<?php

require_once '/usr/local/google/home/yashsahu/work/miscellaneous/Rotation/issue2394/vendor/autoload.php';

use Google\Client;

function getGoogleData($token = '')
{
    // $client_id = $_ENV['GOOGLE_CLIENT_ID'];
    // $client_secret = $_ENV['GOOGLE_CLIENT_SECRET'];

    $client = new Client();
    // $client->setAuthConfig('/var/www/localhost/client_credentials.json');
    // $payload = $client->verifyIdToken($token);
}

getGoogleData();
