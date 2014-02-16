<?php
namespace Jimphle\Messaging\Plugin\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\Collection;

class MessageMetadata extends ClassMetadata
{
    public function addPropertyConstraint($property, Constraint $constraint)
    {
        if (!isset($this->properties[$property])) {
            $this->properties[$property] = new \Jimphle\Messaging\Plugin\Validation\MessagePropertyMetadata(
                $this->getClassName(),
                $property
            );

            $this->addMemberMetadata($this->properties[$property]);
        }

        $constraint->addImplicitGroupName($this->getDefaultGroup());

        $this->properties[$property]->addConstraint($constraint);

        return $this;
    }

    public function addGetterConstraint($property, Constraint $constraint)
    {
        throw new \BadMethodCallException('message does not support getters');
    }
}
