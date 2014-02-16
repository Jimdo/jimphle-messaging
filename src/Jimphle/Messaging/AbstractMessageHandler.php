<?php
namespace Jimphle\Messaging;

use Jimphle\Messaging\MessageHandler\MessageHandler;

abstract class AbstractMessageHandler implements MessageHandler
{
    /**
     * @param string $name
     * @param array $payload
     * @param null|string $channel
     * @return Event
     */
    protected function event($name, array $payload, $channel = null)
    {
        return Event::generate($name, $payload, $channel);
    }

    /**
     * @param string $name
     * @param array $payload
     * @param null|string $channel
     * @return Command
     */
    protected function command($name, array $payload, $channel = null)
    {
        return Command::generate($name, $payload, $channel);
    }

    /**
     * @param array $payload
     * @return MessageHandlerResponse
     */
    protected function response(array $payload = array())
    {
        $response = MessageHandlerResponse::fromMap($payload);
        return $response;
    }
}
