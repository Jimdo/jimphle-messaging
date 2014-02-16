<?php
namespace Jimphle\Messaging;

use Jimphle\Exception\RuntimeException;
use Jimphle\Messaging\Message;

class MessageHandlerProvider
{
    private $serviceContainer;

    public function __construct(\ArrayAccess $pimple)
    {
        $this->serviceContainer = $pimple;
    }

    /**
     * @param string $commandHandlerName
     * @return MessageHandler\MessageHandler
     * @throws RuntimeException
     */
    public function getCommandHandler($commandHandlerName)
    {
        $messageHandler = $this->serviceContainer[$commandHandlerName];

        $this->assertValidMessageHandler($commandHandlerName, $messageHandler);

        return $messageHandler;
    }

    /**
     * @param string $eventHandlerName
     * @return MessageHandler\MessageHandler[]
     * @throws RuntimeException
     */
    public function getEventHandlers($eventHandlerName)
    {
        if (!isset($this->serviceContainer[$eventHandlerName])) {
            return array();
        }
        if (!is_array($this->serviceContainer[$eventHandlerName])) {
            throw new RuntimeException(
                sprintf('service definition for "%s" must return an array', $eventHandlerName)
            );
        }
        $eventHandlers = $this->serviceContainer[$eventHandlerName];
        foreach ($eventHandlers as $eventHandler) {
            $this->assertValidMessageHandler($eventHandlerName, $eventHandler);
        }
        return $this->serviceContainer[$eventHandlerName];
    }

    /**
     * @param Message $message
     * @return MessageHandler\MessageHandler[]
     * @throws RuntimeException
     */
    public function getMessageHandlers(Message $message)
    {
        switch ($message->getMessageType()) {
            case Message::TYPE_COMMAND:
                return array($this->getCommandHandler($message->getMessageName()));
            case Message::TYPE_EVENT:
                return $this->getEventHandlers($message->getMessageName());
        }
        throw new RuntimeException('Cannot handle undefined message type.');
    }

    private function assertValidMessageHandler($messageHandlerName, $messageHandler)
    {
        if (!$messageHandler instanceof MessageHandler\MessageHandler) {
            throw new RuntimeException(
                sprintf('"%s" is not a valid message handler', $messageHandlerName)
            );
        }
    }
}
