<?php
namespace Jimphle\Messaging\Plugin\Authorization;

class NullContext implements \Jimphle\Messaging\Plugin\Authorization\Context
{
    public function assertAccessIsGranted(\Jimphle\Messaging\Message $message, array $authorizationConstraints)
    {
    }

    public function isSuperUser()
    {
        return false;
    }
}
