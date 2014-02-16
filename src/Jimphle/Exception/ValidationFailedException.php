<?php
namespace Jimphle\Exception;

class ValidationFailedException extends \Jimphle\Exception\LogicException
{
    /**
     * @var array
     */
    protected $errors = array();

    /**
     * @param array $errors
     * @return void
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * @return array|null
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
