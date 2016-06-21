<?php
$autoLoader = require __DIR__ . '/vendor/autoload.php';
$autoLoader->add('Jimphle\\Example\\', __DIR__ . '/tests');

use Jimphle\Example\MessageHandler\BeObsceneHandler;
use Jimphle\Example\MessageHandler\SayHelloHandler;

use Jimphle\Messaging\MessageHandler\HandleMessagesToProcessDirectly;
use Jimphle\Messaging\MessageHandler\HandleMessage;
use Jimphle\Messaging\MessageHandlerProvider;
use Jimphle\Messaging\Command;

$messageHandlerDefinitions = array(
    'say-hello' => new SayHelloHandler(),
    'said-hello' => array(
        new BeObsceneHandler()
    )
);
$messagingContext = new HandleMessagesToProcessDirectly(
    new HandleMessage(
        new MessageHandlerProvider(
            new ArrayObject(
                $messageHandlerDefinitions
            )
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
echo $response->answer . "\n";