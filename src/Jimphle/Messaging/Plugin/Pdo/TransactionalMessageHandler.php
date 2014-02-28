<?php
namespace Jimphle\Messaging\Plugin\Pdo;

use Jimphle\Messaging\MessageHandlerMetadataProvider;
use Jimphle\Messaging\Plugin\Pdo\TransactionalExecutor;
use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandler\MessageHandler;

class TransactionalMessageHandler implements MessageHandler
{
    const ANNOTATION_CLASS = 'Jimphle\Messaging\Plugin\Pdo\TransactionalAnnotation';

    private $metaDataProvider;
    private $transactionalExecutor;
    private $next;

    public function __construct(
        MessageHandlerMetadataProvider $metaDataProvider,
        TransactionalExecutor $transactionalExecutor,
        MessageHandler $next
    ) {
        $this->metaDataProvider = $metaDataProvider;
        $this->transactionalExecutor = $transactionalExecutor;
        $this->next = $next;
    }

    public function handle(Message $message)
    {
        $annotations = $this->metaDataProvider->get($message, self::ANNOTATION_CLASS);
        $next = $this->next;

        if (empty($annotations)) {
            return $next->handle($message);
        }

        return $this->transactionalExecutor->execute(
            function () use ($next, $message) {
                return $next->handle($message);
            }
        );
    }
}
