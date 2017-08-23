<?php

namespace Jimphle\Messaging\Plugin\Validation;

use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandlerMetadataProvider;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\MetadataInterface;

class MessageHandlerMetadataFactory implements MetadataFactoryInterface
{
    const MESSAGE_PROPERTY_ANNOTATION_CLASS = 'Jimphle\Messaging\Plugin\Validation\MessagePropertyAnnotation';
    const MESSAGE_ANNOTATION_CLASS = 'Jimphle\Messaging\Plugin\Validation\MessageAnnotation';
    const MESSAGE_CLASS = '\Jimphle\Messaging\Command';

    private $metadataProvider;
    private $serviceContainer;

    public function __construct(
        MessageHandlerMetadataProvider $metadataProvider,
        \ArrayAccess $serviceContainer
    )
    {
        $this->metadataProvider = $metadataProvider;
        $this->serviceContainer = $serviceContainer;
    }


    /**
     * Returns the metadata for the given value.
     *
     * @param mixed $value Some value.
     *
     * @throws \Jimphle\Exception\RuntimeException
     * @return MetadataInterface The metadata for the value.
     */
    public function getMetadataFor($value)
    {
        $messageMetadata = new MessageMetadata(self::MESSAGE_CLASS);

        if ($value instanceof Message) {
            $this->addPropertyConstraintsFromMessagePropertyAnnotations($value, $messageMetadata);
            $this->addPropertyConstraintsFromMessageAnnotations($value, $messageMetadata);
        }

        return $messageMetadata;
    }

    /**
     * Returns whether metadata exists for the given value.
     *
     * @param mixed $value Some value.
     *
     * @return Boolean Whether metadata exists for the value.
     */
    public function hasMetadataFor($value)
    {
        return true;
    }

    private function addPropertyConstraintsFromMessagePropertyAnnotations(
        Message $value,
        MessageMetadata $messageMetadata
    )
    {
        $annotations = $this->metadataProvider->get($value, self::MESSAGE_PROPERTY_ANNOTATION_CLASS);
        foreach ($annotations as $annotation) {
            $messageMetadata->addPropertyConstraint($annotation->prop, $annotation->constraint);
        }
    }

    private function addPropertyConstraintsFromMessageAnnotations(
        Message $value,
        MessageMetadata $messageMetadata
    )
    {
        $annotations = $this->metadataProvider->get($value, self::MESSAGE_ANNOTATION_CLASS);
        foreach ($annotations as $annotation) {
            $constraintDefinitions = $this->serviceContainer->offsetGet($annotation->name);
            foreach ($constraintDefinitions as $constraintDefinition) {
                $messageMetadata->addPropertyConstraint($constraintDefinition[0], $constraintDefinition[1]);
            }
        }
    }
}
