<?php
require 'vendor/autoload.php';

use EdgeLaser\Game;
use EdgeLaser\LaserColor;

$loop = React\EventLoop\Factory::create();

$factory = new Datagram\Factory($loop);

$socket = $factory->createClient('127.0.0.1:4242')->then(function (Datagram\Socket $client) use ($loop) {

    $game = new Game($client);
    $game->setName('ReactGame');
    $game->setResolution(500);
    $game->setDefaultColor(LaserColor::LIME);

    $client->on('message', function($message, $serverAddress, $client) use (&$game) {

        $command = chr(unpack('C', substr($message,0,1))[1]);

        switch ($command) {
            case 'A':
                // Ack
                $game->id = unpack('C', substr($message,1))[1];
                echo 'received gameid "' . $game->id . '" from ' . $serverAddress. PHP_EOL;
                break;
            case 'G':
                // Go
                $game->state = Game::STATE_RUNNING;
                echo 'received "Go" from ' . $serverAddress. PHP_EOL;
                break;
            case 'I':
                // Input
                $key = unpack('v', substr($message,1))[1];
                echo 'received key "' . $key . '" from ' . $serverAddress. PHP_EOL;
                break;
            case 'S':
                // Stop
                $game->state = Game::STATE_PAUSED;
                echo 'received "Pause" from ' . $serverAddress. PHP_EOL;
                break;
            default:
                echo 'received "' . $message . '" from ' . $serverAddress. PHP_EOL;
                break;
        }
    });

    $client->on('error', function($error, $client) {
        echo 'error: ' . $error->getMessage() . PHP_EOL;
    });

    $client->on('close', function(Datagram\Socket $client) {
        echo "Bye !" . PHP_EOL;
    });

    // Register ourselves
    $client->send(pack('C', 0) . pack('Z*', 'H' . $game->name));

    $coeff = 0;
    // Main Game Loop
    // 20FPS = 0.05s per frame
    $loop->addPeriodicTimer(0.05, function() use ($client, &$game, &$coeff) {
        if ($game->isRunning()) {
            $coeff = $coeff > 499 ? 0 : $coeff+4;

            $game
                ->addLine(250, 0, $coeff, 250, LaserColor::CYAN)
                ->addLine(250, 500, $coeff, 250, LaserColor::CYAN)
                ->addCircle(250, 250, $coeff, LaserColor::FUCHSIA)
                ->addRectangle(10, 10, $coeff, $coeff);

            $client->send(pack('C', $game->id) . 'R'); // Refresh (Display the actual frame)
        }
    });

}, function($error) {
    echo 'ERROR: ' . $error->getMessage() . PHP_EOL;
});

$loop->run();
