# Jimphle-messaging

[![Build Status](https://travis-ci.org/Jimdo/jimphle-messaging.png?branch=master)](https://travis-ci.org/Jimdo/jimphle-messaging)


Jimdo PHP library extraction of messaging component.

The Messaging component is base on message-handler which implement the MessageHandler Interface.

## Handling commands and events

Let's get started with sending a command say-hello:
```php
use Jimphle\Example\MessageHandler\SayHelloHandler;

use Jimphle\Messaging\MessageHandler\HandleMessage;
use Jimphle\Messaging\MessageHandlerProvider;
use Jimphle\Messaging\Command;

$messageHandlerDefinitions = array(
    'say-hello' => new SayHelloHandler(),
);
$messagingContext = new HandleMessage(
    new MessageHandlerProvider(
        new ArrayObject(
            $messageHandlerDefinitions
        )
    )
);

$response = $messagingContext->handle(
    Command::generate(
        'say-hello',
        array(
            'name' => 'World'
        )
    )
);
echo $response->answer;

# => Hello World!
```
Ok what happened here? We passed the Command say-hello and it's payload to the messagingContext.
The messagingContext did a lookup for A MessageHandler registered to say-hello.

Something more interesting. If you had a look at the SayHelloHandler you would have seen that the handler itself
generated a new Event which is returned with the MessageResponse "to-be-processed-directly" by the calling context.
To make our messagingContext to be able to handle this kind of messages we have to add another kind of handler.
```php
use Jimphle\Example\MessageHandler\BeObsceneHandler;
use Jimphle\Example\MessageHandler\SayHelloHandler;

use Jimphle\Messaging\MessageHandler\HandleAllMessagesToProcess;
use Jimphle\Messaging\MessageHandler\HandleMessage;
use Jimphle\Messaging\MessageHandlerProvider;
use Jimphle\Messaging\Command;

$messageHandlerDefinitions = array(
    'say-hello' => new SayHelloHandler(),
    'said-hello' => array(
        new BeObsceneHandler()
    )
);
$messagingContext = new HandleAllMessagesToProcess(
    new HandleMessage(
        new MessageHandlerProvider(
            new ArrayObject(
                $messageHandlerDefinitions
            )
        )
    ),
    new \Jimphle\Messaging\MessageHandler\NullHandler()
);

$response = $messagingContext->handle(
    Command::generate(
        'say-hello',
        array(
            'name' => 'World'
        )
    )
);
echo $response->answer . "\n";

# => GTFO Joscha!
# => Hello World!
```
As you can see Our said-hello Event is handled by the messagingContext now.
Which means the messagingContext handles the SayHelloHandler iterates over the returned "to-be-processed-directly"
events and tries to handle them as well.
How you may have noticed the order of the printed messages is reversed to the order the printing handlers are called.
This happens because the response of the first called message-handler is the one which is returned here.

## Message-filter and message-handler annotations

If you add the ApplyFilter MessageHandler to the messagingContext you add the possibility to apply filter to a message
before passing it to the next message-handler.
```php
$messageFilterDefinitions = array(new SomeMessageFilter());
$messagingContext = new HandleAllMessagesToProcess(
    new ApplyFilter(
        $messageFilterDefinitions,
        new HandleMessage(
            new MessageHandlerProvider(
                new ArrayObject(
                    $messageHandlerDefinitions
                )
            )
        )
    ),
    new \Jimphle\Messaging\MessageHandler\NullHandler()
);
```

However there are two predefined filter we can use here:
 * The Validation filter, based on Symfony-Validation-Component
 * The Authorization filter explained here another day

## Handling messages in a PDO transaction

To handle the messages in a PDO transaction we can add the TransactionalMessageHandler to the messagingContext.
```php
$messagingContext = new HandleAllMessagesToProcess(
    new TransactionalMessageHandler(
        new PDO('some-dsn'),
        new ApplyFilter(
            $messageFilterDefinitions,
            new HandleMessage(
                new MessageHandlerProvider(
                    new ArrayObject(
                        $messageHandlerDefinitions
                    )
                )
            )
        )
    ),
    new \Jimphle\Messaging\MessageHandler\NullHandler()
);
```
