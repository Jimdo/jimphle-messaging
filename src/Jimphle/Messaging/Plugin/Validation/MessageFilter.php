<?php
namespace Jimphle\Messaging\Plugin\Validation;

use Symfony\Component\Validator\ValidatorInterface;

class MessageFilter implements \Jimphle\Messaging\Filter
{
    const VALIDATION_FAILED_MESSAGE = 'Validation failed';

    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param \Jimphle\Messaging\Message $message
     * @throws \Jimphle\Exception\ValidationFailedException
     * @return \Jimphle\Messaging\Message
     */
    public function filter(\Jimphle\Messaging\Message $message)
    {
        try {
            $constraintViolations = $this->validator->validate($message);
        } catch (\Symfony\Component\Validator\Exception\ValidatorException $e) {
            throw new \Jimphle\Exception\ValidationFailedException($e->getMessage());
        }
        if (count($constraintViolations) > 0) {
            $errors = array();
            foreach ($constraintViolations as $constraintViolation) {
                /**
                 * @var Symfony\Component\Validator\ConstraintViolationInterface $constraintViolation
                 */
                $errors[] = array(
                    'property' => $constraintViolation->getPropertyPath(),
                    'code' => null,
                    'message' => $constraintViolation->getMessage()
                );
            }
            $exception = new \Jimphle\Exception\ValidationFailedException(self::VALIDATION_FAILED_MESSAGE);
            $exception->setErrors($errors);
            throw $exception;
        }
        return $message;
    }
}
