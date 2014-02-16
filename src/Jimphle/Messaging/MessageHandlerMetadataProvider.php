<?php
namespace Jimphle\Messaging;

use Doctrine\Common\Annotations\Reader;

class MessageHandlerMetadataProvider
{
    /**
     * @var array
     */
    private $allowedAnnotationClasses;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var \Jimphle\Messaging\MessageHandler\MessageHandlerProvider
     */
    private $messageHandlerProvider;

    public function __construct(
        Reader $annotationReader,
        \Jimphle\Messaging\MessageHandlerProvider $messageHandlerProvider,
        array $allowedAnnotationClasses
    ) {
        $this->annotationReader = $annotationReader;
        $this->messageHandlerProvider = $messageHandlerProvider;
        $this->allowedAnnotationClasses = $allowedAnnotationClasses;
    }

    public function get(\Jimphle\Messaging\Message $message, $annotationClass)
    {
        $this->assertAnnotationClassAllowed($annotationClass);

        $messageHandlers = $this->messageHandlerProvider->getMessageHandlers($message);
        $annotations = array();
        foreach ($messageHandlers as $messageHandler) {
            $annotations = array_merge(
                $annotations,
                $this->annotationReader->getClassAnnotations(
                    new \ReflectionClass($messageHandler)
                )
            );
        }
        $matchingAnnotations = array();
        foreach ($annotations as $annotation) {
            $this->assertAnnotationClassAllowed(get_class($annotation));

            if ($annotation instanceof $annotationClass) {
                $matchingAnnotations[] = $annotation;
            }
        }
        return $matchingAnnotations;
    }

    private function assertAnnotationClassAllowed($annotationClass)
    {
        if (!in_array($annotationClass, $this->allowedAnnotationClasses)) {
            throw new \RuntimeException(
                sprintf("class '%s' is not in the allowed annotations for message handlers", $annotationClass)
            );
        }
    }
}
