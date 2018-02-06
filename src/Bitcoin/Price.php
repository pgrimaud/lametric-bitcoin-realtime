<?php
namespace Bitcoin;

use GuzzleHttp\Client as GuzzleClient;
use Predis\Client as PredisClient;

class Price
{
    const TICKER_ENDPOINT = 'https://s2.bitcoinwisdom.com/ticker';

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
     * Price constructor.
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
     */
    public function getValue()
    {
        $redisKey = 'lametric:bitcoin:' . $this->exchange->getName();

        $price = $this->predisClient->get($redisKey);

        if (!$price) {
            $resource = $this->guzzleClient->request('GET', self::TICKER_ENDPOINT);

            $file = $resource->getBody();
            $data = json_decode($file);

            $prop = $this->exchange->getName() . 'btcusd';

            $price = $data->{$prop}->last;

            $this->predisClient->set($redisKey, (int)$price);
            $this->predisClient->expireat($redisKey, strtotime("+30 seconds"));
        }

        return $price;
    }
}
