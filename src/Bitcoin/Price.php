<?php

declare(strict_types=1);

namespace Bitcoin;

use GuzzleHttp\Client as GuzzleClient;
use Predis\Client as PredisClient;

class Price
{
    const ENDPOINT_BITFINEX = 'https://api.bitfinex.com/v1/pubticker/btc{{currency}}';
    const ENDPOINT_BITSTAMP = 'https://www.bitstamp.net/api/v2/ticker/btc{{currency}}/';
    const ENDPOINT_COINBASE = 'https://api.coinbase.com/v2/exchange-rates?currency=BTC';

    /**
     * @var GuzzleClient
     */
    private GuzzleClient $guzzleClient;

    /**
     * @var PredisClient
     */
    private PredisClient $predisClient;

    /**
     * @var Exchange
     */
    private Exchange $exchange;

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
     * @return int
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getValue(): int
    {
        $redisKey = 'lametric:bitcoin:' . $this->exchange->getName() . ':' . strtolower($this->exchange->getCurrency());

        $price = $this->predisClient->get($redisKey);
        $ttl   = $this->predisClient->ttl($redisKey);

        if (!$price || $ttl < 0) {

            switch ($this->exchange->getName()) {
                case Exchange::EXCHANGE_BITSTAMP:
                    $endpoint = str_replace('{{currency}}', $this->exchange->getCurrency(), self::ENDPOINT_BITSTAMP);
                    break;
                case Exchange::EXCHANGE_BITFINEX:
                    $currency = $this->exchange->getCurrency() === 'CHF' ? 'XCH' : $this->exchange->getCurrency();
                    $endpoint = str_replace('{{currency}}', $currency, self::ENDPOINT_BITFINEX);
                    break;
                case Exchange::EXCHANGE_COINBASE:
                default:
                    $endpoint = self::ENDPOINT_COINBASE;
                    break;
            }

            $resource = $this->guzzleClient->request('GET', $endpoint);

            $file = $resource->getBody();
            $data = json_decode((string)$file);

            switch ($this->exchange->getName()) {
                case Exchange::EXCHANGE_BITSTAMP:
                    $price = (int)$data->last;
                    break;
                case Exchange::EXCHANGE_BITFINEX:
                    $price = (int)$data->last_price;
                    break;
                case Exchange::EXCHANGE_COINBASE:
                    $price = (int)$data->data->rates->{$this->exchange->getCurrency()};
                    break;
                default:
                    $price = 0;
                    break;
            }

            $this->predisClient->set($redisKey, $price);
            $this->predisClient->expireat($redisKey, strtotime("+30 seconds"));
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
        }

        return $symbol;
    }
}
