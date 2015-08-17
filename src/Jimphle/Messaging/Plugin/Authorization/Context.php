<?php
namespace Jimphle\Messaging\Plugin\Authorization;

use Jimphle\Messaging\Message;

interface Context
{
    /**
     * @param Message $message
     * @param Constraint[] $constraints
     */
    public function assertAccessIsGranted(Message $message, array $constraints);

    /**
     * Is the current user authorized as superuser?
     * @return bool
     */
    public function isSuperUser();
}
