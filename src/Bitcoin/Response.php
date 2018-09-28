<?php

namespace Bitcoin;

class Response
{
    /**
     * @param array $data
     *
     * @return mixed
     */
    public function asJson($data = [])
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * @return mixed
     */
    public function error()
    {
        return $this->asJson([
            'frames' => [
                [
                    'index' => 0,
                    'text'  => 'ERROR',
                    'icon'  => 'i857'
                ]
            ]
        ]);
    }

    /**
     * @param $price
     *
     * @return mixed
     */
    public function data($price = 0)
    {
        return $this->asJson([
            'frames' => [
                [
                    'index' => 0,
                    'text'  => ($price) . '$',
                    'icon'  => 'i857'
                ]
            ]
        ]);
    }
}
