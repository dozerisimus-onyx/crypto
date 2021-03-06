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
            'hash' => '1F6CD0FFEE2012945E466C94963E0B77858AB4963938EB552BBF0A21CB9E92F0',
            'blockNumber' => NULL
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
            'blockNumber' => NULL
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
            'blockNumber' => NULL
        ],
        [
            'chain' => 'ethereum',
            'code' => 'eth',
            'hash' => '0x1b46a44586ef81e8f7c5cb2dabeb2cbd745d38f04716c6549319658023ebb00f',
            'blockNumber' => NULL
        ],
        [
            'chain' => 'ethereum',
            'code' => 'obt',
            'hash' => '0xf6418243d0b0330bf23ba9c679dde0d60279f35eecd08b151631d499a0b01949',
            'blockNumber' => NULL
        ],
        [
            'chain' => 'ethereum',
            'code' => 'usdt',
            'hash' => '0x1531f8e3257d06aff6ddf7b6a34da3fb9213231486ac543b26685dc74b4d6c03',
            'blockNumber' => NULL
        ],
        [
            'chain' => 'ethereum',
            'code' => 'usdc',
            'hash' => '0xd44200f88817e9dbdfa8bde093f5a2bb508a9c08c4b74a6dc6df596e58277c21',
            'blockNumber' => NULL
        ],
        [
            'chain' => 'ethereum',
            'code' => 'eos',
            'hash' => '0xe0dbdd508f38eca19837b380c40b72188369537fad596c2d12ef40f18732b166',
            'blockNumber' => null
        ],
        [
            'chain' => 'ethereum',
            'code' => 'comp',
            'hash' => '0x80b85946179a7da966479f7733533ec2aff86932d490635d14eadf251d9e4d71',
            'blockNumber' => NULL
        ],
        [
            'chain' => 'ethereum',
            'code' => 'link',
            'hash' => '0x5e8589188cd869fd15b8cab0cbc310793ace08e9bed8492bbb0e4e9cb81d263c',
            'blockNumber' => NULL
        ],
        [
            'chain' => 'ethereum',
            'code' => 'xlm',
            'hash' => '0xccce607578c69a4e502390876f894d494e42654ddce9401b25f1c6aecdd2fefe',
            'blockNumber' => NULL
        ],
        [
            'chain' => 'tezos',
            'code' => 'xtz',
            'hash' => 'oopv8uxgC7wAFn13Wh54Aum6HfSxU4PaHSFQNmPWcENaLYsYpxK',
            'blockNumber' => NULL
        ],
        [
            'chain' => 'ethereum',
            'code' => 'grt',
            'hash' => '0x029245e166a6052b71016fb5293dead57831c4276ee064f8d1c9cb932d6b0d94',
            'blockNumber' => NULL
        ],
        [
            'chain' => 'ethereum',
            'code' => 'yfi',
            'hash' => '0x9c11038125c7f239b62871dafb635b6e8c547beaf32c0bb87259c76052a5c177',
            'blockNumber' => NULL
        ],
        [
            'chain' => 'dogecoin',
            'code' => 'doge',
            'hash' => 'c4f224bec28b9c561930459f79feec1146b13c6438c1686540a91a84a154cf56',
            'blockNumber' => NULL
        ],
        [
            'chain' => 'cardano',
            'code' => 'ada',
            'hash' => 'c9c79c71717341c32f442dbf665a53aaf6bbbdd03bfd59b8782068e3becf194d',
            'blockNumber' => NULL
        ],
    ];

    echo '<pre>';
    $start_time = microtime(true);
    foreach ($transactions as $transaction){
        $exploreService = new TransactionExplorerService($transaction['chain'], $transaction['hash'], $transaction['blockNumber']);
        $confirmationCount = $exploreService->getConfirmationCount();
        echo $transaction['code'] . ' (' . $transaction['chain'] . ')  confirmations:' . $confirmationCount . '  ' . $transaction['hash'] . ' BLOCK : ' . $exploreService->getBlockNumber() . PHP_EOL;
    }
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time);
    echo "It takes ".$execution_time." seconds to execute the script";
    echo '</pre>';

    return view('welcome');
});
