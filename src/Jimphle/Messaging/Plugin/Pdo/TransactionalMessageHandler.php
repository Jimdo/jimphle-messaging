<?php
namespace Jimphle\Messaging\Plugin\Pdo;

use Jimphle\Messaging\Plugin\Pdo\TransactionalExecutor;
use Jimphle\Messaging\Message;
use Jimphle\Messaging\MessageHandler\MessageHandler;

class TransactionalMessageHandler implements MessageHandler
{
    private $transactionalExecutor;
    private $next;

    public function __construct(TransactionalExecutor $transactionalExecutor, MessageHandler $next)
    {
        $this->transactionalExecutor = $transactionalExecutor;
        $this->next = $next;
    }

    public function handle(Message $message)
    {
        $next = $this->next;
        return $this->transactionalExecutor->execute(
            function () use ($next, $message) {
                return $next->handle($message);
            }
        );
    }
}
