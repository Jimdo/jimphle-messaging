<?php
namespace Jimphle\Test\Messaging\MessageHandler;

use Jimphle\DataStructure\Map;
use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandler\HandleMessagesToProcessDirectly;
use Jimphle\Messaging\MessageHandlerResponse;

class HandleMessagesToProcessDirectlyTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NAME = 'test_name';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $directMessageHandlerMock;

    public function setUp()
    {
        $this->directMessageHandlerMock = $this->messageHandlerMock();
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
    public function itShouldCollectMessagesToProcessInBackground()
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

        $response = $this->handleMessage($firstMessage);

        $this->assertEquals(
            array(
                $thirdMessage,
                $fourthMessage
            ),
            $response->getMessagesToProcessInBackground()
        );
    }

    private function messageHandlerMock()
    {
        $messageHandler = $this->getMock('\Jimphle\Messaging\MessageHandler\MessageHandler');
        return $messageHandler;
    }

    private function handleMessage(Message $event)
    {
        $messageHandler = new HandleMessagesToProcessDirectly(
            $this->directMessageHandlerMock
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
