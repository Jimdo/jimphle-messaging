<?php
namespace Jimphle\Test\Messaging\Plugin\Pdo;

use Mockery as m;
use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\Plugin\Pdo\TransactionalMessageHandler;

class TransactionalMessageHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function itShouldExecuteTheNextHandlerInATransaction()
    {
        $message = $this->message();

        $transactionalExecutor = m::mock('\Jimphle\Messaging\Plugin\Pdo\TransactionalExecutor');
        $transactionalExecutor->shouldReceive('execute')
            ->once()
            ->andReturnUsing(
                function ($executable) {
                    return $executable();
                }
            );

        $nextHandler = m::mock('\Jimphle\Messaging\MessageHandler\MessageHandler');
        $nextHandler->shouldReceive('handle')
            ->once()
            ->with(m::mustBe($message))
            ->andReturn($message);

        $handler = new TransactionalMessageHandler($transactionalExecutor, $nextHandler);
        $this->assertThat(
            $handler->handle($message),
            $this->equalTo($message)
        );
    }

    private function message()
    {
        return GenericMessage::generateDummy();
    }
}
