<?php
require __DIR__ . '/vendor/autoload.php';

use Bitcoin\Price;
use Bitcoin\Response;

header("Content-Type: application/json");

$response = new Response();

try {

    $exchange = new \Bitcoin\Exchange($_GET);
    $price    = new Price(new \GuzzleHttp\Client(), new \Predis\Client(), $exchange);

    echo $response->data($price->getValue());

} Catch (Exception $exception) {

    echo $response->error();

}
