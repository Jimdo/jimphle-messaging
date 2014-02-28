<?php
namespace Jimphle\Messaging\Plugin\Authorization;

use Jimphle\Messaging\Filter;
use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandlerMetadataProvider;

class MessageFilter implements Filter
{
    const ANNOTATION_CLASS = 'Jimphle\Messaging\Plugin\Authorization\ConstraintAnnotation';

    private $metadataProvider;

    private $serviceContainer;
    private $authorizationContext;

    public function __construct(
        MessageHandlerMetadataProvider $metadataProvider,
        \ArrayAccess $serviceContainer,
        Context $authorizationContext
    ) {
        $this->metadataProvider = $metadataProvider;
        $this->serviceContainer = $serviceContainer;
        $this->authorizationContext = $authorizationContext;
    }


    public function filter(Message $message)
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
