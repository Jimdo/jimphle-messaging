<?php
namespace Jimphle\Messaging;

use Jimphle\DataStructure\BaseInterface;
use Jimphle\DataStructure\Map;
use Jimphle\Messaging\Command;
use Jimphle\Messaging\Event;

class GenericMessage implements Message
{
    private $createdAt;
    private $name;
    private $payload;
    private $channel;
    private $priority;

    protected function __construct($createdAt, $name, Map $payload, $channel = null, $priority = null)
    {
        $this->createdAt = $createdAt;
        $this->name = $name;
        $this->payload = $payload;
        $this->channel = $channel;
        $this->priority = $priority;
    }

    /**
     * @param array $payload
     * @return Message
     */
    public static function generateDummy(array $payload = array())
    {
        return new static('2014-06-10 10:58:57', 'dummy', new Map($payload));
    }

    /**
     * @param string $name
     * @param array $payload
     * @param null|string $channel
     * @param null|int $priority
     * @return Message
     */
    public static function generate($name, array $payload = array(), $channel = null, $priority = null)
    {
        $createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        return new static($createdAt->format('Y-m-d H:i:s'), $name, new Map($payload), $channel, $priority);
    }

    public static function fromJson($json)
    {
        $data = json_decode($json, true);
        $type = null;
        if (isset($data['type'])) {
            $type = $data['type'];
        }
        $createdAt = null;
        if (isset($data['createdAt'])) {
            $createdAt = $data['createdAt'];
        }
        $channel = null;
        if (isset($data['channel'])) {
            $channel = $data['channel'];
        }
        $priority = null;
        if (isset($data['priority'])) {
            $priority = $data['priority'];
        }
        switch($type) {
            case self::TYPE_EVENT:
                return new Event($createdAt, $data['name'], new Map($data['payload']), $channel, $priority);
            default:
                return new Command($createdAt, $data['name'], new Map($data['payload']), $channel, $priority);
        }
    }

    public function __get($name)
    {
        return $this->payload->__get($name);
    }

    public function __isset($name)
    {
        return $this->payload->__isset($name);
    }

    /**
     * @return string
     */
    public function getMessageName()
    {
        return $this->name;
    }

    /**
     * @param BaseInterface $other
     * @return BaseInterface
     */
    public function merge(BaseInterface $other)
    {
        return new static($this->getMessageCreatedAt(), $this->getMessageName(), $this->payload->merge($other));
    }

    public function toArray()
    {
        $data = array(
            'type' => $this->getMessageType(),
            'createdAt' => $this->getMessageCreatedAt(),
            'name' => $this->getMessageName(),
            'payload' => $this->payload->toArray(),
            'channel' => $this->getMessageChannel(),
            'priority' => $this->getMessagePriority(),
        );
        return $data;
    }

    public function count()
    {
        return $this->payload->count();
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function getPayload()
    {
        return $this->payload->getPayload();
    }

    /**
     * @return string
     */
    public function getMessageType()
    {
    }

    /**
     * @return null|int
     */
    public function getMessagePriority()
    {
        return $this->priority;
    }

    /**
     * @return null|string
     */
    public function getMessageChannel()
    {
        return $this->channel;
    }

    public function getIterator()
    {
        return $this->payload->getIterator();
    }

    public function offsetExists($offset)
    {
        return $this->payload->__isset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->payload->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->payload->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->payload->__unset($offset);
    }

    public function getMessageCreatedAt()
    {
        return $this->createdAt;
    }

    public function equals(Message $someMessage)
    {
        return $this->getMessageType() == $someMessage->getMessageType()
            && $this->getMessageName() == $someMessage->getMessageName()
            && $this->getPayload() == $someMessage->getPayload()
            && $this->getMessageChannel() == $someMessage->getMessageChannel()
            && $this->getMessagePriority() == $someMessage->getMessagePriority();
    }
}
