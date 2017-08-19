<?php

$json   = file_get_contents('https://s2.bitcoinwisdom.com/ticker');
$decode = gzinflate(substr($json, 10, -8));

$data = json_decode($decode);

$display = [
    'frames' => [
        [
            'text' => $data->bitstampbtcusd->last . '$',
            'icon' => 'i857',
        ]
    ]
];

echo json_encode($display);
