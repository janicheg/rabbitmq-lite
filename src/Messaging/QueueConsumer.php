<?php


namespace SlimQ\Messaging;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Ramsey\Uuid\Uuid;
use SlimQ\ConsumerInterFace;

class QueueConsumer
{
    /** @var AMQPChannel  */
    protected $channel;

    /** @var ConsumerInterFace  */
    protected $consumerRunner;

    /** @var \Ramsey\Uuid\UuidInterface */
    protected $tag;

    /** @var string */
    protected $queueName;

    const DELIMITER_QUEUE = '.';

    /**
     * QueueConsumer constructor.
     *
     * @param                    $queueName
     * @param AMQPChannel        $channel
     * @param ContainerInterface $container
     */
    public function __construct($queueName, AMQPChannel $channel, ConsumerInterFace $consumerRunner)
    {
        $this->channel = $channel;
        $this->consumerRunner = $consumerRunner;
        $this->tag = Uuid::uuid4();
        $this->queueName = $queueName;
    }

    /**
     * @return string
     */
    protected function getTriggeredClass()
    {
        return implode('',array_map(function($v){return ucfirst($v);}, explode(self::DELIMITER_QUEUE, $this->queueName)));
    }


    public function consume()
    {
        $this->channel->basic_consume($this->queueName, $this->tag->toString(), false, false, false, false, [$this, 'start']);

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }

    }

    /**
     * @param AMQPMessage $message
     */
    public function start(AMQPMessage $message)
    {
        $json = json_decode($message->body);

        try {
            $this->consumerRunner->run($json);
        } catch (\Exception $exception)
        {
            $result = false;
        }

        if ($result) {
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            return;
        }

        $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag']);
    }
}