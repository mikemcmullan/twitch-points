<?php

use fXmlRpc\Client;
use Supervisor\Supervisor;
use Supervisor\Connector\XmlRpc;
use GuzzleHttp\Client as GuzzleClient;
use Mtdowling\Supervisor\EventListener;
use Ivory\HttpAdapter\Guzzle6HttpAdapter;
use Mtdowling\Supervisor\EventNotification;
use fXmlRpc\Transport\HttpAdapterTransport;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

$client = new Client(
    $_ENV['SUPERVISOR_RPC'],
    new HttpAdapterTransport(
        new Guzzle6HttpAdapter(
            new GuzzleClient([
                'auth' => [$_ENV['SUPERVISOR_USERNAME'], $_ENV['SUPERVISOR_PASSWORD']]
            ])
        )
    )
);

$supervisor = new Supervisor(new XmlRpc($client));
$listener = new EventListener();
$guzzleClient = new GuzzleClient([
    'base_uri' => ($_ENV['APP_SECURE'] === 'true' ? 'https' : 'http') . '://' . $_ENV['API_DOMAIN'],
    'headers'  => [
        'Authorization' => 'Bearer ' . $_ENV['API_TOKEN']
    ],
    'timeout'  => 2.0
]);

$states = [];

$listener->listen(function (EventListener $listener, EventNotification $event) use($supervisor, $guzzleClient, &$states) {

    $processes = array_filter($supervisor->getAllProcessInfo(), function ($proc) {
        return preg_match('/twitch-bot-(?!worker)/', $proc['name']);
    });

    foreach ($processes as $proc) {
        if (! isset($states[$proc['name']])) {
            $states[$proc['name']] = '';
        }

        $state = $proc['statename'] === 'RUNNING' ? 'available' : 'unavailable';

        if ($states[$proc['name']] !== $state) {
            // Something has changed, do something.
            // file_put_contents('/home/vagrant/Code/twitch-points/supervisord/event.log', str_replace('twitch-bot-', '', $proc['name']) . ' ' . $state . "\r\n", FILE_APPEND);
            try {
                $guzzleClient->request('PUT', '/bot/status', ['json' => ['status' => $state, 'bot' => str_replace('twitch-bot-', '', $proc['name'])]]);
            } catch (Exception $e) {
                // echo $e->getMessage();
            }

        }

        $states[$proc['name']] = $state;
    }

    return true;
});
