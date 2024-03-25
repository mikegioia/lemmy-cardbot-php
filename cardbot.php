<?php

/**
 * Main daemon script which runs and interacts wit the CardBot.
 *  - Reads the credentials from the environment file
 *  - Sets up signal handling for exiting
 *  - Kicks off loop to run forever
 * The bot logs all actions to daily log files, and stores every post
 * or comment action in SQL database.
 */

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use Lemmy\CardBot;
use Rikudou\LemmyApi\DefaultLemmyApi;
use Rikudou\LemmyApi\Enum\LemmyApiVersion;

// For signal handling
declare(ticks=1);

// Load vendor libraries, check if autoload exists
if (! file_exists(__DIR__.'/vendor/autoload.php')) {
    echo 'Composer autoload file does not exist!', PHP_EOL;
    echo 'Please run `composer install` first.', PHP_EOL;

    exit(1);
}

require __DIR__.'/vendor/autoload.php';

// Connection to Lemmy API
$api = new DefaultLemmyApi(
    instanceUrl: 'https://stage.mtgzone.com',
    version: LemmyApiVersion::Version3,
    httpClient: new HttpClient(),
    requestFactory: new HttpFactory(),
);

// Load credentials from environment
$cardbot = new CardBot(
    api: $api,
    sleepFor: 5, // 5 seconds
    username: 'cardbot',
    password: 'password'
);

$stopCardBot = function (int $signo) use ($cardbot) {
    $cardbot->stop();
};

// Set up signal handling to exit
pcntl_signal(SIGHUP, $stopCardBot);
pcntl_signal(SIGINT, $stopCardBot);
pcntl_signal(SIGQUIT, $stopCardBot);
pcntl_signal(SIGTERM, $stopCardBot);

// Kick off the loop
$cardbot->start();
