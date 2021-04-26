<?php

declare(strict_types=1);

namespace Bitcoin;

class Exchange
{
    const ALLOWED_EXCHANGES = [
        'bitstamp',
        'bitfinex',
        'coinbase',
    ];

    const ALLOWED_CURRENCIES = [
        'USD',
        'EUR',
        'GBP',
        'JPY',
        'CHF',
    ];

    const EXCHANGE_BITSTAMP = 'bitstamp';
    const EXCHANGE_BITFINEX = 'bitfinex';
    const EXCHANGE_COINBASE = 'coinbase';

    private string $name;
    private string $currency;
    private bool $showSatoshi;
    private bool $showHeight;

    /**
     * @param array $parameters
     *
     * @throws \Exception
     */
    public function __construct(array $parameters = [])
    {
        $exchange   = strtolower(isset($parameters['exchange']) ? $parameters['exchange'] : '');
        $this->name = in_array($exchange, self::ALLOWED_EXCHANGES) ? $exchange : self::ALLOWED_EXCHANGES[0];

        $currency       = isset($parameters['currency']) ? $parameters['currency'] : '';
        $this->currency = in_array($currency, self::ALLOWED_CURRENCIES) ? $currency : self::ALLOWED_CURRENCIES[0];

        $this->showSatoshi = isset($parameters['satoshi']) && $parameters['satoshi'] === 'true';
        $this->showHeight  = isset($parameters['height']) && $parameters['height'] === 'true';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return bool
     */
    public function showSatoshi(): bool
    {
        return $this->showSatoshi;
    }

    /**
     * @return bool
     */
    public function showHeight(): bool
    {
        return $this->showHeight;
    }
}
