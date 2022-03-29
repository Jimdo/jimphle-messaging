<?php
namespace Jimphle\Test\Messaging;

use Jimphle\Exception\RuntimeException;
use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\MessageHandlerProvider;
use PHPUnit\Framework\TestCase;

class MessageHandlerProviderTest extends TestCase
{
    const SOME_COMMAND_NAME = 'a_command';
    const SOME_EVENT_NAME = 'an_event';
    const SOME_MESSAGE_NAME = 'a_message';

    /**
     * @var \ArrayObject
     */
    private $serviceContainer;

    /**
     * @var MessageHandlerProvider
     */
    private $messageHandlerProvider;

    public function setUp(): void
    {
        $this->serviceContainer = new \ArrayObject();
        $this->messageHandlerProvider = new MessageHandlerProvider(
            $this->serviceContainer
        );
    }

    /**
     * @test
     */
    public function itShouldReturnAnInteractor()
    {
        $messageHandler = $this->createMock(\Jimphle\Messaging\MessageHandler\MessageHandler::class);
        $this->serviceContainer[self::SOME_COMMAND_NAME] = $messageHandler;
        $this->assertThat(
            $this->messageHandlerProvider->getCommandHandler(self::SOME_COMMAND_NAME),
            $this->equalTo($messageHandler)
        );
    }

    /**
     * @test
     */
    public function itShouldReturnACommandHandler()
    {
        $messageHandler = $this->createMock(\Jimphle\Messaging\MessageHandler\MessageHandler::class);
        $this->serviceContainer[self::SOME_COMMAND_NAME] = $messageHandler;
        $this->assertThat(
            $this->messageHandlerProvider->getCommandHandler(self::SOME_COMMAND_NAME),
            $this->equalTo($messageHandler)
        );
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfCommandHandlerIsNotAValidMessageHandler()
    {
        $this->expectException(RuntimeException::class);
        $this->serviceContainer[self::SOME_COMMAND_NAME] = new \stdClass;
        $this->messageHandlerProvider->getCommandHandler(self::SOME_COMMAND_NAME);
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfEventHandlersAreNotAList()
    {
        $this->expectException(RuntimeException::class);
        $this->serviceContainer[self::SOME_EVENT_NAME] = new \stdClass;
        $this->messageHandlerProvider->getEventHandlers(self::SOME_EVENT_NAME);
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfEventHandlerIsNotAValidMessageHandler()
    {
        $this->expectException(RuntimeException::class);
        $this->serviceContainer[self::SOME_EVENT_NAME] = array(new \stdClass);
        $this->messageHandlerProvider->getEventHandlers(self::SOME_EVENT_NAME);
    }

    /**
     * @test
     */
    public function itShouldReturnAnEmptyListOfEventHandlersIfNothingWasFound()
    {
        $this->assertThat(
            $this->messageHandlerProvider->getEventHandlers(self::SOME_EVENT_NAME),
            $this->equalTo(array())
        );
    }

    /**
     * @test
     */
    public function itShouldReturnSomeEventHandlers()
    {
        $messageHandler = $this->createMock(\Jimphle\Messaging\MessageHandler\MessageHandler::class);
        $this->serviceContainer[self::SOME_EVENT_NAME] = array($messageHandler, $messageHandler);
        $this->assertThat(
            $this->messageHandlerProvider->getEventHandlers(self::SOME_EVENT_NAME),
            $this->equalTo(array($messageHandler, $messageHandler))
        );
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfMessageTypeIsUndefined()
    {
        $this->expectException(RuntimeException::class);
        $this->messageHandlerProvider->getMessageHandlers(
            GenericMessage::generate(self::SOME_MESSAGE_NAME)
        );
    }
}
