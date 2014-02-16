<?php
namespace Jimphle\Messaging\MessageHandler;

use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandlerResponse;

interface MessageHandler
{
    /**
     * @param Message|\Jimphle\DataStructure\Map $message
     * @return MessageHandlerResponse|Message|\Jimphle\DataStructure\Map|null
     */
    public function handle(Message $message);
}
