<?php

declare(strict_types = 1);

namespace Bitcoin;

class Exchange
{
    const ALLOWED_EXCHANGES = [
        'bitstamp',
        'bitfinex',
        'coinbase',
    ];

    const EXCHANGE_BITSTAMP = 'bitstamp';
    const EXCHANGE_BITFINEX = 'bitfinex';
    const EXCHANGE_COINBASE = 'coinbase';

    /**
     * @var string
     */
    private string $name;

    /**
     * @param array $parameters
     *
     * @throws \Exception
     */
    public function __construct(array $parameters = [])
    {
        $exchange   = strtolower(isset($parameters['exchange']) ? $parameters['exchange'] : '');
        $this->name = in_array($exchange, self::ALLOWED_EXCHANGES) ? $exchange : self::ALLOWED_EXCHANGES[0];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
