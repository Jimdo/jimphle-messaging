<?php
namespace Jimphle\Messaging;

class Event extends GenericMessage
{
    public function getMessageType()
    {
        return self::TYPE_EVENT;
    }
}
