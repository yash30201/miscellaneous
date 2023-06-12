<?php

if (!isset($argc) || $argc != 2) {
    echo "Please enter the name of the file to publish.\n";
    exit;
}

use Google\Cloud\PubSub\PubSubClient;

require __DIR__ . '/vendor/autoload.php';
class Publisher
{
    private const MINUTE_LOOP = 60000;

    public function __construct($fileName)
    {
        $client = new PubSubClient([
            'projectId' => 'yashsahu-dev-test'
        ]);
        $this->sendPublishRequestsAndPull($client, self::MINUTE_LOOP, $fileName);
        // $this->sendPublishRequestsAndPull($client, 1, $fileName);
    }

    public function sendPublishRequestsAndPull($client, int $count = 1, string $filename = 'data/data7')
    {
        $contents = file_get_contents($filename);
        $message = $this->formMessage($contents);

        $topicName = 'bloom'; // Already exists
        $subscriptionName = 'bloom-sub'; // Already exists
        $topic = $client->topic($topicName);
        $subscriber = $client->subscription($subscriptionName);
        for ($_ = 0 ; $_ < $count ; $_++) {
            $topic->publish($message);
            usleep(10);
            $messages = $subscriber->pull(['maxMessages' => 1]);
            $subscriber->acknowledgeBatch($messages);
        }
    }


    private function formMessage(string $messageText)
    {
        return ['data' => $messageText];
    }
}

$object = new Publisher($argv[1]);
