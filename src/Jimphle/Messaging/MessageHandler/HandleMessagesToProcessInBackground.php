<?php
namespace Jimphle\Messaging\MessageHandler;

use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandlerResponse;

class HandleMessagesToProcessInBackground implements MessageHandler
{
    private $directMessageHandler;
    private $backgroundMessageHandler;

    public function __construct(
        MessageHandler $directMessageHandler,
        MessageHandler $backgroundMessageHandler
    ) {
        $this->directMessageHandler = $directMessageHandler;
        $this->backgroundMessageHandler = $backgroundMessageHandler;
    }

    public function handle(Message $message)
    {
        $response = $this->directMessageHandler->handle($message);

        if ($response instanceof MessageHandlerResponse) {
            foreach ($response->getMessagesToProcessInBackground() as $message) {
                $this->backgroundMessageHandler->handle($message);
            }
        }

        return $response;
    }
}
