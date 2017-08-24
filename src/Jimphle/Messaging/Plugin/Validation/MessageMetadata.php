<?php
namespace Jimphle\Messaging\Plugin\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\PropertyMetadataInterface;

class MessageMetadata extends ClassMetadata
{
    public function addPropertyConstraint($property, Constraint $constraint)
    {
        if (!isset($this->properties[$property])) {
            $this->properties[$property] = new MessagePropertyMetadata(
                $this->getClassName(),
                $property
            );

            $this->addPropertyMetadata($this->properties[$property]);
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
     * Adds a property metadata.
     *
     * @param PropertyMetadataInterface $metadata
     */
    private function addPropertyMetadata(PropertyMetadataInterface $metadata)
    {
        $property = $metadata->getPropertyName();
        $this->members[$property][] = $metadata;
    }
}
