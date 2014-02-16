<?php
namespace Jimphle\Test\Messaging\Plugin\Validation;

use Jimphle\Messaging\Plugin\Validation\MessagePropertyAnnotation;

class ConstraintAnnotationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldLoadPropertyValuesFromNumericKeys()
    {
        $propValue = 'foo';
        $constraintValue = 'bar';
        $annotation = new MessagePropertyAnnotation(
            array('value' => array($propValue, $constraintValue))
        );
        $this->assertThat($annotation->prop, $this->equalTo($propValue));
        $this->assertThat($annotation->constraint, $this->equalTo($constraintValue));
    }

    /**
     * @test
     */
    public function itShouldLoadPropertyValuesFromStringKeys()
    {
        $propValue = 'foo';
        $constraintValue = 'bar';
        $annotation = new MessagePropertyAnnotation(
            array('prop' => $propValue, 'constraint' => $constraintValue)
        );
        $this->assertThat($annotation->prop, $this->equalTo($propValue));
        $this->assertThat($annotation->constraint, $this->equalTo($constraintValue));
    }
}
