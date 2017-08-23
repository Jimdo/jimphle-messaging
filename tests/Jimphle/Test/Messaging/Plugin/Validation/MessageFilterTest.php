<?php
namespace Jimphle\Test\Messaging\Plugin\Validation;

use Jimphle\Exception\ValidationFailedException;
use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\Plugin\Validation\MessageFilter;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\ConstraintViolationList;

class MessageFilterTest extends \PHPUnit_Framework_TestCase
{
    const SOME_PROPERTY = 'some_prop';
    const SOME_MESSAGE = 'sine blbfjkvhfdjsk';
    const SOME_FAKE_UUID = 'hihi-fake-hihi-hihi';
    const SOME_MESSAGE_TEMPLATE = 'some_message_template';
    const SOME_CODE = 'some_code_for_message';

    private $constraintViolationCodeMap;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    public function setUp()
    {
        $this->validator = $this->validatorMock();
        $this->validator->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList(array())));

        $this->constraintViolationCodeMap = array(
            self::SOME_MESSAGE_TEMPLATE => self::SOME_FAKE_UUID
        );
    }

    /**
     * @test
     */
    public function itShouldValidateTheRequest()
    {
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->someMessage()))
            ->will($this->returnValue(new ConstraintViolationList(array())));

        $this->filterMessage();
    }

    /**
     * @test
     * @expectedException \Jimphle\Exception\ValidationFailedException
     */
    public function itShouldThrowAnValidationExceptionIfValidationFails()
    {
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->someMessage()))
            ->will($this->throwException(new ValidatorException()));

        $this->filterMessage();
    }

    /**
     * @test
     */
    public function itShouldAssertThatRequestIsValid()
    {
        $this->validator = $this->validatorMock();
        $this->validator->expects($this->any())
            ->method('validate')
            ->will(
                $this->returnValue(
                    new ConstraintViolationList(
                        array(
                            $this->constraintViolationWithCode(),
                            $this->constraintViolationWithCode()
                        )
                    )
                )
            );

        try {
            $this->filterMessage();
            $this->fail('expected an exception here!');
        } catch (ValidationFailedException $e) {
            $expectedException = new ValidationFailedException(
                MessageFilter::VALIDATION_FAILED_MESSAGE
            );
            $expectedException->setErrors(array($this->errorDefinition(), $this->errorDefinition()));
            $this->assertThat($e, $this->equalTo($expectedException));
        }
    }

    /**
     * @test
     */
    public function itShouldAssertWithoutErrorCodeIfNotInMap()
    {
        $this->validator = $this->validatorMock();
        $this->validator->expects($this->any())
            ->method('validate')
            ->will(
                $this->returnValue(
                    new ConstraintViolationList(array($this->constraintViolationWithoutCode()))
                )
            );
        $this->constraintViolationCodeMap = array();

        try {
            $this->filterMessage();
            $this->fail('expected an exception here!');
        } catch (ValidationFailedException $e) {
            $expectedException = new ValidationFailedException(
                MessageFilter::VALIDATION_FAILED_MESSAGE
            );
            $expectedException->setErrors(array($this->errorDefinitionWithEmptyCode()));
            $this->assertThat($e, $this->equalTo($expectedException));
        }
    }

    /**
     * @test
     */
    public function itShouldReturnTheGivenMessage()
    {
        $this->assertThat($this->filterMessage(), $this->equalTo($this->someMessage()));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function validatorMock()
    {
        return $this->getMock('\Symfony\Component\Validator\Validator\ValidatorInterface');
    }

    private function someMessage()
    {
        return GenericMessage::generateDummy(
            array('blub1' => 'blaaa', 'hui' => 'haaa')
        );
    }

    private function filterMessage()
    {
        $filter = new MessageFilter($this->validator);
        return $filter->filter($this->someMessage());
    }

    private function constraintViolationWithCode()
    {
        $constraintViolation = $this->getMock('\Symfony\Component\Validator\ConstraintViolationInterface');
        $constraintViolation->expects($this->any())->method('getPropertyPath')->will(
            $this->returnValue(self::SOME_PROPERTY)
        );
        $constraintViolation->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue(self::SOME_MESSAGE));
        $constraintViolation->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue(self::SOME_CODE));
        $constraintViolation->expects($this->any())->method('getMessageTemplate')->will(
            $this->returnValue(self::SOME_MESSAGE_TEMPLATE)
        );
        return $constraintViolation;
    }

    private function constraintViolationWithoutCode()
    {
        $constraintViolation = $this->getMock('\Symfony\Component\Validator\ConstraintViolationInterface');
        $constraintViolation->expects($this->any())->method('getPropertyPath')->will(
            $this->returnValue(self::SOME_PROPERTY)
        );
        $constraintViolation->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue(self::SOME_MESSAGE));
        $constraintViolation->expects($this->any())->method('getMessageTemplate')->will(
            $this->returnValue(self::SOME_MESSAGE_TEMPLATE)
        );
        return $constraintViolation;
    }

    /**
     * @return array
     */
    private function errorDefinition()
    {
        return array(
            'property' => self::SOME_PROPERTY,
            'code' => self::SOME_CODE,
            'message' => self::SOME_MESSAGE
        );
    }

    private function errorDefinitionWithEmptyCode()
    {
        return array('property' => self::SOME_PROPERTY, 'code' => null, 'message' => self::SOME_MESSAGE);
    }
}
