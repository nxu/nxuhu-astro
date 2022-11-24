#!/usr/bin/env php
<?php

use JsonMachine\JsonDecoder\ExtJsonDecoder;
use JsonMachine\Items;

// Composer autoloader
require_once(__DIR__ . '/vendor/autoload.php');
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
shell_exec("wrangler d1 execute --local cube --file $sqlFile");
shell_exec("wrangler d1 execute cube --file $sqlFile");