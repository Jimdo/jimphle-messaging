<?php
namespace Jimphle\Messaging\MessageHandler;

use Jimphle\DataStructure\Map;
use Jimphle\DataStructure\Vector;
use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandlerProvider;
use Jimphle\Messaging\MessageHandlerResponse;

class HandleMessage implements MessageHandler
{
    /**
     * @var MessageHandler
     */
    private $messageHandlerProvider;

    public function __construct(MessageHandlerProvider $messageHandlerProvider)
    {
        $this->messageHandlerProvider = $messageHandlerProvider;
    }

    public function handle(Message $message)
    {
        $messageHandlers = $this->messageHandlerProvider->getMessageHandlers($message);

        $responseToReturn = null;
        $responses = array();
        foreach ($messageHandlers as $messageHandler) {
            $latestResponse = $messageHandler->handle($message);
            if ($responseToReturn === null) {
                $responseToReturn = $this->mapToResponse($latestResponse);
            } else {
                $responses[] = $this->mapToResponse($latestResponse);
            }
        }

        foreach ($responses as $currentResponse) {
            $responseToReturn = $responseToReturn->mergeMessageToProcess($currentResponse);
        }
        return $responseToReturn;
    }

    private function mapToResponse($latestResponse)
    {
        if ($latestResponse instanceof MessageHandlerResponse) {
            return $latestResponse;
        }
        if ($latestResponse instanceof Map
            || $latestResponse instanceof Vector
            || $latestResponse instanceof Message
        ) {
            return new MessageHandlerResponse($latestResponse);
        }
        return MessageHandlerResponse::withoutPayload();
    }
}
