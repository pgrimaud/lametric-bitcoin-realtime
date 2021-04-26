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
     * @param int|null   $price
     * @param string     $symbol
     * @param int|null   $height
     * @param float|null $satPrice
     * @return string
     */
    public function data(?int $price = 0, string $symbol = '$', int $height = null, float $satPrice = null): string
    {
        $position = 0;
        $frames   = [];

        if ($price) {
            $frames = array_merge($frames, [
                [
                    'index' => $position,
                    'text'  => ($price) . $symbol,
                    'icon'  => 'i857',
                ],
            ]);
            $position++;
        }

        if ($satPrice) {
            $frames = array_merge($frames, [
                [
                    'index' => $position,
                    'text'  => $satPrice,
                    'icon'  => 'i45102',
                ],
            ]);
            $position++;
        }

        if ($height) {
            $frames = array_merge($frames, [
                [
                    'index' => $position,
                    'text'  => $height,
                    'icon'  => 'i45101',
                ],
            ]);
        }

        return $this->asJson([
            'frames' => $frames,
        ]);
    }
}
