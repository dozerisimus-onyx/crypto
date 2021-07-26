<?php

return [
    'coinBaseApi' => [
        'version' => env('COINBASE_API_VERSION', '2'),
        'baseUri' => 'https://api.coinbase.com',
        'bearer' => env('COINBASE_API_VERSION'),
        'CB_VERSION' => env('COINBASE_API_VERSION', '2021-07-14'),
    ],

    'etherscan' => [
        'baseUri' => 'https://api.etherscan.io',
        'apikey' => env('ETHERSCAN_API_KEY'),
    ],

    'zcha' => [
        'baseUri' => 'https://api.zcha.in',
        'version' => env('ZCHA_API_VERSION', '2'),
        'postfix' => 'mainnet',
    ],

    'blockcypher' => [
        'baseUri' => 'https://api.blockcypher.com',
        'version' => env('BLOCKCYPHER_API_VERSION', '1'),
    ],

    'blockchain_info' => [
        'baseUri' => 'https://blockchain.info',
    ],

    'bch_chain' => [
        'baseUri' => 'https://bch-chain.api.btc.com',
        'version' => env('BCH_CHAIN_API_VERSION', '3'),
    ],

    'tzstats' => [
        'baseUri' => 'https://api.tzstats.com',
    ],

    'xrpscan' => [
        'baseUri' => 'https://api.xrpscan.com/api',
        'version' => env('XRPSCAN_API_VERSION', '1'),
    ],
];
