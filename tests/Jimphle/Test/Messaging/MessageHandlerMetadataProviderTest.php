<?php
namespace Jimphle\Test\Messaging;

use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandlerMetadataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MessageHandlerMetadataProviderTest extends TestCase
{
    const SOME_ANNOTATION = 'Jimphle\Test\Messaging\SomeAnnotation';
    const SOME_MESSAGE_NAME = 'a_message';
    const ANOTHER_ANNOTATION = 'Jimphle\Test\Messaging\AnotherAnnotation';
    const UNKNOWN_ANNOTATION = 'Jimphle\Test\Messaging\UnknownAnnotation';

    /**
     * @var MockObject
     */
    private $messageHandlerFake;

    /**
     * @var MockObject
     */
    private $reader;

    /**
     * @var MockObject
     */
    private $messageHandlerProvider;

    public function setUp(): void
    {
        $this->reader = $this->createMock(\Doctrine\Common\Annotations\Reader::class);
        $this->messageHandlerFake = new MessageHandlerFake();
        $this->messageHandlerProvider = $this->messageHandlerProviderMock();
        $this->messageHandlerProvider->expects($this->any())
            ->method('getMessageHandlers')
            ->will($this->returnValue(array($this->messageHandlerFake)));
    }

    /**
     * @test
     */
    public function itShouldGetTheMessageHandlers()
    {
        $this->messageHandlerProvider = $this->messageHandlerProviderMock();
        $this->messageHandlerProvider->expects($this->once())
            ->method('getMessageHandlers')
            ->with(
                $this->callback(
                    function (Message $message) {
                        return $message->equals(GenericMessage::generate(MessageHandlerMetadataProviderTest::SOME_MESSAGE_NAME));
                    }
                )
            )
            ->will($this->returnValue(array($this->messageHandlerFake)));
        $this->reader->expects($this->any())
            ->method('getClassAnnotations')
            ->with($this->equalTo(new \ReflectionClass($this->messageHandlerFake)))
            ->will($this->returnValue(array()));
        $this->readAnnotations();
    }

    /**
     * @test
     */
    public function itShouldReadClassAnnotations()
    {
        $this->reader->expects($this->once())
            ->method('getClassAnnotations')
            ->with($this->equalTo(new \ReflectionClass($this->messageHandlerFake)))
            ->will($this->returnValue(array()));
        $this->readAnnotations();
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfUnknownAnnotationFound()
    {
        $this->expectException(\RuntimeException::class);
        $annotations = array(
            new SomeAnnotation(),
            new UnknownAnnotation(),
            new AnotherAnnotation(),
        );
        $this->reader->expects($this->any())
            ->method('getClassAnnotations')
            ->with($this->equalTo(new \ReflectionClass($this->messageHandlerFake)))
            ->will($this->returnValue($annotations));
        $this->readAnnotations();
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfTryingToGetUnknownAnnotation()
    {
        $this->expectException(\RuntimeException::class);
        $this->readAnnotations(self::UNKNOWN_ANNOTATION);
    }

    /**
     * @test
     */
    public function itShouldReturnAnnotationsOfTheGivenType()
    {
        $annotations = array(
            new SomeAnnotation(),
            new SomeAnnotation(),
            new AnotherAnnotation(),
        );
        $this->reader->expects($this->any())
            ->method('getClassAnnotations')
            ->will($this->returnValue($annotations));

        $this->assertThat(
            $this->readAnnotations(),
            $this->equalTo($this->someMetaData())
        );
    }

    /**
     * @test
     */
    public function itShouldCollectAnnotationsFromMultipleHandlers()
    {
        $this->messageHandlerProvider = $this->messageHandlerProviderMock();
        $this->messageHandlerProvider->expects($this->any())
            ->method('getMessageHandlers')
            ->will($this->returnValue(array($this->messageHandlerFake, $this->messageHandlerFake)));

        $firstAnnotations = array(
            new SomeAnnotation()
        );
        $secondAnnotations = array(
            new AnotherAnnotation(),
            new SomeAnnotation(),
        );
        $this->reader->expects($this->any())
            ->method('getClassAnnotations')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue($firstAnnotations),
                    $this->returnValue($secondAnnotations)
                )
            );

        $this->assertThat(
            $this->readAnnotations(),
            $this->equalTo($this->someMetaData())
        );
    }

    private function readAnnotations($annotationClass = self::SOME_ANNOTATION)
    {
        $reader = new MessageHandlerMetadataProvider(
            $this->reader,
            $this->messageHandlerProvider,
            array(self::SOME_ANNOTATION, self::ANOTHER_ANNOTATION)
        );
        return $reader->get($this->someMessage(), $annotationClass);
    }

    /**
     * @return array
     */
    private function someMetaData()
    {
        return array(
            new SomeAnnotation(),
            new SomeAnnotation(),
        );
    }

    private function someMessage()
    {
        return GenericMessage::generate(self::SOME_MESSAGE_NAME);
    }

    private function messageHandlerProviderMock()
    {
        return $this->getMockBuilder(
            \Jimphle\Messaging\MessageHandlerProvider::class
        )
            ->disableOriginalConstructor()
            ->getMock();
    }
}

class SomeAnnotation
{
}

class AnotherAnnotation
{
}

class UnknownAnnotation
{
}

class MessageHandlerFake
{
}
