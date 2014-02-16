<?php
namespace Jimphle\Messaging;

interface Filter
{
    /**
     * @param Message $message
     * @return Message
     */
    public function filter(Message $message);
}
