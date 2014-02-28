<?php
namespace Jimphle\Messaging\Plugin\Pdo;

class TransactionalExecutor
{
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param \Closure $executable
     * @return mixed
     * @throws \Exception
     */
    public function execute(\Closure $executable)
    {
        $this->connection->beginTransaction();
        try {
            $response = $executable();
            $this->connection->commit();
            return $response;
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }
}
