<?php
namespace Jimphle\Messaging;

interface MessageHandlerProvider
{
    /**
     * @param Message $message
     * @return \Jimphle\Messaging\MessageHandler\MessageHandler[]
     * @throws \Jimphle\Exception\RuntimeException
     */
    public function getMessageHandlers(Message $message);
}
