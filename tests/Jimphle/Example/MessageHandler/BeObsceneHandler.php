<?php
namespace Jimphle\Example\MessageHandler;

use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandler\MessageHandler;

class BeObsceneHandler implements MessageHandler
{
    public function handle(Message $message)
    {
        echo sprintf('GTFO %s!', $message->name) . "\n";
    }
}
