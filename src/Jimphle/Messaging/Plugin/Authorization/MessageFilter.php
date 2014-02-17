<?php
namespace Jimphle\Messaging\Plugin\Authorization;

class MessageFilter implements \Jimphle\Messaging\Filter
{
    const ANNOTATION_CLASS = 'Jimphle\Messaging\Plugin\Authorization\ConstraintAnnotation';

    private $metadataProvider;

    private $serviceContainer;
    private $authorizationContext;

    public function __construct(
        \Jimphle\Messaging\MessageHandlerMetadataProvider $metadataProvider,
        \ArrayAccess $serviceContainer,
        \Jimphle\Messaging\Plugin\Authorization\Context $authorizationContext
    ) {
        $this->metadataProvider = $metadataProvider;
        $this->serviceContainer = $serviceContainer;
        $this->authorizationContext = $authorizationContext;
    }


    public function filter(\Jimphle\Messaging\Message $message)
    {
        $annotations = $this->metadataProvider->get($message, self::ANNOTATION_CLASS);
        $authConstraints = array();
        foreach ($annotations as $annotation) {
            $authConstraints[] = $this->serviceContainer->offsetGet($annotation->name);
        }
        $this->authorizationContext->assertAccessIsGranted($message, $authConstraints);

        return $message;
    }
}
