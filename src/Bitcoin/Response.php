<?php

declare(strict_types=1);

namespace Bitcoin;

class Response
{
    /**
     * @param array $data
     *
     * @return string
     */
    public function asJson(array $data = []): string
    {
        return json_encode($data);
    }

    /**
     * @return string
     */
    public function error(): string
    {
        return $this->asJson([
            'frames' => [
                [
                    'index' => 0,
                    'text'  => 'ERROR',
                    'icon'  => 'i857',
                ],
            ],
        ]);
    }

    /**
     * @param int $price
     *
     * @return string
     */
    public function data(int $price = 0): string
    {
        return $this->asJson([
            'frames' => [
                [
                    'index' => 0,
                    'text'  => ($price) . '$',
                    'icon'  => 'i857',
                ],
            ],
        ]);
    }
}
