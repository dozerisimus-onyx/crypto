<?php

use Illuminate\Support\Facades\Route;
use App\Services\TransactionExplorerService;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    $transactions = [
        [
            'chain' => 'ripple',
            'code' => 'xrp',
            'hash' => 'A2B890C8C0ABBC6072AE294735800A0B242EF007D193C2CA01A1C352C14D4CCA',
            'blockNumber' => null
        ],
        [
            'chain' => 'bitcoin-cash',
            'code' => 'bch',
            'hash' => '29a5d3e5d0e1d370ffd3d7d0d9ffea5221ff3b6f6c4e5648355633cf35484604',
            'blockNumber' => null
        ],
        [
            'chain' => 'bitcoin',
            'code' => 'btc',
            'hash' => '273a596fda27c04f0298e7a1cb8274cd5bd0ac1178d0bc989906db648e7d0337',
            'blockNumber' => null
        ],
        [
            'chain' => 'litecoin',
            'code' => 'ltc',
            'hash' => 'bee867d3f6ceb55d4dd011d502cf652fdb4d6c476121b01acf7d6556db0470ed',
            'blockNumber' => null
        ],
        [
            'chain' => 'zcash',
            'code' => 'zec',
            'hash' => 'cd408d627400881b7a7bc44d39a19023dc98e0e00475f486d8a9defa169a313b',
            'blockNumber' => null
        ],
        [
            'chain' => 'ethereum',
            'code' => 'eth',
            'hash' => '0x1b46a44586ef81e8f7c5cb2dabeb2cbd745d38f04716c6549319658023ebb00f',
            'blockNumber' => null
        ],
        [
            'chain' => 'ethereum',
            'code' => 'obt',
            'hash' => '0xf6418243d0b0330bf23ba9c679dde0d60279f35eecd08b151631d499a0b01949',
            'blockNumber' => null
        ],
        [
            'chain' => 'ethereum',
            'code' => 'usdt',
            'hash' => '0x1531f8e3257d06aff6ddf7b6a34da3fb9213231486ac543b26685dc74b4d6c03',
            'blockNumber' => null
        ],
        [
            'chain' => 'ethereum',
            'code' => 'usdc',
            'hash' => '0xd44200f88817e9dbdfa8bde093f5a2bb508a9c08c4b74a6dc6df596e58277c21',
            'blockNumber' => null
        ],
//        [
//            'chain' => 'ethereum',
//            'code' => 'eos',
//            'hash' => '43c3e8c5652d1a7ff8d3459d21060a920f60ce89d0df61e56325c23ede8e3154',
//            'blockNumber' => null
//        ],
        [
            'chain' => 'ethereum',
            'code' => 'comp',
            'hash' => '0x80b85946179a7da966479f7733533ec2aff86932d490635d14eadf251d9e4d71',
            'blockNumber' => null
        ],
        [
            'chain' => 'ethereum',
            'code' => 'link',
            'hash' => '0x5e8589188cd869fd15b8cab0cbc310793ace08e9bed8492bbb0e4e9cb81d263c',
            'blockNumber' => null
        ],
        [
            'chain' => 'ethereum',
            'code' => 'xlm',
            'hash' => '0xccce607578c69a4e502390876f894d494e42654ddce9401b25f1c6aecdd2fefe',
            'blockNumber' => null
        ],
        [
            'chain' => 'tezos',
            'code' => 'xtz',
            'hash' => 'oopv8uxgC7wAFn13Wh54Aum6HfSxU4PaHSFQNmPWcENaLYsYpxK',
            'blockNumber' => null
        ],
        [
            'chain' => 'ethereum',
            'code' => 'grt',
            'hash' => '0x029245e166a6052b71016fb5293dead57831c4276ee064f8d1c9cb932d6b0d94',
            'blockNumber' => null
        ],
        [
            'chain' => 'ethereum',
            'code' => 'yfi',
            'hash' => '0x9c11038125c7f239b62871dafb635b6e8c547beaf32c0bb87259c76052a5c177',
            'blockNumber' => null
        ],
    ];

    echo '<pre>';
    foreach ($transactions as $transaction){
        $exploreService = new TransactionExplorerService($transaction['chain'], $transaction['hash'], $transaction['blockNumber']);
        $confirmationCount = $exploreService->getConfirmationCount() ?? 'ERROR';
        echo $transaction['code'] . ' (' . $transaction['chain'] . ')  confirmations:' . $confirmationCount . '  ' . $transaction['hash'] . PHP_EOL;
    }
    echo '</pre>';

    return view('welcome');
});
