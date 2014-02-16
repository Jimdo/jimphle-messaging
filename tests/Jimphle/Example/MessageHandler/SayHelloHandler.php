<?php
namespace Jimphle\Example\MessageHandler;

use Jimphle\Messaging\AbstractMessageHandler;
use Jimphle\Messaging\Message;

class SayHelloHandler extends AbstractMessageHandler
{
    public function handle(Message $message)
    {
        return $this->response(
            array('answer' => sprintf("Hello %s!", $message->name))
        )
            ->addMessageToProcessDirectly(
                $this->event('said-hello', array('name' => 'Joscha'))
            );
    }
}
