<?php
namespace Jimphle\Messaging\Plugin\Pdo;

class TransactionalMessageHandler implements \Jimphle\Messaging\MessageHandler\MessageHandler
{
    private $pdo;
    private $next;

    public function __construct(\PDO $pdo, \Jimphle\Messaging\MessageHandler\MessageHandler $next)
    {
        $this->pdo = $pdo;
        $this->next = $next;
    }

    /**
     * @param \Jimphle\Messaging\Message|\Jimphle\DataStructure\Map $message
     * @return \Jimphle\Messaging\MessageHandlerResponse|\Jimphle\Messaging\Message|\Jimphle\DataStructure\Map|null
     */
    public function handle(\Jimphle\Messaging\Message $message)
    {
        $next = $this->next;
        return $this->executeTransactional(
            function (\Jimphle\Messaging\Message $message) use ($next) {
                return $next->handle($message);
            },
            $message
        );
    }

    private function executeTransactional(\Closure $closure, \Jimphle\Messaging\Message $message)
    {
        $executor = new \Jimphle\Messaging\Plugin\Pdo\TransactionalExecutor($closure, $this->pdo);
        return $executor->execute($message);
    }
}
