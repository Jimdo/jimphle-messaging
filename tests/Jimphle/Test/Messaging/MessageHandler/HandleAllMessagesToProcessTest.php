<?php
namespace Jimphle\Test\Messaging\MessageHandler;

use Jimphle\DataStructure\Map;
use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandler\HandleAllMessagesToProcess;
use Jimphle\Messaging\MessageHandlerResponse;

class HandleAllMessagesToProcessTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NAME = 'test_name';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $directMessageHandlerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $backgroundMessageHandlerMock;

    public function setUp()
    {
        $this->directMessageHandlerMock = $this->messageHandlerMock();
        $this->backgroundMessageHandlerMock = $this->messageHandlerMock();
    }

    /**
     * @test
     */
    public function itShouldHandleTheMessageDirectly()
    {
        $this->directMessageHandlerMock->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($this->someMessage()))
            ->will($this->returnValue(new Map()));

        $this->handleMessage($this->someMessage());
    }

    /**
     * @test
     */
    public function itShouldReturnTheFirstResponse()
    {
        $secondMessage = $this->someMessage('fooo');
        $response = $this->messageHandlerResponse();
        $response->addMessageToProcessDirectly($secondMessage);

        $this->directMessageHandlerMock->expects($this->at(0))
            ->method('handle')
            ->will($this->returnValue($response));
        $this->directMessageHandlerMock->expects($this->at(1))
            ->method('handle')
            ->will($this->returnValue($secondMessage));

        $this->assertThat(
            $this->handleMessage($this->someMessage()),
            $this->equalTo($response)
        );
    }

    /**
     * @test
     */
    public function itShouldHandleMessagesToProcessDirectly()
    {
        $firstMessage = $this->someMessage('first');
        $secondMessage = $this->someMessage('second');
        $thirdMessage = $this->someMessage('third');
        $fourthMessage = $this->someMessage('fourth');
        $firstResponse = $this->messageHandlerResponse();
        $firstResponse->addMessageToProcessDirectly($secondMessage);
        $firstResponse->addMessageToProcessDirectly($thirdMessage);
        $secondResponse = $this->messageHandlerResponse();
        $secondResponse->addMessageToProcessDirectly($fourthMessage);

        $this->directMessageHandlerMock = $this->messageHandlerMock();
        $this->directMessageHandlerMock->expects($this->at(0))
            ->method('handle')
            ->with($this->equalTo($firstMessage))
            ->will($this->returnValue($firstResponse));
        $this->directMessageHandlerMock->expects($this->at(1))
            ->method('handle')
            ->with($this->equalTo($secondMessage))
            ->will($this->returnValue($secondResponse));
        $this->directMessageHandlerMock->expects($this->at(2))
            ->method('handle')
            ->with($this->equalTo($fourthMessage))
            ->will($this->returnValue(new Map()));
        $this->directMessageHandlerMock->expects($this->at(3))
            ->method('handle')
            ->with($this->equalTo($thirdMessage))
            ->will($this->returnValue(new Map()));

        $this->handleMessage($firstMessage);
    }

    /**
     * @test
     */
    public function itShouldHandleMessagesToProcessInBackgroundToo()
    {
        $firstMessage = $this->someMessage('first');
        $secondMessage = $this->someMessage('second');
        $thirdMessage = $this->someMessage('third');
        $fourthMessage = $this->someMessage('fourth');
        $firstResponse = $this->messageHandlerResponse();
        $firstResponse->addMessageToProcessDirectly($secondMessage);
        $firstResponse->addMessageToProcessInBackground($thirdMessage);
        $secondResponse = $this->messageHandlerResponse();
        $secondResponse->addMessageToProcessInBackground($fourthMessage);

        $this->directMessageHandlerMock = $this->messageHandlerMock();
        $this->directMessageHandlerMock->expects($this->at(0))
            ->method('handle')
            ->with($this->equalTo($firstMessage))
            ->will($this->returnValue($firstResponse));
        $this->directMessageHandlerMock->expects($this->at(1))
            ->method('handle')
            ->with($this->equalTo($secondMessage))
            ->will($this->returnValue($secondResponse));

        $this->backgroundMessageHandlerMock = $this->messageHandlerMock();
        $this->backgroundMessageHandlerMock->expects($this->at(0))
            ->method('handle')
            ->with($this->equalTo($thirdMessage));
        $this->backgroundMessageHandlerMock->expects($this->at(1))
            ->method('handle')
            ->with($this->equalTo($fourthMessage));

        $this->handleMessage($firstMessage);
    }

    /**
     * @test
     */
    public function itShouldNotHandleMessageToProcessInBackgroundIfDirectMessageHandlingFails()
    {
        $firstMessage = $this->someMessage('first');
        $secondMessage = $this->someMessage('second');
        $thirdMessage = $this->someMessage('third');

        $response = $this->messageHandlerResponse();
        $response->addMessageToProcessDirectly($secondMessage);
        $response->addMessageToProcessInBackground($thirdMessage);

        $this->directMessageHandlerMock = $this->messageHandlerMock();
        $this->directMessageHandlerMock->expects($this->at(0))
            ->method('handle')
            ->will($this->returnValue($response));
        $this->directMessageHandlerMock->expects($this->at(1))
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
        $messageHandler = $this->getMock('\Jimphle\Messaging\MessageHandler\MessageHandler');
        return $messageHandler;
    }

    private function handleMessage(Message $event)
    {
        $messageHandler = new HandleAllMessagesToProcess(
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
