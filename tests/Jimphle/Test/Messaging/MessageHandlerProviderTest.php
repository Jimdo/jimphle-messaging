<?php
namespace Jimphle\Test\Messaging;

use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\MessageHandlerProvider;

class MessageHandlerProviderTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
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
        $messageHandler = $this->getMock('\Jimphle\Messaging\MessageHandler\MessageHandler');
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
        $messageHandler = $this->getMock('\Jimphle\Messaging\MessageHandler\MessageHandler');
        $this->serviceContainer[self::SOME_COMMAND_NAME] = $messageHandler;
        $this->assertThat(
            $this->messageHandlerProvider->getCommandHandler(self::SOME_COMMAND_NAME),
            $this->equalTo($messageHandler)
        );
    }

    /**
     * @test
     * @expectedException \Jimphle\Exception\RuntimeException
     */
    public function itShouldThrowAnExceptionIfCommandHandlerIsNotAValidMessageHandler()
    {
        $this->serviceContainer[self::SOME_COMMAND_NAME] = new \stdClass;
        $this->messageHandlerProvider->getCommandHandler(self::SOME_COMMAND_NAME);
    }

    /**
     * @test
     * @expectedException \Jimphle\Exception\RuntimeException
     */
    public function itShouldThrowAnExceptionIfEventHandlersAreNotAList()
    {
        $this->serviceContainer[self::SOME_EVENT_NAME] = new \stdClass;
        $this->messageHandlerProvider->getEventHandlers(self::SOME_EVENT_NAME);
    }

    /**
     * @test
     * @expectedException \Jimphle\Exception\RuntimeException
     */
    public function itShouldThrowAnExceptionIfEventHandlerIsNotAValidMessageHandler()
    {
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
        $messageHandler = $this->getMock('\Jimphle\Messaging\MessageHandler\MessageHandler');
        $this->serviceContainer[self::SOME_EVENT_NAME] = array($messageHandler, $messageHandler);
        $this->assertThat(
            $this->messageHandlerProvider->getEventHandlers(self::SOME_EVENT_NAME),
            $this->equalTo(array($messageHandler, $messageHandler))
        );
    }

    /**
     * @test
     * @expectedException \Jimphle\Exception\RuntimeException
     */
    public function itShouldThrowAnExceptionIfMessageTypeIsUndefined()
    {
        $this->messageHandlerProvider->getMessageHandlers(
            GenericMessage::generate(self::SOME_MESSAGE_NAME)
        );
    }
}
