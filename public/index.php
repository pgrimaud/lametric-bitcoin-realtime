<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
$config = require_once __DIR__ . '/../config/parameters.php';

Sentry\init(['dsn' => $config['sentry_key']]);

use Bitcoin\{Exchange, Bitcoin, Response};
use GuzzleHttp\Client as GuzzleClient;
use Predis\Client as PredisClient;

header("Content-Type: application/json");

$response = new Response();

try {
    $exchange = new Exchange($_GET);
    $bitcoin  = new Bitcoin(new GuzzleClient(), new PredisClient(), $exchange);

    $price    = $exchange->showPrice() ? $bitcoin->getPrice() : null;
    $height   = $exchange->showHeight() ? $bitcoin->getHeight() : null;
    $satPrice = $exchange->showSatoshi() ? $bitcoin->getSatPrice() : null;

    echo $response->data($price, $bitcoin->getSymbol(), $height, $satPrice);

} catch (Exception $exception) {
    echo $response->error();
}
