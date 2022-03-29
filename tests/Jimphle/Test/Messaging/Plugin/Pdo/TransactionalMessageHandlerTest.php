<?php
namespace Jimphle\Test\Messaging\Plugin\Pdo;

use Mockery as m;
use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\Plugin\Pdo\TransactionalMessageHandler;
use PHPUnit\Framework\TestCase;

class TransactionalMessageHandlerTest extends TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $metaDataProvider;

    /**
     * @var \Mockery\MockInterface
     */
    private $transactionalExecutor;

    /**
     * @var \Mockery\MockInterface
     */
    private $nextHandler;

    protected function tearDown(): void
    {
        m::close();
    }

    protected function setUp(): void
    {
        $this->metaDataProvider = m::mock('Jimphle\Messaging\MessageHandlerMetadataProvider');
        $this->transactionalExecutor = m::mock('\Jimphle\Messaging\Plugin\Pdo\TransactionalExecutor');
        $this->nextHandler = m::mock('\Jimphle\Messaging\MessageHandler\MessageHandler');
    }

    /**
     * @test
     */
    public function itShouldExecuteTheNextHandlerInATransaction()
    {
        $message = $this->message();

        $this->metaDataProvider->shouldReceive('get')
            ->once()
            ->with($message, TransactionalMessageHandler::ANNOTATION_CLASS)
            ->andReturn(array(new \StdClass()));

        $this->transactionalExecutor->shouldReceive('execute')
            ->once()
            ->andReturnUsing(
                function ($executable) {
                    return $executable();
                }
            );

        $this->nextHandler->shouldReceive('handle')
            ->once()
            ->with(m::mustBe($message))
            ->andReturn($message);

        $this->assertThat(
            $this->transactionalHandler()->handle($message),
            $this->equalTo($message)
        );
    }

    /**
     * @test
     */
    public function itShouldExecuteTheNextHandlerWithoutATransaction()
    {
        $message = $this->message();

        $this->metaDataProvider->shouldReceive('get')
            ->once()
            ->with($message, TransactionalMessageHandler::ANNOTATION_CLASS)
            ->andReturn(array());

        $this->transactionalExecutor->shouldReceive('execute')
            ->never();

        $this->nextHandler->shouldReceive('handle')
            ->once()
            ->with(m::mustBe($message))
            ->andReturn($message);

        $this->assertThat(
            $this->transactionalHandler()->handle($message),
            $this->equalTo($message)
        );
    }

    private function message()
    {
        return GenericMessage::generateDummy();
    }

    private function transactionalHandler()
    {
        $handler = new TransactionalMessageHandler(
            $this->metaDataProvider,
            $this->transactionalExecutor,
            $this->nextHandler
        );
        return $handler;
    }
}
