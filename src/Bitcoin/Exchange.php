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
        'ZAR (Coinbase only)',
    ];

    const EXCHANGE_BITSTAMP = 'bitstamp';
    const EXCHANGE_BITFINEX = 'bitfinex';
    const EXCHANGE_COINBASE = 'coinbase';

    private string $name;
    private string $currency;
    private bool $showSatoshi;
    private bool $showHeight;
    private bool $showPrice;
    private bool $showNodes;
    private string $position;

    public function __construct(array $parameters = [])
    {
        $exchange = strtolower(isset($parameters['exchange']) ? $parameters['exchange'] : '');
        $this->name = in_array($exchange, self::ALLOWED_EXCHANGES) ? $exchange : self::ALLOWED_EXCHANGES[0];

        $currency = isset($parameters['currency']) ? $parameters['currency'] : '';
        $this->currency = $this->sanitizeCurrency(in_array($currency, self::ALLOWED_CURRENCIES) ? $currency : self::ALLOWED_CURRENCIES[0]);

        $this->showPrice = !isset($parameters['bitcoin']) || (isset($parameters['bitcoin']) && ($parameters['bitcoin'] === 'true' || $parameters['bitcoin'] === ''));
        $this->showSatoshi = isset($parameters['satoshi']) && $parameters['satoshi'] === 'true';
        $this->showHeight = isset($parameters['height']) && $parameters['height'] === 'true';
        $this->showNodes = isset($parameters['nodes']) && $parameters['nodes'] === 'true';
        $this->position = $parameters['position'] ?? Response::POSITION_BEFORE;
    }

    private function sanitizeCurrency(string $currency): string
    {
        return strlen($currency) > 3 ? substr($currency, 0, 3) : $currency;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function showSatoshi(): bool
    {
        return $this->showSatoshi;
    }

    public function showHeight(): bool
    {
        return $this->showHeight;
    }

    public function showPrice(): bool
    {
        return $this->showPrice;
    }

    public function showNodes(): bool
    {
        return $this->showNodes;
    }

    public function getPosition(): string
    {
        return $this->position;
    }
}
