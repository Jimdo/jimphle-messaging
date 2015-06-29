<?php
namespace Jimphle\Test\Messaging\Plugin\Validation;

use Jimphle\Messaging\Command;
use Jimphle\Messaging\Plugin\Validation\MessageHandlerMetadataFactory;
use Jimphle\Messaging\Plugin\Validation\MessageMetadata;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Type;

class MessageHandlerMetadataFactoryTest extends \PHPUnit_Framework_TestCase
{
    const SOME_COMMAND = 'some_command';
    const SOME_SERVICE_DEFINITION_ID = 'some.service_definition';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceContainer;

    public function setUp()
    {
        $this->metadataProvider = $this->getMockBuilder(
            '\Jimphle\Messaging\MessageHandlerMetadataProvider'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->serviceContainer = $this->getMock(
            'ArrayAccess'
        );
    }

    /**
     * @test
     */
    public function itShouldOnlyCollectMetadataFromJimphleMessages()
    {
        $messageMetadata = new MessageMetadata('\Jimphle\Messaging\Command');
        $this->assertThat(
            $this->getMetadataFor(new \stdClass()),
            $this->equalTo($messageMetadata)
        );
    }

    /**
     * @test
     */
    public function itShouldGetMetadata()
    {
        $message = $this->someMessage();

        $this->metadataProvider->expects($this->at(0))
            ->method('get')
            ->with(
                $this->equalTo($message),
                $this->equalTo(MessageHandlerMetadataFactory::MESSAGE_PROPERTY_ANNOTATION_CLASS)
            )
            ->will($this->returnValue(array()));
        $this->metadataProvider->expects($this->at(1))
            ->method('get')
            ->with(
                $this->equalTo($message),
                $this->equalTo(MessageHandlerMetadataFactory::MESSAGE_ANNOTATION_CLASS)
            )
            ->will($this->returnValue(array()));

        $this->getMetadataFor($message);
    }

    /**
     * @test
     */
    public function itShouldReturnMetadataForMessagePropertyConstraint()
    {
        $message = $this->someMessage();
        $annotation = $this->someMessagePropertyAnnotation();

        $this->metadataProvider->expects($this->any())
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(array($annotation)),
                    $this->returnValue(array())
                )
            );

        $messageMetadata = new MessageMetadata('\Jimphle\Messaging\Command');
        $messageMetadata->addPropertyConstraint('name', new Type(array('string')));
        $this->assertThat(
            $this->getMetadataFor($message),
            $this->equalTo($messageMetadata)
        );
    }

    /**
     * @test
     */
    public function itShouldReturnMetadataForMessageConstraint()
    {
        $message = $this->someMessage();
        $annotation = $this->someMessageAnnotation();
        $someConstraint = array('name', new Type(array('string')));
        $someConstraints = array(
            $someConstraint,
            $someConstraint,
        );

        $this->metadataProvider->expects($this->any())
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(array()),
                    $this->returnValue(array($annotation, $annotation))
                )
            );
        $this->serviceContainer->expects($this->exactly(2))
            ->method('offsetGet')
            ->with($this->equalTo(self::SOME_SERVICE_DEFINITION_ID))
            ->will($this->returnValue($someConstraints));

        $messageMetadata = new MessageMetadata('\Jimphle\Messaging\Command');
        $messageMetadata->addPropertyConstraint('name', new Type(array('string')));
        $messageMetadata->addPropertyConstraint('name', new Type(array('string')));
        $messageMetadata->addPropertyConstraint('name', new Type(array('string')));
        $messageMetadata->addPropertyConstraint('name', new Type(array('string')));
        $this->assertThat(
            $this->getMetadataFor($message),
            $this->equalTo($messageMetadata)
        );
    }

    private function getMetadataFor($object)
    {
        $factory = new MessageHandlerMetadataFactory(
            $this->metadataProvider,
            $this->serviceContainer
        );
        return $factory->getMetadataFor($object);
    }

    private function someMessagePropertyAnnotation()
    {
        $annotation = new \stdClass;
        $annotation->prop = 'name';
        $annotation->constraint = new Type(array('string'));
        return $annotation;
    }

    private function someMessage()
    {
        $message = Command::generate(self::SOME_COMMAND, array('name' => 'foo'));
        return $message;
    }

    private function someMessageAnnotation()
    {
        $annotation = new \stdClass;
        $annotation->name = self::SOME_SERVICE_DEFINITION_ID;
        return $annotation;
    }
}
