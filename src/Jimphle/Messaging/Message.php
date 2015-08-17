<?php
namespace Jimphle\Messaging;

use Jimphle\DataStructure\BaseInterface;

interface Message extends BaseInterface
{
    const TYPE_COMMAND = 'command';
    const TYPE_EVENT = 'event';

    /**
     * @return string
     */
    public function getMessageName();

    /**
     * @return null|string
     */
    public function getMessageType();

    /**
     * @return null|int
     */
    public function getMessagePriority();

    /**
     * @return null|string
     */
    public function getMessageChannel();

    /**
     * @return null|string
     */
    public function getMessageCreatedAt();

    /**
     * @param Message $someMessage
     * @return bool
     */
    public function equals(Message $someMessage);

    /**
     * @return Map
     */
    public function toJimphleDataStructure();
}
