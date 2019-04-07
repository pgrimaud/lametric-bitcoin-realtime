<?php

namespace Bitcoin;

use GuzzleHttp\Client as GuzzleClient;
use Predis\Client as PredisClient;

class Price
{
    const ENDPOINT_BITFINEX = 'https://api.bitfinex.com/v1/pubticker/btcusd';
    const ENDPOINT_BITSTAMP = 'https://www.bitstamp.net/api/v2/ticker/btcusd/';

    /**
     * @var GuzzleClient
     */
    private $guzzleClient;

    /**
     * @var PredisClient
     */
    private $predisClient;

    /**
     * @var Exchange
     */
    private $exchange;

    /**
     * @param GuzzleClient $guzzleClient
     * @param PredisClient $predisClient
     * @param Exchange $exchange
     */
    public function __construct(GuzzleClient $guzzleClient, PredisClient $predisClient, Exchange $exchange)
    {
        $this->guzzleClient = $guzzleClient;
        $this->predisClient = $predisClient;
        $this->exchange     = $exchange;
    }

    /**
     * @return string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getValue()
    {
        $redisKey = 'lametric:bitcoin:' . $this->exchange->getName();

        $price = $this->predisClient->get($redisKey);
        $ttl   = $this->predisClient->ttl($redisKey);

        if (!$price || $ttl < 0) {

            if ($this->exchange->getName() === Exchange::EXCHANGE_BITSTAMP) {
                $endpoint = self::ENDPOINT_BITSTAMP;
            } else {
                $endpoint = self::ENDPOINT_BITFINEX;
            }

            $resource = $this->guzzleClient->request('GET', $endpoint);

            $file = $resource->getBody();
            $data = json_decode($file);

            if ($this->exchange->getName() === Exchange::EXCHANGE_BITSTAMP) {
                $price = (int)$data->last;
            } else {
                $price = (int)$data->last_price;
            }

            $this->predisClient->set($redisKey, $price);
            $this->predisClient->expireat($redisKey, strtotime("+30 seconds"));
        }

        return $price;
    }
}
