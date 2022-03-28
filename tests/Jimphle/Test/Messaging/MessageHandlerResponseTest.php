<?php
namespace Jimphle\Test\Messaging;

use Jimphle\Messaging\GenericMessage;
use Jimphle\Messaging\MessageHandlerResponse;
use PHPUnit\Framework\TestCase;

class MessageHandlerResponseTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldMergeEventsToProcess()
    {
        $response = $this->someResponse();
        $response->addMessageToProcessDirectly($this->someMessage());
        $response->addMessageToProcessInBackground($this->someMessage());

        $responseToMerge = MessageHandlerResponse::fromMap(array('foo' => 'bar'));
        $responseToMerge->addMessageToProcessDirectly($this->someMessage());

        $expectedResponse = $this->someResponse();
        $expectedResponse->addMessageToProcessDirectly($this->someMessage());
        $expectedResponse->addMessageToProcessDirectly($this->someMessage());
        $expectedResponse->addMessageToProcessInBackground($this->someMessage());
        $this->assertThat(
            $response->mergeMessageToProcess($responseToMerge),
            $this->equalTo($expectedResponse)
        );
    }

    private function someResponse()
    {
        return MessageHandlerResponse::fromMap(array('blub' => 'bla'));
    }

    private function someMessage()
    {
        $event = GenericMessage::generateDummy();
        return $event;
    }
}
