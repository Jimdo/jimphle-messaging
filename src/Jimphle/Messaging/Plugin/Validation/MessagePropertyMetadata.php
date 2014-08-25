<?php
namespace Jimphle\Messaging\Plugin\Validation;

use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Mapping\PropertyMetadata;

class MessagePropertyMetadata extends PropertyMetadata
{
    /**
     * Constructor.
     *
     * @param string $class    The name of the class this member is defined on
     * @param string $name     The name of the member
     */
    public function __construct($class, $name)
    {
        $this->class = $class;
        $this->name = $name;
        $this->property = $name;
    }

    public function getPropertyValue($object)
    {
        if (!is_a($object, $this->class)) {
            throw new ValidatorException(sprintf('Object is not of type %s', $this->class));
        }
        if (!isset($object->{$this->name})) {
            return null;
        }
        return $object->{$this->name};
    }

    protected function newReflectionMember($objectOrClassName)
    {
        return null;
    }

    public function isPublic($objectOrClassName)
    {
        return true;
    }

    public function isProtected($objectOrClassName)
    {
        return false;
    }

    public function isPrivate($objectOrClassName)
    {
        return false;
    }
}
