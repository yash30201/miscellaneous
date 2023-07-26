<?php

// if (!isset($argc) || $argc != 2) {
//     echo "Please enter the name of the file to publish.\n";
//     exit;
// }

use Google\Cloud\PubSub\PubSubClient;

require __DIR__ . '/vendor/autoload.php';
class Publisher
{
    private const MINUTE_LOOP = 60000;

    public function __construct()
    {
        $client = new PubSubClient([
            'projectId' => 'yashsahu-dev-test'
        ]);
        // $this->sendPublishRequestsAndPull($client, self::MINUTE_LOOP, $fileName);
        // $this->sendPublishRequestsAndPull($client, 1, $fileName);
        $this->sendPublishRequestsAndPull($client, 1);
    }

    public function sendPublishRequestsAndPull($client, int $count = 1, string $filename = 'data/data7')
    {
        // $contents = file_get_contents($filename);
        // $message = $this->formMessage($contents);
        $message = $this->formMessage('Hellondjkswqqweankjdnas,mnfkjbsajkfnjkasbjhkdbasjhkfb dfksabdkjbasjkbdfj kabdkhdasdasdasdfedgfqeg43fbasjkdbj kabsdjkb, ellondjkswqqweankjdnas,mnfkjbsajkfnjkasbjhkdbasjhkfb dfksabdkjbasjkbdfj kabdkhdasdasdasdfedgfqeg43fbasjkdbj kabsdjkbellondjkswqqweankjdnas,mnfkjbsajkfnjkasbjhkdbasjhkfb dfksabdkjbasjkbdfj kabdkhdasdasdasdfedgfqeg43fbasjkdbj kabsdjkbellondjkswqqweankjdnas,mnfkjbsajkfnjkasbjhkdbasjhkfb dfksabdkjbasjkbdfj kabdkhdasdasdasdfedgfqeg43fbasjkdbj kabsdjkbellondjkswqqweankjdnas,mnfkjbsajkfnjkasbjhkdbasjhkfb dfksabdkjbasjkbdfj kabdkhdasdasdasdfedgfqeg43fbasjkdbj kabsdjkbellondjkswqqweankjdnas,mnfkjbsajkfnjkasbjhkdbasjhkfb dfksabdkjbasjkbdfj kabdkhdasdasdasdfedgfqeg43fbasjkdbj kabsdjkb');

        $topicName = 'bloom'; // Already exists
        $subscriptionName = 'bloom-sub'; // Already exists
        $topic = $client->topic($topicName, [
            'enableCompression' => true
        ]);
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

// $object = new Publisher($argv[1]);
$object = new Publisher();
