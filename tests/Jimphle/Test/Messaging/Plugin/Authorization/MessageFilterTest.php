<?php
namespace Jimphle\Test\Messaging\Plugin\Authorization;

use Jimphle\Messaging\Command;
use Jimphle\Messaging\Message;
use Jimphle\Messaging\Plugin\Authorization\MessageFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MessageFilterTest extends TestCase
{
    const SOME_MESSAGE_HANDLER_NAME = 'vlaehfjkwehf';
    const SOME_AUTH_CONSTRAINT = 'some_auth_constraint';
    const SOME_OTHER_AUTH_CONSTRAINT = 'some_other_auth_constraint';

    /**
     * @var MockObject
     */
    private $metadataProvider;

    /**
     * @var MockObject
     */
    public $serviceContainer;

    /**
     * @var MockObject
     */
    public $authorizationContext;

    public function setUp(): void
    {
        $this->loadMetaDataProvider();
        $this->loadServiceContainer();
        $this->loadAuthorizationContext();

        $this->metadataProvider->expects($this->any())
            ->method('get')
            ->will($this->returnValue(array($this->metadataRow(), $this->anotherMetadataRow())));
        $this->serviceContainer->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnValue($this->authorizationConstraint()));
    }

    /**
     * @test
     */
    public function itShouldGetAuthorizationMetadataForMessage()
    {
        $this->loadMetaDataProvider();
        $this->metadataProvider->expects($this->once())
            ->method('get')
            ->with(
                $this->callback(
                    function (Message $message) {
                        return $message->equals(Command::generate(MessageFilterTest::SOME_MESSAGE_HANDLER_NAME));
                    }
                ),
                $this->equalTo(MessageFilter::ANNOTATION_CLASS)
            )
            ->will($this->returnValue(array($this->metadataRow())));

        $this->filterMessage();
    }

    /**
     * @test
     */
    public function itShouldGetConstraintsFromServiceContainer()
    {
        $this->loadServiceContainer();
        $this->serviceContainer->expects($this->at(0))
            ->method('offsetGet')
            ->with($this->equalTo(self::SOME_AUTH_CONSTRAINT))
            ->will($this->returnValue($this->authorizationConstraint()));
        $this->serviceContainer->expects($this->at(1))
            ->method('offsetGet')
            ->with($this->equalTo(self::SOME_OTHER_AUTH_CONSTRAINT))
            ->will($this->returnValue($this->authorizationConstraint()));

        $this->filterMessage();
    }

    /**
     * @test
     */
    public function itShouldAssertConstraintsAreValid()
    {
        $authorizationConstraint = $this->authorizationConstraint();
        $anotherAuthorizationConstraint = $this->anotherAuthorizationConstraint();

        $this->serviceContainer->expects($this->any())
            ->method('offsetGet')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue($authorizationConstraint),
                    $this->returnValue($anotherAuthorizationConstraint)
                )
            );

        $this->authorizationContext->expects($this->once())
            ->method('assertAccessIsGranted')
            ->with(
                $this->callback(
                    function (Message $message) {
                        return $message->equals(Command::generate(MessageFilterTest::SOME_MESSAGE_HANDLER_NAME));
                    }
                ),
                $this->equalTo(array($authorizationConstraint, $anotherAuthorizationConstraint))
            );

        $this->filterMessage();
    }

    /**
     * @test
     */
    public function itShouldReturnTheMessagePassedIn()
    {
        $this->assertThat(Command::generate(self::SOME_MESSAGE_HANDLER_NAME)->equals($this->filterMessage()), $this->isTrue());
    }

    private function filterMessage()
    {
        $filter = new MessageFilter(
            $this->metadataProvider,
            $this->serviceContainer,
            $this->authorizationContext
        );
        return $filter->filter(Command::generate(self::SOME_MESSAGE_HANDLER_NAME));
    }

    private function loadMetaDataProvider()
    {
        $this->metadataProvider = $this->getMockBuilder(
            \Jimphle\Messaging\MessageHandlerMetadataProvider::class
        )
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function metadataRow()
    {
        $metadataRow = new \stdClass();
        $metadataRow->name = self::SOME_AUTH_CONSTRAINT;
        return $metadataRow;
    }

    private function anotherMetadataRow()
    {
        $metadataRow = new \stdClass();
        $metadataRow->name = self::SOME_OTHER_AUTH_CONSTRAINT;
        return $metadataRow;
    }

    private function loadServiceContainer()
    {
        $this->serviceContainer = $this->createMock(\ArrayAccess::class);
    }

    private function authorizationConstraint()
    {
        return $this->createMock(\Jimphle\Messaging\Plugin\Authorization\Constraint::class);
    }

    private function anotherAuthorizationConstraint()
    {
        return $this->createMock(\Jimphle\Messaging\Plugin\Authorization\Constraint::class);
    }

    private function loadAuthorizationContext()
    {
        $this->authorizationContext = $this->createMock(\Jimphle\Messaging\Plugin\Authorization\Context::class);
    }
}
