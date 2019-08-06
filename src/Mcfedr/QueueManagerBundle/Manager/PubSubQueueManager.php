<?php

declare(strict_types=1);

namespace Mcfedr\QueueManagerBundle\Manager;

use Google\Cloud\PubSub\PubSubClient;
use Mcfedr\QueueManagerBundle\Exception\NoSuchJobException;
use Mcfedr\QueueManagerBundle\Queue\Job;
use Mcfedr\QueueManagerBundle\Queue\PubSubJob;

class PubSubQueueManager implements QueueManager
{
    use PubSubClientTrait;

    /**
     * @var PubSubClient
     */
    private $pubSub;

    public function __construct(PubSubClient $pubSubClient, array $options)
    {
        $this->pubSub = $pubSubClient;
        $this->setOptions($options);
    }

    public function put(string $name, array $arguments = [], array $options = []): Job
    {
        if (\array_key_exists('queue', $options)) {
            $topicName = reset($this->queues[$options['queue']]);
        } else {
            $topicName = reset($this->defaultQueue);
        }

        $topic = $this->pubSub->topic($topicName);

        $job = new PubSubJob($name, $arguments, null, 0);

        $result = $topic->publish(['data' => $job->getMessageBody()]);

        $job->setId(reset($result['messageIds']));

        return $job;
    }

    public function delete(Job $job): void
    {
        throw new NoSuchJobException('Pub\Sub queue manager cannot delete jobs');
    }
}
