<?php

declare(strict_types=1);

namespace Bitcoin;

class Response
{
    public const POSITION_BEFORE = 'before';
    public const POSITION_AFTER = 'after';

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
                    'text' => 'ERROR',
                    'icon' => 'i857',
                ],
            ],
        ]);
    }

    /**
     * @param int|null $price
     * @param string $symbol
     * @param int|null $height
     * @param float|null $satPrice
     * @param int|null $nodes
     *
     * @return string
     */
    public function data(
        string $symbolPosition,
        ?int   $price = 0,
        string $symbol = '$',
        int    $height = null,
        float  $satPrice = null,
        int    $nodes = null
    ): string
    {
        $position = 0;
        $frames = [];

        // set $ position
        if ($symbolPosition === self::POSITION_BEFORE) {
            $price = '$' . $price;
        } else if ($symbolPosition === self::POSITION_AFTER) {
            $price = $price . '$';
        }

        if ($price) {
            $frames = array_merge($frames, [
                [
                    'index' => $position,
                    'text' => $price,
                    'icon' => 'i857',
                ],
            ]);
            $position++;
        }

        if ($satPrice) {
            $frames = array_merge($frames, [
                [
                    'index' => $position,
                    'text' => $satPrice,
                    'icon' => 'i45102',
                ],
            ]);
            $position++;
        }

        if ($height) {
            $frames = array_merge($frames, [
                [
                    'index' => $position,
                    'text' => $height,
                    'icon' => 'i45101',
                ],
            ]);
            $position++;
        }

        if ($nodes) {
            $frames = array_merge($frames, [
                [
                    'index' => $position,
                    'text' => $nodes,
                    'icon' => 'i45219',
                ],
            ]);
        }

        return $this->asJson([
            'frames' => $frames,
        ]);
    }
}
