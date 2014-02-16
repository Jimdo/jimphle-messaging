<?php
namespace Jimphle\Test\Messaging\Plugin\Authorization;

use Jimphle\DataStructure\Map;
use Jimphle\Messaging\Plugin\Authorization\ConstraintCollection;

class ConstraintCollectionTest extends \PHPUnit_Framework_TestCase
{
    const SOME_ERROR_MESSAGE = 'some bblaaaa';

    /**
     * @test
     */
    public function itShouldValidateACollectionOfConstraints()
    {
        $constraints = array(
            $this->someValidConstraintMock(),
            $this->someValidConstraintMock(),
        );
        $collection = new ConstraintCollection($constraints);
        $this->assertThat($collection->validate($this->someRequest()), $this->isTrue());
        $this->assertThat($collection->getErrorMessage(), $this->isNull());
    }

    /**
     * @test
     */
    public function itShouldStopValidationIfAConstraintFails()
    {
        $constraintExpectedNeverToBeCalled = $this->getMock('\Jimphle\Messaging\Plugin\Authorization\Constraint');
        $constraintExpectedNeverToBeCalled->expects($this->never())
            ->method('validate');

        $invalidConstraint = $this->getMock('\Jimphle\Messaging\Plugin\Authorization\Constraint');
        $invalidConstraint->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->someRequest()))
            ->will($this->returnValue(false));
        $invalidConstraint->expects($this->once())
            ->method('getErrorMessage')
            ->will($this->returnValue(self::SOME_ERROR_MESSAGE));

        $constraints = array(
            $this->someValidConstraintMock(),
            $invalidConstraint,
            $constraintExpectedNeverToBeCalled,
        );
        $collection = new ConstraintCollection($constraints);
        $this->assertThat($collection->validate($this->someRequest()), $this->isFalse());
        $this->assertThat($collection->getErrorMessage(), $this->equalTo(self::SOME_ERROR_MESSAGE));
    }

    private function someRequest()
    {
        return new Map();
    }

    private function someValidConstraintMock()
    {
        $constraint = $this->getMock('\Jimphle\Messaging\Plugin\Authorization\Constraint');
        $constraint->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->someRequest()))
            ->will($this->returnValue(true));
        return $constraint;
    }
}
