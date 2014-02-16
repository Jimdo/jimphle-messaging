<?php
namespace Jimphle\Messaging;

class Command extends GenericMessage
{
    public function getMessageType()
    {
        return self::TYPE_COMMAND;
    }
}
