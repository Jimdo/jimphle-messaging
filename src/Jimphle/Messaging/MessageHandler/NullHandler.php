<?php
namespace Jimphle\Messaging\MessageHandler;

class NullHandler implements \Jimphle\Messaging\MessageHandler\MessageHandler
{
    /**
     * @param \Jimphle\Messaging\Message|\Jimphle\DataStructure\Map $message
     * @return \Jimphle\Messaging\MessageHandlerResponse|\Jimphle\Messaging\Message|\Jimphle\DataStructure\Map|null
     */
    public function handle(\Jimphle\Messaging\Message $message)
    {
    }
}
