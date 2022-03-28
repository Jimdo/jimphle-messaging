<?php
namespace Jimphle\Test\Messaging\Plugin\Validation;

use Jimphle\Messaging\Command;
use Jimphle\Messaging\Plugin\Validation\MessagePropertyMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\ValidatorException;

class MessagePropertyMetadataTest extends TestCase
{
    const MESSAGE_HANDLER_CLASS = '\Jimphle\Messaging\Command';
    const SOME_NAME = 'blub';

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfObjectIsOfWrongType()
    {
        $this->expectException(ValidatorException::class);
        $this->getPropertyValue('\stdClass');
    }

    /**
     * @test
     */
    public function itShouldReturnNullIfPropertyDoesNotExistsOnObject()
    {
        $this->assertThat($this->getPropertyValue(self::MESSAGE_HANDLER_CLASS, 'foo'), $this->isNull());
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
