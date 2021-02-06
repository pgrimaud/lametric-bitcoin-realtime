<?php

require_once __DIR__ . '/../vendor/autoload.php';
$config = require_once __DIR__ . '/../config/parameters.php';

Sentry\init(['dsn' => $config['sentry_key']]);

use Bitcoin\Exchange;
use Bitcoin\Price;
use Bitcoin\Response;
use GuzzleHttp\Client as GuzzleClient;
use Predis\Client as PredisClient;

header("Content-Type: application/json");

$response = new Response();

try {

    $exchange = new Exchange($_GET);
    $price    = new Price(new GuzzleClient(), new PredisClient(), $exchange);

    echo $response->data($price->getValue());

} catch (Exception $exception) {

    echo $response->error();

}
