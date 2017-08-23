<?php
namespace Jimphle\Messaging\Plugin\Validation;

use Jimphle\Exception\ValidationFailedException;
use Jimphle\Messaging\Filter;
use Jimphle\Messaging\Message;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MessageFilter implements Filter
{
    const VALIDATION_FAILED_MESSAGE = 'Validation failed';

    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Message $message
     * @throws ValidationFailedException
     * @return Message
     */
    public function filter(Message $message)
    {
        try {
            $constraintViolations = $this->validator->validate($message);
        } catch (ValidatorException $e) {
            throw new ValidationFailedException($e->getMessage());
        }
        if (count($constraintViolations) > 0) {
            $errors = array();
            foreach ($constraintViolations as $constraintViolation) {
                /**
                 * @var \Symfony\Component\Validator\ConstraintViolationInterface $constraintViolation
                 */
                $errors[] = array(
                    'property' => $constraintViolation->getPropertyPath(),
                    'code' => $constraintViolation->getCode(),
                    'message' => $constraintViolation->getMessage()
                );
            }
            $exception = new ValidationFailedException(self::VALIDATION_FAILED_MESSAGE);
            $exception->setErrors($errors);
            throw $exception;
        }
        return $message;
    }
}
