<?php

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;

require __DIR__ . '/../vendor/autoload.php';

class Base
{
    private $client;
    const TOPIC = 'alpha001';
    const SUBSCRIPTION = 'alpha001-sub';

    public function __construct()
    {
        $this->client = new PubSubClient();
    }

    public function getTopic(string $topicName)
    {
        $topic = $this->client->topic($topicName);
        return $topic;
    }

    public function publishMessage(Topic $topic, array $message)
    {
        $topic->publish($message);
    }

    public function getSubsciption(string $subscriptionName)
    {
        $subscription = $this->client->subscription($subscriptionName);
        return $subscription;
    }

    public function getAllAvailableMessages(Subscription $subscription)
    {
        $messages = $subscription->pull();
        return $messages;
    }

    /**
     * @param Message[] $messages
     */
    public function printMessages(array $messages)
    {
        foreach ($messages as $message) {
            printf('Data: %s' . PHP_EOL, $message->data());
            $attributes = $message->attributes();
            printf('Attributes:' . PHP_EOL);
            foreach ($attributes as $key => $value) {
                printf('%s => %s,' . PHP_EOL, $key, $value);
            }
        }
    }

    /**
     * @param Message[] $messages
     */
    public function printAndAckMessages(array $messages, Subscription $subscription)
    {
        foreach ($messages as $message) {
            printf('Data: %s' . PHP_EOL, $message->data());
            $attributes = $message->attributes();
            printf('Attributes:' . PHP_EOL);
            foreach ($attributes as $key => $value) {
                printf('%s => %s,' . PHP_EOL, $key, $value);
            }
            $subscription->acknowledge($message);
        }
    }

    public function createSubscription(string $subscriptionName, Topic $topic)
    {
        $subscription = $topic->subscribe($subscriptionName);
        return $subscription;
    }

    /**
     * Creates a Pub/Sub topic.
     *
     * @param string $topicName  The Pub/Sub topic name.
     * @return Topic
     */
    function createTopic(string $topicName)
    {
        $topic = $this->client->createTopic($topicName);
        return $topic;
    }

    /**
     * Delete a Pub/Sub topic.
     *
     * @param string $topic  The Pub/Sub topic name or Topic object.
     */
    function delete_topic(mixed $topic)
    {
        if (is_string($topic)) {
            $topic = $this->client->topic($topic);
        }
        $topic->delete();
    }
}

$pubsub = new Base();
$topic = $pubsub->getTopic(Base::TOPIC);

// $pubsub->publishMessage($topic, [
//     'data' => 'My Third Message.',
//     'attributes' => [
//         'feeling' => 'Happy!'
//     ]
// ]);
// $subscription = $pubsub->getSubsciption(Base::SUBSCRIPTION);
// $messages = $pubsub->getAllAvailableMessages($subscription);
// $pubsub->printMessages($messages);

// $subscriptionName = 'alpha-sub-002';
// $subscription = $pubsub->getSubsciption($subscriptionName);
// $messages = $pubsub->getAllAvailableMessages($subscription);
// $subscription->acknowledge($messages[0]);
// $pubsub->printMessages($messages);
