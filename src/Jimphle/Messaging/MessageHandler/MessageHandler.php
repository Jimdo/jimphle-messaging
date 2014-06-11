<?php
namespace Jimphle\Messaging\MessageHandler;

use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandlerResponse;

interface MessageHandler
{
    /**
     * @param Message|\Jimphle\DataStructure\Map $message
     * @return \Jimphle\Messaging\MessageHandlerResponse|\Jimphle\Messaging\Message|\Jimphle\DataStructure\Map|null
     */
    public function handle(Message $message);
}
