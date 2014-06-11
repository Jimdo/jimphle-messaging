<?php
namespace Jimphle\Test\Messaging\Plugin\Validation;

use Jimphle\Messaging\Command;
use Jimphle\Messaging\Plugin\Validation\MessagePropertyMetadata;

class MessagePropertyMetadataTest extends \PHPUnit_Framework_TestCase
{
    const MESSAGE_HANDLER_CLASS = '\Jimphle\Messaging\Command';
    const SOME_NAME = 'blub';

    /**
     * @test
     * @expectedException \Symfony\Component\Validator\Exception\ValidatorException
     */
    public function itShouldThrowAnExceptionIfObjectIsOfWrongType()
    {
        $this->getPropertyValue('\stdClass');
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Validator\Exception\ValidatorException
     */
    public function itShouldThrowAnExceptionIfPropertyDoesNotExistsOnObject()
    {
        $this->getPropertyValue(self::MESSAGE_HANDLER_CLASS, 'foo');
    }

    /**
     * @test
     */
    public function itShouldReturnTheObjectPropertyValue()
    {
        $this->assertThat($this->getPropertyValue(), $this->equalTo(self::SOME_NAME));
    }

    private function getPropertyValue($class = self::MESSAGE_HANDLER_CLASS, $name = 'name')
    {
        $metadata = new MessagePropertyMetadata($class, $name);
        return $metadata->getPropertyValue(Command::generateDummy(array('name' => self::SOME_NAME)));
    }
}
