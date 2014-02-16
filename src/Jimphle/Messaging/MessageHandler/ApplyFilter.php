<?php
namespace Jimphle\Messaging\MessageHandler;

class ApplyFilter implements \Jimphle\Messaging\MessageHandler\MessageHandler
{
    /**
     * @var \Jimphle\Messaging\Filter[]
     */
    private $filters;

    /**
     * @var \Jimphle\Messaging\MessageHandler\MessageHandler
     */
    private $next;

    public function __construct(array $filters, \Jimphle\Messaging\MessageHandler\MessageHandler $next)
    {
        $this->filters = $filters;
        $this->next = $next;
    }

    /**
     * @param \Jimphle\Messaging\Message|\Jimphle\DataStructure\Map $message
     * @return \Jimphle\Messaging\MessageHandlerResponse|\Jimphle\Messaging\Message|\Jimphle\DataStructure\Map|null
     */
    public function handle(\Jimphle\Messaging\Message $message)
    {
        foreach ($this->filters as $filter) {
            $message = $filter->filter($message);
        }
        return $this->next->handle($message);
    }
}
