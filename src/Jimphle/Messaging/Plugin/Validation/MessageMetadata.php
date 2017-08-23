<?php
namespace Jimphle\Messaging\Plugin\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class MessageMetadata extends ClassMetadata
{
    public function addPropertyConstraint($property, Constraint $constraint)
    {
        if (!isset($this->properties[$property])) {
            $this->properties[$property] = new MessagePropertyMetadata(
                $this->getClassName(),
                $property
            );

            $property = $this->addProperty($property);
        }

        $constraint->addImplicitGroupName($this->getDefaultGroup());
        $this->properties[$property]->addConstraint($constraint);

        return $this;
    }

    public function addGetterConstraint($property, Constraint $constraint)
    {
        throw new \BadMethodCallException('message does not support getters');
    }

    /**
     * @see ClassMetadata::addPropertyMetadata()
     * @param string $property
     * @return string
     */
    private function addProperty($property)
    {
        $property = $this->properties[$property]->getPropertyName();
        $this->members[$property][] = $property;
        return $property;
    }
}
