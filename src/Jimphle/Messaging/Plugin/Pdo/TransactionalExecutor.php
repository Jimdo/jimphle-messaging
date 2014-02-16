<?php
namespace Jimphle\Messaging\Plugin\Pdo;

class TransactionalExecutor
{
    private $executable;
    private $connection;

    public function __construct(\Closure $executable, \PDO $connection)
    {
        $this->executable = $executable;
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     * @return \Jimphle\DataStructure\Map
     */
    public function execute()
    {
        $this->connection->beginTransaction();
        try {
            $executableReflection = new \ReflectionFunction($this->executable);
            $response = $executableReflection->invokeArgs(func_get_args());
            $this->connection->commit();
            return $response;
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }
}
