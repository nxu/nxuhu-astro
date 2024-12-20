#!/usr/bin/env php
<?php

use Dotenv\Dotenv;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use JsonMachine\Items;


// Composer autoloader
require_once(__DIR__ . '/vendor/autoload.php');

// Initialize dotenv config
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Parse input arguments
$opts = getopt('', ['since::']);

if (array_key_exists('since', $opts)) {
    $since = (DateTimeImmutable::createFromFormat('Y-m-d', $opts['since']))->setTime(0, 0, 0);

    if ($since === false) {
        fwrite(STDERR, "[Error] Invalid date provided for --since argument: " . $opts['since'] . PHP_EOL);
        exit(1);
    }
} else {
    $since = (new DateTimeImmutable())
        ->setTime(0, 0, 0)
        ->sub(DateInterval::createFromDateString('2 weeks'));
}

echo "Processing entries after " . $since->format('Y-m-d') . PHP_EOL;

$since = $since->getTimestamp();

chdir(__DIR__ .'/../');

// Load Block Keeper json data file
$puzzles = Items::fromFile('/Users/nxu/Library/Application Support/Block Keeper/storage/puzzles.json', [
    'decoder' => new ExtJsonDecoder(true),
]);

// Writes records as insert statement into the file and flushes the buffer
function flushWriteStatements($handle, &$buffer) {
    fwrite($handle, 'INSERT OR IGNORE INTO "records" ("session", "time", "ts", "result") VALUES ');

    foreach ($buffer as $index => $record) {
        fwrite($handle, sprintf(
            "('%s', %s, %s, '%s')",
            $record['session'],
            $record['time'],
            $record['ts'],
            $record['result']
        ));

        if (($index != (count($buffer) - 1))) {
            fwrite($handle, ', ');
        }
    }

    fwrite($handle, ';');

    $buffer = [];
}

// Create insert statements
$sqlFile = tempnam('/tmp', 'cube');
$handle = fopen($sqlFile, 'rw+');

$buffer = [];

foreach ($puzzles as $types) {
    if (! is_array($types)) {
        continue;
    }

    foreach ($types as $type) {
        if ($type['name'] !== '3x3x3') {
            continue;
        }

        foreach ($type['sessions'] as $session) {
            foreach ($session['records'] as $record) {
                if ($record['date'] < $since) {
                    continue;
                }

                $buffer[] = [
                    'session' => $session['name'],
                    'time' => $record['time'],
                    'ts' => $record['date'],
                    'result' => $record['result'],
                ];

                if (count($buffer) >= 300) {
                    flushWriteStatements($handle, $buffer);
                }
            }
        }
    }
}

// Flush remaining items in the buffer
flushWriteStatements($handle, $buffer);
fwrite($handle, "\n");
fclose($handle);

// Execute SQL
// wrangler d1 execute --local cube --file $path
shell_exec("wrangler d1 execute --local cube-live --file $sqlFile");

// Use CLOUDFLARE_API_TOKEN from .env
$cfApiToken = getenv('CLOUDFLARE_API_TOKEN');
shell_exec("export CLOUDFLARE_API_TOKEN=$cfApiToken && wrangler d1 execute cube-live --remote --file $sqlFile");
