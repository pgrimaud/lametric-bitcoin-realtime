<?php

declare(strict_types=1);

namespace Bitcoin;

use GuzzleHttp\Client as GuzzleClient;
use Predis\Client as PredisClient;

class Bitcoin
{
    const ENDPOINT_BITFINEX = 'https://api.bitfinex.com/v1/pubticker/btc{{currency}}';
    const ENDPOINT_BITSTAMP = 'https://www.bitstamp.net/api/v2/ticker/btc{{currency}}/';
    const ENDPOINT_COINBASE = 'https://api.coinbase.com/v2/exchange-rates?currency=BTC';

    const ENDPOINT_HEIGHT = 'https://blockchain.info/q/getblockcount';
    const ENDPOINT_NODES = 'https://bitnodes.io/api/v1/snapshots/';

    /**
     * @param GuzzleClient $guzzleClient
     * @param PredisClient $predisClient
     * @param Exchange     $exchange
     */
    public function __construct(private GuzzleClient $guzzleClient, private PredisClient $predisClient, private Exchange $exchange)
    {
    }

    /**
     * @param string|null $exchange
     * @return int
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPrice(string $exchange = null, string $currency = null): int
    {
        $exchange ??= $this->exchange->getName();
        $currency ??= $this->exchange->getCurrency();

        $redisKey = 'lametric:bitcoin:' . $this->exchange->getName() . ':' . strtolower($currency);

        $price = $this->predisClient->get($redisKey);
        $ttl   = $this->predisClient->ttl($redisKey);

        if (!$price || $ttl < 0) {
            switch ($exchange) {
                case Exchange::EXCHANGE_BITSTAMP:
                    $endpoint = str_replace('{{currency}}', $currency, self::ENDPOINT_BITSTAMP);
                    break;
                case Exchange::EXCHANGE_BITFINEX:
                    $currency = $currency === 'CHF' ? 'XCH' : $currency;
                    $endpoint = str_replace('{{currency}}', $currency, self::ENDPOINT_BITFINEX);
                    break;
                case Exchange::EXCHANGE_COINBASE:
                default:
                    $endpoint = self::ENDPOINT_COINBASE;
                    break;
            }

            $resource = $this->guzzleClient->request('GET', $endpoint);

            $file = $resource->getBody();
            $data = json_decode((string) $file);

            switch ($exchange) {
                case Exchange::EXCHANGE_BITSTAMP:
                    $price = (int) $data->last;
                    break;
                case Exchange::EXCHANGE_BITFINEX:
                    $price = (int) $data->last_price;
                    break;
                case Exchange::EXCHANGE_COINBASE:
                    $price = (int) $data->data->rates->{$currency};
                    break;
                default:
                    $price = 0;
                    break;
            }

            $this->predisClient->set($redisKey, $price);
            $this->predisClient->expireat($redisKey, strtotime("+30 seconds"));
        }

        return (int) $price;
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        $symbol = '';

        switch ($this->exchange->getCurrency()) {
            case 'USD':
                $symbol = '$';
                break;
            case 'EUR':
                $symbol = '€';
                break;
            case 'GBP':
                $symbol = '£';
                break;
            case 'JPY':
                $symbol = '¥';
                break;
            case 'CHF':
                $symbol = 'CHF';
                break;
        }

        return $symbol;
    }

    public function getHeight(): int
    {
        $redisKey = 'lametric:bitcoin:height';

        $height = $this->predisClient->get($redisKey);
        $ttl    = $this->predisClient->ttl($redisKey);

        if (!$height || $ttl < 0) {
            $resource = $this->guzzleClient->request('GET', self::ENDPOINT_HEIGHT);
            $height   = (string) $resource->getBody();

            $this->predisClient->set($redisKey, $height);
            $this->predisClient->expireat($redisKey, strtotime("+300 seconds"));
        }

        return (int) $height;
    }

    public function getSatPrice(): float
    {
        $price    = $this->getPrice($this->exchange->getName(), 'USD');
        $satPrice = 10e7 / $price;

        return round($satPrice, 2);
    }

    public function getNodes(): int
    {
        $redisKey = 'lametric:bitcoin:nodes';

        $nodes = $this->predisClient->get($redisKey);
        $ttl   = $this->predisClient->ttl($redisKey);

        if (!$nodes || $ttl < 0) {
            $resource = $this->guzzleClient->request('GET', self::ENDPOINT_NODES);
            $jsonData = (string) $resource->getBody();

            $data = json_decode($jsonData, true);

            $nodes = (int) $data['results'][0]['total_nodes'];
            $this->predisClient->set($redisKey, $nodes);
            $this->predisClient->expireat($redisKey, strtotime("+300 seconds"));
        }

        return (int) $nodes;
    }
}
