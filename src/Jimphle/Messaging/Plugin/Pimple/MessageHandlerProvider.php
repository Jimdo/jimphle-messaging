<?php
namespace Jimphle\Messaging\Plugin\Pimple;

class MessageHandlerProvider implements \Jimphle\Messaging\MessageHandlerProvider
{
    private $serviceContainer;

    public function __construct(\Pimple $pimple)
    {
        $this->serviceContainer = $pimple;
    }

    /**
     * @param string $commandHandlerName
     * @return \Jimphle\Messaging\MessageHandler\MessageHandler
     * @throws \Jimphle\Exception\RuntimeException
     */
    public function getCommandHandler($commandHandlerName)
    {
        $messageHandler = $this->serviceContainer[$commandHandlerName];

        $this->assertValidMessageHandler($commandHandlerName, $messageHandler);

        return $messageHandler;
    }

    /**
     * @param string $eventHandlerName
     * @return \Jimphle\Messaging\MessageHandler\MessageHandler[]
     * @throws \Jimphle\Exception\RuntimeException
     */
    public function getEventHandlers($eventHandlerName)
    {
        if (!isset($this->serviceContainer[$eventHandlerName])) {
            return array();
        }
        if (!is_array($this->serviceContainer[$eventHandlerName])) {
            throw new \Jimphle\Exception\RuntimeException(
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
     * @param \Jimphle\Messaging\Message $message
     * @return \Jimphle\Messaging\MessageHandler\MessageHandler[]
     * @throws \Jimphle\Exception\RuntimeException
     */
    public function getMessageHandlers(\Jimphle\Messaging\Message $message)
    {
        switch ($message->getMessageType()) {
            case \Jimphle\Messaging\Message::TYPE_COMMAND:
                return array($this->getCommandHandler($message->getMessageName()));
            case \Jimphle\Messaging\Message::TYPE_EVENT:
                return $this->getEventHandlers($message->getMessageName());
        }
        throw new \Jimphle\Exception\RuntimeException('Cannot handle undefined message type.');
    }

    private function assertValidMessageHandler($messageHandlerName, $messageHandler)
    {
        if (!$messageHandler instanceof \Jimphle\Messaging\MessageHandler\MessageHandler) {
            throw new \Jimphle\Exception\RuntimeException(
                sprintf('"%s" is not a valid message handler', $messageHandlerName)
            );
        }
    }
}
