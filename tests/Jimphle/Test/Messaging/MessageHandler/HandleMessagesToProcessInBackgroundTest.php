<?php
namespace Jimphle\Test\Messaging\MessageHandler;

use Jimphle\DataStructure\Map;
use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandler\HandleMessagesToProcessDirectly;
use Jimphle\Messaging\MessageHandler\HandleMessagesToProcessInBackground;
use Jimphle\Messaging\MessageHandlerResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HandleMessagesToProcessInBackgroundTest extends TestCase
{
    const TEST_NAME = 'test_name';

    /**
     * @var MockObject
     */
    private $directMessageHandlerMock;

    /**
     * @var MockObject
     */
    private $backgroundMessageHandlerMock;

    public function setUp(): void
    {
        $this->directMessageHandlerMock = $this->messageHandlerMock();
        $this->backgroundMessageHandlerMock = $this->messageHandlerMock();
    }

    /**
     * @test
     */
    public function itShouldHandleTheMessageDirectly()
    {
        $someMessage = $this->someMessage();
        $this->directMessageHandlerMock->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($someMessage))
            ->will($this->returnValue(new Map()));

        $this->handleMessage($someMessage);
    }

    /**
     * @test
     */
    public function itShouldForwardResponse()
    {
        $response = $this->messageHandlerResponse();

        $this->directMessageHandlerMock->expects($this->once())
            ->method('handle')
            ->will($this->returnValue($response));

        $this->assertThat(
            $this->handleMessage($this->someMessage()),
            $this->equalTo($response)
        );
    }

    /**
     * @test
     */
    public function itShouldHandleMessagesToProcessInBackground()
    {
        $firstMessage = $this->someMessage('first');
        $secondMessage = $this->someMessage('second');
        $firstResponse = $this->messageHandlerResponse();
        $firstResponse->addMessageToProcessInBackground($secondMessage);

        $this->directMessageHandlerMock = $this->messageHandlerMock();
        $this->directMessageHandlerMock->expects($this->at(0))
            ->method('handle')
            ->with($this->equalTo($firstMessage))
            ->will($this->returnValue($firstResponse));

        $this->backgroundMessageHandlerMock = $this->messageHandlerMock();
        $this->backgroundMessageHandlerMock->expects($this->at(0))
            ->method('handle')
            ->with($this->equalTo($secondMessage));

        $this->handleMessage($firstMessage);
    }

    /**
     * @test
     */
    public function itShouldNotHandleMessageToProcessInBackgroundIfDirectMessageHandlingFails()
    {
        $firstMessage = $this->someMessage('first');
        $secondMessage = $this->someMessage('second');

        $response = $this->messageHandlerResponse();
        $response->addMessageToProcessInBackground($secondMessage);

        $this->directMessageHandlerMock = $this->messageHandlerMock();
        $this->directMessageHandlerMock->expects($this->once())
            ->method('handle')
            ->will($this->throwException(new \LogicException()));

        $this->backgroundMessageHandlerMock = $this->messageHandlerMock();
        $this->backgroundMessageHandlerMock->expects($this->never())
            ->method('handle');

        try {
            $this->handleMessage($firstMessage);
            $this->fail('should throw an exception here.');
        } catch (\LogicException $e) {

        }
    }

    private function messageHandlerMock()
    {
        $messageHandler = $this->createMock(\Jimphle\Messaging\MessageHandler\MessageHandler::class);
        return $messageHandler;
    }

    private function handleMessage(Message $event)
    {
        $messageHandler = new HandleMessagesToProcessInBackground(
            $this->directMessageHandlerMock,
            $this->backgroundMessageHandlerMock
        );
        return $messageHandler->handle($event);
    }

    private function someMessage($name = self::TEST_NAME)
    {
        return GenericMessage::generate($name);
    }

    private function messageHandlerResponse()
    {
        return MessageHandlerResponse::withoutPayload();
    }
}
