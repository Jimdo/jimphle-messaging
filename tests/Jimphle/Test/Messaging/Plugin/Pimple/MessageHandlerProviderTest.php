<?php
namespace Jimphle\Test\Messaging\Plugin\Pimple;

use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\Plugin\Pimple\MessageHandlerProvider;

class MessageHandlerProviderTest extends \PHPUnit_Framework_TestCase
{
    const SOME_COMMAND_NAME = 'a_command';
    const SOME_EVENT_NAME = 'an_event';
    const SOME_MESSAGE_NAME = 'a_message';

    /**
     * @var \Pimple
     */
    private $serviceContainer;

    /**
     * @var MessageHandlerProvider
     */
    private $messageHandlerProvider;

    public function setUp()
    {
        $this->serviceContainer = new \Pimple();
        $this->messageHandlerProvider = new MessageHandlerProvider(
            $this->serviceContainer
        );
    }

    /**
     * @test
     */
    public function itShouldReturnAnInteractor()
    {
        $interactor = $this->getMock('\Jimphle\Messaging\MessageHandler\MessageHandler');
        $this->serviceContainer[self::SOME_COMMAND_NAME] = function () use ($interactor) {
            return $interactor;
        };
        $this->assertThat(
            $this->messageHandlerProvider->getCommandHandler(self::SOME_COMMAND_NAME),
            $this->equalTo($interactor)
        );
    }

    /**
     * @test
     */
    public function itShouldReturnACommandHandler()
    {
        $messageHandler = $this->getMock('\Jimphle\Messaging\MessageHandler\MessageHandler');
        $this->serviceContainer[self::SOME_COMMAND_NAME] = function () use ($messageHandler) {
            return $messageHandler;
        };
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
        $this->serviceContainer[self::SOME_COMMAND_NAME] = function () {
            return new \stdClass;
        };
        $this->messageHandlerProvider->getCommandHandler(self::SOME_COMMAND_NAME);
    }

    /**
     * @test
     * @expectedException \Jimphle\Exception\RuntimeException
     */
    public function itShouldThrowAnExceptionIfEventHandlersAreNotAList()
    {
        $this->serviceContainer[self::SOME_EVENT_NAME] = function () {
            return new \stdClass;
        };
        $this->messageHandlerProvider->getEventHandlers(self::SOME_EVENT_NAME);
    }

    /**
     * @test
     * @expectedException \Jimphle\Exception\RuntimeException
     */
    public function itShouldThrowAnExceptionIfEventHandlerIsNotAValidMessageHandler()
    {
        $this->serviceContainer[self::SOME_EVENT_NAME] = function () {
            return array(new \stdClass);
        };
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
        $this->serviceContainer[self::SOME_EVENT_NAME] = function () use ($messageHandler) {
            return array($messageHandler, $messageHandler);
        };
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
