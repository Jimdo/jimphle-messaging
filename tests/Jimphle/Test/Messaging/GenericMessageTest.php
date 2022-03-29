<?php
namespace Jimphle\Test\Messaging;

use Jimphle\Messaging\Command;
use Jimphle\Messaging\Event;
use Jimphle\Messaging\GenericMessage;
use PHPUnit\Framework\TestCase;

class GenericMessageTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldGenerateACommandFromJsonAsDefault()
    {
        $message = GenericMessage::fromJson('{"name":"dummy","payload":{"some_prop":"some value"}}');
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
        $expectedMessageData['createdAt'] = $message->getMessageCreatedAt();
        $this->assertThat($message->toArray(), $this->equalTo($expectedMessageData));
    }

    /**
     * @test
     */
    public function itShouldGenerateACommandFromJson()
    {
        $message = GenericMessage::fromJson('{"type":"command","name":"dummy","payload":{"some_prop":"some value"}}');
        $this->assertThat($message, $this->isInstanceOf('\Jimphle\Messaging\Command'));
    }

    /**
     * @test
     */
    public function itShouldGenerateAnEventFromJson()
    {
        $message = GenericMessage::fromJson('{"type":"event","name":"dummy","payload":{"some_prop":"some value"}}');
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
        $message = GenericMessage::generateDummy(array('some_prop' => 'some value'));
        $this->assertThat($message->toJson(), $this->equalTo($this->aJsonMessage('null')));
    }

    /**
     * @test
     */
    public function itShouldConvertACommandToJson()
    {
        $message = Command::generateDummy(array('some_prop' => 'some value'));
        $this->assertThat($message->toJson(), $this->equalTo($this->aJsonMessage('"command"')));
    }

    /**
     * @test
     */
    public function itShouldConvertAnEventToJson()
    {
        $message = Event::generateDummy(array('some_prop' => 'some value'));
        $this->assertThat($message->toJson(), $this->equalTo($this->aJsonMessage('"event"')));
    }

    /**
     * @test
     */
    public function itShouldBeEqualEvenIfTheCreatedAtFieldVaries()
    {
        $this->assertThat(Command::generateDummy()->equals(Command::generate('dummy')), $this->isTrue());
    }

    /**
     * @test
     */
    public function itShouldBeNotEqualIfTheTypeVaries()
    {
        $this->assertThat(Event::generateDummy()->equals(Command::generateDummy()), $this->isFalse());
    }

    /**
     * @test
     */
    public function itShouldBeNotEqualIfTheNameVaries()
    {
        $this->assertThat(Command::generateDummy()->equals(Command::generate('another dummy')), $this->isFalse());
    }

    /**
     * @test
     */
    public function itShouldBeNotEqualIfThePayloadVaries()
    {
        $this->assertThat(Command::generateDummy(array('foo' => 'bar'))->equals(Command::generateDummy()), $this->isFalse());
    }

    /**
     * @test
     */
    public function itShouldBeNotEqualIfTheChannelVaries()
    {
        $this->assertThat(Command::generate('foo', array(), 'someChannel')->equals(Command::generate('foo')), $this->isFalse());
    }

    /**
     * @test
     */
    public function itShouldBeNotEqualIfThePriorityVaries()
    {
        $this->assertThat(Command::generate('foo', array(), null, 1)->equals(Command::generate('foo')), $this->isFalse());
    }

    private function aJsonMessage($type, $channel = 'null', $priority = 'null')
    {
        return '{"type":' . $type . ',"createdAt":"2014-06-10 10:58:57","name":"dummy","payload":{"some_prop":"some value"},"channel":' . $channel . ',"priority":' . $priority . '}';
    }
}
