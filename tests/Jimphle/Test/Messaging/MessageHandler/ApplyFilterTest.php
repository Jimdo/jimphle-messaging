<?php
namespace Jimphle\Test\Messaging\MessageHandler;

use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\MessageHandler\ApplyFilter;
use PHPUnit\Framework\TestCase;

class ApplyFilterTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldApplyTheFilters()
    {
        $message = $this->message();
        $messageFilter = $this->messageFilterMock();
        $messageFilter->expects($this->exactly(2))
            ->method('filter')
            ->with($this->equalTo($message))
            ->will($this->returnValue($message));

        $applyFilter = new ApplyFilter(
            array(
                $messageFilter,
                $messageFilter,
            ),
            $this->messageHandlerMock()
        );
        $applyFilter->handle($message);
    }

    /**
     * @test
     */
    public function itShouldDelegateToTheNextHandler()
    {
        $message = $this->message();
        $messageFilter = $this->messageFilterMock();
        $messageFilter->expects($this->any())
            ->method('filter')
            ->will($this->returnValue($message));

        $nextHandler = $this->messageHandlerMock();
        $nextHandler->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($message))
            ->will($this->returnValue($message));

        $applyFilter = new ApplyFilter(
            array(
                $messageFilter,
            ),
            $nextHandler
        );
        $applyFilter->handle($message);
    }

    /**
     * @test
     */
    public function itShouldReturnTheFilteredMessage()
    {
        $message = $this->message();
        $messageFilter = $this->messageFilterMock();
        $messageFilter->expects($this->any())
            ->method('filter')
            ->will($this->returnValue($message));

        $nextHandler = $this->messageHandlerMock();
        $nextHandler->expects($this->any())
            ->method('handle')
            ->will($this->returnValue($message));

        $applyFilter = new ApplyFilter(
            array(
                $messageFilter,
            ),
            $nextHandler
        );
        $this->assertThat($message, $this->equalTo($applyFilter->handle($message)));
    }

    private function messageFilterMock()
    {
        return $this->createMock(\Jimphle\Messaging\Filter::class);
    }

    private function message()
    {
        return GenericMessage::generateDummy();
    }

    private function messageHandlerMock()
    {
        return $this->createMock(\Jimphle\Messaging\MessageHandler\MessageHandler::class);
    }
}
