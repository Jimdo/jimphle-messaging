<?php
namespace Jimphle\Messaging\MessageHandler;

use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandlerResponse;

class HandleMessagesToProcessDirectly implements MessageHandler
{
    private $directMessageHandler;

    public function __construct(MessageHandler $directMessageHandler) {
        $this->directMessageHandler = $directMessageHandler;
    }

    public function handle(Message $message)
    {
        $response = $this->directMessageHandler->handle($message);

        if ($response instanceof MessageHandlerResponse) {
            foreach ($response->getMessagesToProcessDirectly() as $messageToHandleDirectly) {
                $responseOfDirectlyHandledMessage = $this->handle($messageToHandleDirectly);

                if ($responseOfDirectlyHandledMessage instanceof MessageHandlerResponse) {
                    foreach ($responseOfDirectlyHandledMessage->getMessagesToProcessInBackground() as $messageToProcessInBackground) {
                        $response->addMessageToProcessInBackground($messageToProcessInBackground);
                    }
                }
            }
        }

        return $response;
    }
}
