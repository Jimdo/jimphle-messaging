<?php
namespace Jimphle\Messaging\MessageHandler;

use Jimphle\Messaging\Message;

class ApplyFilter implements MessageHandler
{
    /**
     * @var \Jimphle\Messaging\Filter[]
     */
    private $filters;

    /**
     * @var MessageHandler
     */
    private $next;

    public function __construct(array $filters, MessageHandler $next)
    {
        $this->filters = $filters;
        $this->next = $next;
    }

    /**
     * @param Message|\Jimphle\DataStructure\Map $message
     * @return \Jimphle\Messaging\MessageHandlerResponse|Message|\Jimphle\DataStructure\Map|null
     */
    public function handle(Message $message)
    {
        foreach ($this->filters as $filter) {
            $message = $filter->filter($message);
        }
        return $this->next->handle($message);
    }
}
