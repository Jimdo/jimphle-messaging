<?php
namespace Jimphle\Test\Messaging\MessageHandler;

use Jimphle\DataStructure\Vector;
use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandler\HandleMessage;
use Jimphle\Messaging\MessageHandlerResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HandleMessageTest extends TestCase
{
    const TEST_NAME = 'test_message';

    /**
     * @var MockObject
     */
    private $messageHandlerProvider;

    protected function setUp(): void
    {
        $this->messageHandlerProvider = $this->getMockBuilder(
            \Jimphle\Messaging\MessageHandlerProvider::class
        )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function itShouldGetMessageHandlersFromProvider()
    {
        $someMessage = $this->someMessage();
        $this->messageHandlerProvider->expects($this->once())
            ->method('getMessageHandlers')
            ->with($this->equalTo($someMessage))
            ->will($this->returnValue(array($this->messageHandlerMock())));
        $this->handleMessage($someMessage);
    }

    /**
     * @test
     */
    public function itShouldHandleTheMessageWithTheRegisteredHandlers()
    {
        $event = $this->someMessage();
        $firstEventHandler = $this->messageHandlerMock();
        $firstEventHandler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($event));

        $secondEventHandler = $this->messageHandlerMock();
        $secondEventHandler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($event));

        $this->messageHandlerProvider->expects($this->any())
            ->method('getMessageHandlers')
            ->will($this->returnValue(array($firstEventHandler, $secondEventHandler)));

        $this->handleMessage($event);
    }

    /**
     * @test
     */
    public function itShouldCollectMessagesToProcess()
    {
        $expectedResponse = MessageHandlerResponse::fromMap(array());
        $expectedResponse->addMessageToProcessDirectly($this->someMessage());
        $expectedResponse->addMessageToProcessDirectly($this->someMessage());
        $expectedResponse->addMessageToProcessInBackground($this->someMessage());
        $expectedResponse->addMessageToProcessInBackground($this->someMessage());

        $this->messageHandlerProvider->expects($this->any())
            ->method('getMessageHandlers')
            ->will(
                $this->returnValue(
                    array(
                        $this->messageHandlerMockWhichRespondsWithMessagesToProcess(),
                        $this->messageHandlerMockWhichRespondsWithMessagesToProcess(),
                    )
                )
            );
        $this->assertThat(
            $this->handleMessage($this->someMessage()),
            $this->equalTo($expectedResponse)
        );
    }

    /**
     * @test
     */
    public function itShouldReturnTheFirstResponsePayload()
    {
        $expectedPayload = array('foo' => 'bar');
        $expectedResponse = MessageHandlerResponse::fromMap($expectedPayload);
        $expectedResponse->addMessageToProcessDirectly($this->someMessage());
        $expectedResponse->addMessageToProcessDirectly($this->someMessage());
        $expectedResponse->addMessageToProcessInBackground($this->someMessage());
        $expectedResponse->addMessageToProcessInBackground($this->someMessage());

        $firstMessageHandler = $this->messageHandlerMock();
        $firstMessageHandler->expects($this->any())
            ->method('handle')
            ->will($this->returnValue($this->someResponse($expectedPayload)));
        $secondMessageHandler = $this->messageHandlerMock();
        $secondMessageHandler->expects($this->any())
            ->method('handle')
            ->will($this->returnValue($this->someResponse(array('nope' => 'nope'))));

        $this->messageHandlerProvider->expects($this->any())
            ->method('getMessageHandlers')
            ->will(
                $this->returnValue(
                    array(
                        $firstMessageHandler,
                        $secondMessageHandler,
                    )
                )
            );
        $this->assertThat(
            $this->handleMessage($this->someMessage()),
            $this->equalTo($expectedResponse)
        );
    }

    /**
     * @test
     */
    public function itShouldReturnTheFirstResponseAlsoIfBaseDataStructure()
    {
        $expectedPayload = array('foo');
        $expectedResponse = MessageHandlerResponse::fromVector($expectedPayload);

        $firstMessageHandler = $this->messageHandlerMock();
        $firstMessageHandler->expects($this->any())
            ->method('handle')
            ->will($this->returnValue(new Vector($expectedPayload)));
        $secondMessageHandler = $this->messageHandlerMock();
        $secondMessageHandler->expects($this->any())
            ->method('handle')
            ->will($this->returnValue(MessageHandlerResponse::fromMap(array('nope' => 'nope'))));

        $this->messageHandlerProvider->expects($this->any())
            ->method('getMessageHandlers')
            ->will(
                $this->returnValue(
                    array(
                        $firstMessageHandler, $secondMessageHandler
                    )
                )
            );
        $this->assertThat(
            $this->handleMessage($this->someMessage()),
            $this->equalTo($expectedResponse)
        );
    }

    /**
     * @test
     */
    public function itShouldReturnTheFirstResponseAlsoIfNull()
    {
        $firstMessageHandler = $this->messageHandlerMock();
        $firstMessageHandler->expects($this->any())
            ->method('handle')
            ->will($this->returnValue(null));
        $secondMessageHandler = $this->messageHandlerMock();
        $secondMessageHandler->expects($this->any())
            ->method('handle')
            ->will($this->returnValue(MessageHandlerResponse::fromMap(array('nope' => 'nope'))));

        $this->messageHandlerProvider->expects($this->any())
            ->method('getMessageHandlers')
            ->will(
                $this->returnValue(
                    array(
                        $firstMessageHandler, $secondMessageHandler
                    )
                )
            );
        $this->assertThat(
            $this->handleMessage($this->someMessage()),
            $this->equalTo(MessageHandlerResponse::withoutPayload())
        );
    }

    private function messageHandlerMock()
    {
        $messageHandler = $this->createMock(\Jimphle\Messaging\MessageHandler\MessageHandler::class);
        return $messageHandler;
    }

    private function handleMessage(Message $event)
    {
        $messageHandler = new HandleMessage(
            $this->messageHandlerProvider
        );
        return $messageHandler->handle($event);
    }

    private function someMessage()
    {
        return GenericMessage::generate(self::TEST_NAME);
    }

    private function messageHandlerMockWhichRespondsWithMessagesToProcess()
    {
        $response = $this->someResponse();
        $messageHandler = $this->messageHandlerMock();
        $messageHandler->expects($this->any())
            ->method('handle')
            ->will($this->returnValue($response));
        return $messageHandler;
    }

    private function someResponse(array $payload = array())
    {
        $response = MessageHandlerResponse::fromMap($payload);
        $response->addMessageToProcessDirectly($this->someMessage());
        $response->addMessageToProcessInBackground($this->someMessage());
        return $response;
    }
}
