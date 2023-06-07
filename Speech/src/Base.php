<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Speech\V1\SpeechClient;

class Base
{
    protected $client;

    public function __construct()
    {
        $this->client = new SpeechClient();
    }
}
