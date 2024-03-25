<?php

/**
 * Main daemon script which runs and interacts wit the CardBot.
 *  - Reads the credentials from the environment file
 *  - Sets up signal handling for exiting
 *  - Kicks off loop to run forever
 * The bot logs all actions to daily log files, and stores every post
 * or comment action in SQL database.
 */

use Dotenv\Dotenv;
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

// Load environment variables
if (! Dotenv::createImmutable(__DIR__)->safeLoad()) {
    echo 'Missing .env file!', PHP_EOL;
    echo 'Please copy .env.example to .env and update it with your data', PHP_EOL;

    exit(1);
}

// Create log directory and logger
mkdir(__DIR__.'/log', 0755, true);

// Load credentials from environment
$cardbot = new CardBot(
    api: new DefaultLemmyApi(
        instanceUrl: $_ENV['INSTANCE_URL'] ?? '',
        version: LemmyApiVersion::Version3,
        httpClient: new HttpClient(),
        requestFactory: new HttpFactory()
    ),
    logPath: __DIR__.'/log',
    sleepFor: intval($_ENV['SLEEP_SECONDS'] ?? 5),
    username: $_ENV['USERNAME'] ?? '',
    password: $_ENV['PASSWORD'] ?? ''
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
