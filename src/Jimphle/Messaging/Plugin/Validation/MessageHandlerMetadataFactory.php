<?php
namespace Jimphle\Messaging\Plugin\Validation;

use Symfony\Component\Validator\Exception;
use Symfony\Component\Validator\MetadataFactoryInterface;
use Symfony\Component\Validator\MetadataInterface;

class MessageHandlerMetadataFactory implements MetadataFactoryInterface
{
    const MESSAGE_PROPERTY_ANNOTATION_CLASS = 'cc_Services_Messaging_Plugin_Validation_MessagePropertyAnnotation';
    const MESSAGE_ANNOTATION_CLASS = 'cc_Services_Messaging_Plugin_Validation_MessageAnnotation';
    const MESSAGE_CLASS = '\Jimphle\Messaging\Command';

    private $metadataProvider;
    private $serviceContainer;

    public function __construct(
        \Jimphle\Messaging\MessageHandlerMetadataProvider $metadataProvider,
        \ArrayAccess $serviceContainer
    ) {
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
        if (!$value instanceof \Jimphle\Messaging\Message) {
            throw new \Jimphle\Exception\RuntimeException(
                sprintf("Cannot get metadata for '%s'", var_export($value, true))
            );
        }

        $messageMetadata = new \Jimphle\Messaging\Plugin\Validation\MessageMetadata(self::MESSAGE_CLASS);

        $this->addPropertyConstraintsFromMessagePropertyAnnotations($value, $messageMetadata);
        $this->addPropertyConstraintsFromMessageAnnotations($value, $messageMetadata);

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
        \Jimphle\Messaging\Message $value,
        \Jimphle\Messaging\Plugin\Validation\MessageMetadata $messageMetadata
    ) {
        $annotations = $this->metadataProvider->get($value, self::MESSAGE_PROPERTY_ANNOTATION_CLASS);
        foreach ($annotations as $annotation) {
            $messageMetadata->addPropertyConstraint($annotation->prop, $annotation->constraint);
        }
    }

    private function addPropertyConstraintsFromMessageAnnotations(
        \Jimphle\Messaging\Message $value,
        \Jimphle\Messaging\Plugin\Validation\MessageMetadata $messageMetadata
    ) {
        $annotations = $this->metadataProvider->get($value, self::MESSAGE_ANNOTATION_CLASS);
        foreach ($annotations as $annotation) {
            $constraintDefinitions = $this->serviceContainer->offsetGet($annotation->name);
            foreach ($constraintDefinitions as $constraintDefinition) {
                $messageMetadata->addPropertyConstraint($constraintDefinition[0], $constraintDefinition[1]);
            }
        }
    }
}
