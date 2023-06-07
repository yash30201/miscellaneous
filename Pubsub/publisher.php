<?php

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;

require __DIR__ . '/vendor/autoload.php';

class Publisher
{

    public function __construct()
    {
        $client = new PubSubClient();
        // $this->initialize($client);

        $this->sendPublishRequests($client);
        // $this->deleteAllSubscriptions($client);
        // $this->deleteAlltopics($client);
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

        // $name = uniqid('pub');
        $name = 'bloom';
        // Create a topic
        // $topic = $client->createTopic($name);
        $topic = $client->topic($name);

        // Create atleast one subscriber
        $subscription = $topic->subscription($name . '-sub');

        printf('Topic name: %s' . PHP_EOL, $topic->info()['name']);

        // while (true) {
        //     $input = readline('Text to publish: ');

        //     // End process if $input == exit
        //     if ($input == 'exit') break;
        //     else if ($input == 'data') {
        //         $fileName = readline('Enter data file name: ');
        //         $input = file_get_contents('data/' . $fileName);
        //     }

        //     $messageArray = $this->formMessage($input);
        //     $topic->publish($messageArray);
        //     printf('Published message' . PHP_EOL);
        // }

        $messageCount = 1000;
        $starttime = microtime(true);
        $input = file_get_contents('data/data0');
        $messageArray = $this->formMessage($input);
        while ($messageCount--) {
            $topic->publish($messageArray);
        }
        $endtime = microtime(true);
        $timediff = $endtime - $starttime;

        // ************ CLEAN UP ************

        // Delete Subscription
        // $subscription->delete();
        // // Delete topic
        // $topic->delete();
    }

    public function deleteAlltopics($client)
    {
        $topics = $client->topics();
        foreach ($topics as $topic) {
            printf('Deleting topic: %s ... ', $topic->info()['name']);
            $topic->delete();
        }
    }

    public function deleteAllSubscriptions($client)
    {
        $subscriptions = $client->subscriptions();
        foreach ($subscriptions as $subscription) {
            printf('Deleting subscription: %s ... ', $subscription->info()['name']);
            $subscription->delete();
        }
    }

    public function initialize($client)
    {
        $topic = $client->createTopic('bloom');
        $topic->subscription('bloom-sub');
    }

    public function sendPublishRequests($client, int $count = 1, string $filename = 'bigData/data4')
    {
        $contents = file_get_contents($filename);
        $message = $this->formMessage($contents);

        $name = 'bloom';
        $topic = $client->topic($name);
        // $starttime = microtime(true);
        for ($_ = 0 ; $_ < $count ; $_++) {
            $topic->publish($message);
        }
        // $endtime = microtime(true);
        // $timediff = $endtime - $starttime;
        // printf('Time taken is: ' . $timediff . PHP_EOL);
    }


    private function formMessage(string $messageText, array $attributes = [])
    {
        return [
            'data' => $messageText
            // 'attributes' => $attributes
        ];
    }
}

function sumTime() {
    $content = file_get_contents('nohup.out');
    $content = explode(' ', $content);
    $sum = array_sum($content);
}

$object = new Publisher();
// sumTime();

// With Compression
// 2244.3695151806 + 22.283607006073 for 3000 messages of 8mb each in 100 clients

// Without compression
// 2255.4242067337 + 23.0445799827588 for 3000 messages of 8mb each in 100 clients


