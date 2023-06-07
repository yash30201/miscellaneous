<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\StreamingRecognitionConfig;

class Base
{
    protected $client;

    public function __construct()
    {
        $this->client = new SpeechClient();
    }

    public function solve()
    {
        $recognitionConfig = new RecognitionConfig();
        $recognitionConfig->setEncoding(AudioEncoding::FLAC);
        $recognitionConfig->setSampleRateHertz(44100);
        $recognitionConfig->setLanguageCode('en-US');
        $config = new StreamingRecognitionConfig();
        $config->setConfig($recognitionConfig);

        $audioResource = fopen(__DIR__ . '/../data/music.mp3', 'r');

        $responses = $this->client->recognizeAudioStream($config, $audioResource);

        foreach ($responses as $element) {
            // doSomethingWith($element);
        }
    }
}

$base = new Base();
$base->solve();

