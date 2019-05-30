<?php


namespace SlimQ\Messaging;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class QueuePublisher
{
    /**
     * @var AMQPChannel
     */
    private $channel;
    private $exchangeName;

    /**
     * QueuePublisher constructor.
     * @param $exchangeName
     * @param AMQPChannel $channel
     */
    public function __construct($exchangeName, AMQPChannel $channel)
    {
        $this->channel = $channel;
        $this->exchangeName = $exchangeName;
    }

    public function publish($routingKey, array $arguments)
    {
        $amqpMessage = new AMQPMessage(
            json_encode($arguments),
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]
        );

        $this->channel->basic_publish($amqpMessage, $this->exchangeName, $routingKey);
    }
}