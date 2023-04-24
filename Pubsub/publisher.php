<?php

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;

require __DIR__ . '/../vendor/autoload.php';

class Publisher
{

    public function __construct()
    {
        $client = new PubSubClient();
        // $this->solve($client);
        $this->deleteAllSubscriptions($client);
        $this->deleteAlltopics($client);
    }
    public function solve($client)
    {
        if(!function_exists("readline")) {
            function readline($prompt = null){
                if($prompt){
                    echo $prompt;
                }
                $fp = fopen("php://stdin","r");
                $line = rtrim(fgets($fp, 1024));
                return $line;
            }
        }

        $name = uniqid('pub');
        // Create a topic
        $topic = $client->createTopic($name);

        // Create atleast one subscriber
        $subscription = $topic->subscribe($name . '-sub');

        printf('Topic name: %s' . PHP_EOL, $topic->info()['name']);

        while (true) {
            $input = readline('Text to publish: ');

            // End process if $input == exit
            if ($input == 'exit') break;

            $messageArray = $this->formMessage($input);
            $topic->publish($messageArray);
            printf('Published message' . PHP_EOL);
        }

        // ************ CLEAN UP ************

        // Delete Subscription
        $subscription->delete();
        // Delete topic
        $topic->delete();
    }

    public function deleteAlltopics($client)
    {
        $topics = $client->topics();
        foreach ($topics as $topic) {
            printf('Deleting topic: %s ... ', $topic->info()['name']);
            $topic->delete();
            echo "Deleted.\n";
        }
    }

    public function deleteAllSubscriptions($client)
    {
        $subscriptions = $client->subscriptions();
        foreach ($subscriptions as $subscription) {
            printf('Deleting subscription: %s ... ', $subscription->info()['name']);
            $subscription->delete();
            echo "Deleted.\n";
        }
    }

    private function formMessage(string $messageText, array $attributes = [])
    {
        return [
            'data' => $messageText,
            'attributes' => $attributes
        ];
    }
}

$object = new Publisher();
