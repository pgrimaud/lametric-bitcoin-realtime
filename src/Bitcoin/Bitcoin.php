<?php

declare(strict_types=1);

namespace Bitcoin;

use GuzzleHttp\Client as GuzzleClient;
use Predis\Client as PredisClient;

class Bitcoin
{
    const ENDPOINT_BITFINEX = 'https://api.bitfinex.com/v1/pubticker/btcusd';
    const ENDPOINT_BITSTAMP = 'https://www.bitstamp.net/api/v2/ticker/btcusd/';
    const ENDPOINT_COINBASE = 'https://api.coinbase.com/v2/exchange-rates?currency=BTC';
    const ENDPOINT_KRAKEN = 'https://api.kraken.com/0/public/Ticker?pair=BTCUSDT';
    const ENDPOINT_BINANCE = 'https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT';

    const ENDPOINT_HEIGHT = 'https://blockchain.info/q/getblockcount';
    const ENDPOINT_NODES = 'https://bitnodes.io/api/v1/snapshots/';

    /**
     * @param GuzzleClient $guzzleClient
     * @param PredisClient $predisClient
     * @param Exchange $exchange
     */
    public function __construct(
        private GuzzleClient $guzzleClient,
        private PredisClient $predisClient,
        private Exchange $exchange
    ) {
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
        $currency = !empty($this->exchange->getCurrency()) ? $this->exchange->getCurrency() : 'USD';

        $redisKey = 'lametric:bitcoin:' . $this->exchange->getName() . ':' . strtolower($currency);

        $price = $this->predisClient->get($redisKey);
        $ttl = $this->predisClient->ttl($redisKey);

        if (!$price || $ttl < 0) {
            $endpoint = match ($exchange) {
                Exchange::EXCHANGE_BITSTAMP => self::ENDPOINT_BITSTAMP,
                Exchange::EXCHANGE_BITFINEX => self::ENDPOINT_BITFINEX,
                Exchange::EXCHANGE_KRAKEN => self::ENDPOINT_KRAKEN,
                Exchange::EXCHANGE_BINANCE => self::ENDPOINT_BINANCE,
                default => self::ENDPOINT_COINBASE,
            };

            $resource = $this->guzzleClient->request('GET', $endpoint);

            $file = $resource->getBody();
            $data = json_decode((string)$file);

            $price = match ($exchange) {
                Exchange::EXCHANGE_BITSTAMP => (int)$data->last,
                Exchange::EXCHANGE_BITFINEX => (int)$data->last_price,
                Exchange::EXCHANGE_COINBASE => (int)$data->data->rates->{$currency},
                Exchange::EXCHANGE_KRAKEN => (int)$data->result->XBTUSDT->c[0],
                Exchange::EXCHANGE_BINANCE => (int)$data->price,
                default => 0,
            };

            $currencies = json_decode($this->predisClient->get('lametric:forex'), true);

            if (!isset($currencies[$currency])) {
                throw new \Exception('Currency not found');
            }

            $price = intval($price * $currencies[$currency]);

            $this->predisClient->set($redisKey, $price);
            $this->predisClient->expireat($redisKey, strtotime('+5 seconds'));
        }

        return (int)$price;
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
            case 'ZAR':
                $symbol = 'R';
                break;
        }

        return $symbol;
    }

    public function getHeight(): int
    {
        $redisKey = 'lametric:bitcoin:height';

        $height = $this->predisClient->get($redisKey);
        $ttl = $this->predisClient->ttl($redisKey);

        if (!$height || $ttl < 0) {
            $resource = $this->guzzleClient->request('GET', self::ENDPOINT_HEIGHT);
            $height = (string)$resource->getBody();

            $this->predisClient->set($redisKey, $height);
            $this->predisClient->expireat($redisKey, strtotime("+300 seconds"));
        }

        return (int)$height;
    }

    public function getSatPrice(): float
    {
        $price = $this->getPrice($this->exchange->getName(), 'USD');
        $satPrice = 10e7 / $price;

        return round($satPrice, 2);
    }

    public function getNodes(): int
    {
        $redisKey = 'lametric:bitcoin:nodes';

        $nodes = $this->predisClient->get($redisKey);
        $ttl = $this->predisClient->ttl($redisKey);

        if (!$nodes || $ttl < 0) {
            $resource = $this->guzzleClient->request('GET', self::ENDPOINT_NODES);
            $jsonData = (string)$resource->getBody();

            $data = json_decode($jsonData, true);

            $nodes = (int)$data['results'][0]['total_nodes'];
            $this->predisClient->set($redisKey, $nodes);
            $this->predisClient->expireat($redisKey, strtotime("+300 seconds"));
        }

        return (int)$nodes;
    }
}
