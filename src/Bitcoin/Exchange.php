<?php

namespace Bitcoin;

class Exchange
{
    const ALLOWED_EXCHANGES = [
        'bitstamp',
        'bitfinex'
    ];

    const EXCHANGE_BITSTAMP = 'bitstamp';
    const EXCHANGE_BITFINEX = 'bitfinex';

    /**
     * @var string
     */
    private $name;

    /**
     * @param array $parameters
     *
     * @throws \Exception
     */
    public function __construct($parameters = [])
    {
        $exchange   = strtolower(isset($parameters['exchange']) ? $parameters['exchange'] : '');
        $this->name = in_array($exchange, self::ALLOWED_EXCHANGES) ? $exchange : self::ALLOWED_EXCHANGES[0];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
