<?php

namespace Bitcoin;

use GuzzleHttp\Client as GuzzleClient;
use Predis\Client as PredisClient;

class Price
{
    const ENDPOINT_BITFINEX = 'https://api.bitfinex.com/v1/pubticker/btcusd';
    const ENDPOINT_BITSTAMP = 'https://www.bitstamp.net/api/v2/ticker/btcusd/';
    const ENDPOINT_COINBASE = 'https://api.coinbase.com/v2/exchange-rates';

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
     * @param Exchange     $exchange
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

            switch ($this->exchange->getName()) {
                case Exchange::EXCHANGE_BITSTAMP:
                    $endpoint = self::ENDPOINT_BITSTAMP;
                    break;
                case Exchange::EXCHANGE_BITFINEX:
                    $endpoint = self::ENDPOINT_BITFINEX;
                    break;
                case Exchange::EXCHANGE_COINBASE:
                default:
                    $endpoint = self::ENDPOINT_COINBASE;
                    break;
            }

            $resource = $this->guzzleClient->request('GET', $endpoint);

            $file = $resource->getBody();
            $data = json_decode($file);

            switch ($this->exchange->getName()) {
                case Exchange::EXCHANGE_BITSTAMP:
                    $price = (int)$data->last;
                    break;
                case Exchange::EXCHANGE_BITFINEX:
                    $price = (int)$data->last_price;
                    break;
                case Exchange::EXCHANGE_COINBASE:
                    $price = (int)$data->rates->USD;
                    break;
                default:
                    $price = 0;
                    break;
            }

            $this->predisClient->set($redisKey, $price);
            $this->predisClient->expireat($redisKey, strtotime("+30 seconds"));
        }

        return $price;
    }
}
