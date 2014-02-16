<?php
namespace Jimphle\Test\Messaging;

use Jimphle\Messaging\Command;
use Jimphle\Messaging\Event;
use Jimphle\Messaging\GenericMessage;

class GenericMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldGenerateACommandFromJsonAsDefault()
    {
        $message = GenericMessage::fromJson('{"name":"test","payload":{"some_prop":"some value"}}');
        $this->assertThat($message, $this->isInstanceOf('\Jimphle\Messaging\Command'));
    }

    /**
     * @test
     */
    public function toArrayShouldReturnWholeMessageInformation()
    {
        $expectedMessageData = array(
            'name' => 'some_name',
            'type' => GenericMessage::TYPE_COMMAND,
            'payload' => array('foo' => 'bar'),
            'channel' => 'some_channel',
            'priority' => 999
        );
        $message = Command::generate(
            $expectedMessageData['name'],
            $expectedMessageData['payload'],
            $expectedMessageData['channel'],
            $expectedMessageData['priority']
        );
        $this->assertThat($message->toArray(), $this->equalTo($expectedMessageData));
    }

    /**
     * @test
     */
    public function itShouldGenerateACommandFromJson()
    {
        $message = GenericMessage::fromJson('{"type":"command","name":"test","payload":{"some_prop":"some value"}}');
        $this->assertThat($message, $this->isInstanceOf('\Jimphle\Messaging\Command'));
    }

    /**
     * @test
     */
    public function itShouldGenerateAnEventFromJson()
    {
        $message = GenericMessage::fromJson('{"type":"event","name":"test","payload":{"some_prop":"some value"}}');
        $this->assertThat($message, $this->isInstanceOf('\Jimphle\Messaging\Event'));
    }

    /**
     * @test
     */
    public function itShouldGenerateAnEventWithTheWholeInformationFromJson()
    {
        $json = $this->aJsonMessage('"event"', '"a_channel"', 999);
        $message = GenericMessage::fromJson($json);
        $this->assertThat($message->toJson(), $this->equalTo($json));
    }

    /**
     * @test
     */
    public function itShouldGenerateACommandWithTheWholeInformationFromJson()
    {
        $json = $this->aJsonMessage('"command"', '"a_channel"', 999);
        $message = GenericMessage::fromJson($json);
        $this->assertThat($message->toJson(), $this->equalTo($json));
    }

    /**
     * @test
     */
    public function itShouldConvertAMessageToJson()
    {
        $message = GenericMessage::generate('test', array('some_prop' => 'some value'));
        $this->assertThat($message->toJson(), $this->equalTo($this->aJsonMessage('null')));
    }

    /**
     * @test
     */
    public function itShouldConvertACommandToJson()
    {
        $message = Command::generate('test', array('some_prop' => 'some value'));
        $this->assertThat($message->toJson(), $this->equalTo($this->aJsonMessage('"command"')));
    }

    /**
     * @test
     */
    public function itShouldConvertAnEventToJson()
    {
        $message = Event::generate('test', array('some_prop' => 'some value'));
        $this->assertThat($message->toJson(), $this->equalTo($this->aJsonMessage('"event"')));
    }

    private function aJsonMessage($type, $channel = 'null', $priority = 'null')
    {
        return '{"type":' . $type . ',"name":"test","payload":{"some_prop":"some value"},"channel":' . $channel . ',"priority":' . $priority . '}';
    }
}
