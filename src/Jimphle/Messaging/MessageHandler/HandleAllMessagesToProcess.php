<?php
namespace Jimphle\Messaging\MessageHandler;

use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandlerResponse;

class HandleAllMessagesToProcess implements MessageHandler
{
    private $directMessageHandler;
    private $backgroundMessageHandler;

    public function __construct(
        MessageHandler $directMessageHandler,
        MessageHandler $backgroundMessageHandler
    )
    {
        $this->directMessageHandler = $directMessageHandler;
        $this->backgroundMessageHandler = $backgroundMessageHandler;
    }

    public function handle(Message $message)
    {
        list($response, $messageToProcessInBackground) = $this->handleDirectlyAndCollectToProcessInBackground($message);
        foreach ($messageToProcessInBackground as $message) {
            $this->backgroundMessageHandler->handle($message);
        }
        return $response;
    }

    private function handleDirectlyAndCollectToProcessInBackground(Message $message)
    {
        $response = $this->directMessageHandler->handle($message);

        $messageToProcessInBackground = array();
        if ($response instanceof MessageHandlerResponse) {
            foreach ($response->getMessagesToProcessInBackground() as $message) {
                $messageToProcessInBackground[] = $message;
            }
            foreach ($response->getMessagesToProcessDirectly() as $message) {
                list(
                    $latestResponse,
                    $latestMessageToProcessInBackground
                    ) = $this->handleDirectlyAndCollectToProcessInBackground($message);

                $messageToProcessInBackground = array_merge(
                    $messageToProcessInBackground,
                    $latestMessageToProcessInBackground
                );
            }
        }
        return array($response, $messageToProcessInBackground);
    }
}
