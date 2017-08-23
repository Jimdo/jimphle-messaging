<?php
namespace Jimphle\Messaging;

use Jimphle\DataStructure\BaseInterface;
use Jimphle\DataStructure\Map;
use Jimphle\DataStructure\Nullable;
use Jimphle\DataStructure\Vector;

class MessageHandlerResponse implements Message
{
    /**
     * @var BaseInterface
     */
    private $data;

    /**
     * @var Message[]
     */
    private $messagesToProcessDirectly = array();

    /**
     * @var Message[]
     */
    private $messagesToProcessInBackground = array();

    public static function fromMap(array $payload)
    {
        return new static(Map::fromArray($payload));
    }

    public static function withoutPayload()
    {
        return new static(new Nullable());
    }

    public static function fromVector(array $payload)
    {
        return new static(Vector::fromArray($payload));
    }

    public function __construct(BaseInterface $data)
    {
        $this->data = $data;
    }

    public function addMessageToProcessDirectly(Message $message)
    {
        $this->messagesToProcessDirectly[] = $message;
        return $this;
    }

    public function getMessagesToProcessDirectly()
    {
        return $this->messagesToProcessDirectly;
    }

    public function addMessageToProcessInBackground(Message $message)
    {
        $this->messagesToProcessInBackground[] = $message;
        return $this;
    }

    public function getMessagesToProcessInBackground()
    {
        return $this->messagesToProcessInBackground;
    }

    /**
     * @param MessageHandlerResponse $other
     * @return MessageHandlerResponse
     */
    public function mergeMessageToProcess(MessageHandlerResponse $other)
    {
        $response = new static($this->data);
        $response->messagesToProcessDirectly = array_merge(
            $this->messagesToProcessDirectly,
            $other->messagesToProcessDirectly
        );
        $response->messagesToProcessInBackground = array_merge(
            $this->messagesToProcessInBackground,
            $other->messagesToProcessInBackground
        );
        return $response;
    }

    public function getIterator()
    {
        return $this->data->getIterator();
    }

    public function offsetExists($offset)
    {
        return $this->data->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->data->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->data->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->data->offsetUnset($offset);
    }

    public function toJson()
    {
        return $this->data->toJson();
    }

    public function toArray()
    {
        return $this->data->toArray();
    }

    public function __toString()
    {
        return $this->data->__toString();
    }

    public function __get($name)
    {
        return $this->data->__get($name);
    }

    public function __isset($name)
    {
        return $this->data->__isset($name);
    }

    public function merge(BaseInterface $other)
    {
        return new static($this->data->merge($other));
    }

    public function getPayload()
    {
        return $this->data->getPayload();
    }

    public function getMessageName()
    {
    }

    public function getMessageType()
    {
    }

    public function getMessagePriority()
    {
    }

    public function getMessageChannel()
    {
    }

    public function count()
    {
        return $this->data->count();
    }

    public function toJimphleDataStructure()
    {
        return $this->data;
    }

    public function getMessageCreatedAt()
    {
    }

    public function equals(Message $someMessage)
    {
        throw new \BadMethodCallException('not implemented yet');
    }
}
