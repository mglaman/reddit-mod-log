<?php

declare(strict_types=1);

use GuzzleHttp\Client;

require __DIR__.'/vendor/autoload.php';

$last_checked_mid = file_get_contents(__DIR__ . '/last_checked_mid');

$client = new Client([
    'headers' => [
        'Authorization' => 'Bearer ' . $_ENV['REDDIT_ACCESS_TOKEN']
    ],
]);

$url = sprintf(
    'https://reddit.com/r/mod/about/log/.json?feed=%s&u=%s',
    $_ENV['REDDIT_FEED_HASH'],
    $_ENV['REDDIT_ACCOUNT_NAME']
);

$res = $client->get($url);
$json = (string) $res->getBody();
$logs = json_decode($json);

// @todo need to find docs on output and it's schema shape
$logs = array_filter(
    $logs,
    fn (object $log) => $log->mid > $last_checked_mid
);

foreach ($logs as $log) {
    // @todo push in a better format, maybe a syslog type format?
    file_put_contents(
        __DIR__ . '/logs.text',
        json_encode($log) . PHP_EOL,
        FILE_APPEND
    );
}

// @todo use GitHub API to upload new logs.txt to repo
//    or can it be committed in the workflow with `gh`?
