<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;
use Exception;

class TransactionExplorerService
{
    private $chain;
    private $blockNumber;
    private $hash;
    private static $supportedCurrencies = [
        'btc',
        'eth',
        'usdt',
        'xrp',
        'bch',
        'ltc',
        'obt',
        'usdc',
        'eos',
        'comp',
        'link',
        'xlm',
        'xtz',
        'zec',
        'grt',
        'yfi',
    ];

    /**
     * Create A New Service Instance.
     *
     * @throws Exception
     */
    function __construct($chain, $hash = NULL, $blockNumber = NULL)
    {
        if (is_null($hash)) {
            Log::critical('TransactionExplorerService Error', ['message' => 'Transaction Hash Is Empty']);
            throw new Exception('App\Services\TransactionExplorerServiceThe Transaction Hash Is Empty');
        }

        $this->chain = $chain;
        $this->blockNumber = $blockNumber;
        $this->hash = $hash;
    }

    /**
     * Return Converted Value From Hexadecimal to Decimal.
     *
     * @throws Exception
     */
    protected function convertHexToDecimal($number)
    {
        try {
            if (is_string($number)) {
                return ctype_xdigit(str_replace('0x', '', $number)) ?
                    hexdec($number)
                    :
                    $number;
            }

            return $number;
        } catch (Exception $exception) {
            Log::critical('ConvertHexToDecimal Error', ['message' => $exception->getMessage()]);
            throw $exception;
        }
    }

    /**
     * Check If Entered Currency Code Exists In Supported Currency List.
     *
     * @throws Exception
     */
    public static function checkIsSupportedCurrency($code): bool
    {
        try {
            if (is_null($code)) {
                throw new Exception('Code Is Empty');
            }

            return in_array($code, self::$supportedCurrencies);
        } catch (Exception $exception) {
            Log::critical('checkIsSupportedCurrency Error', ['message' => $exception->getMessage()]);
            throw $exception;
        }
    }

    /**
     * Return Confirmation Count From Requests.
     *
     * @return mixed
     */
    public function getConfirmationCount()
    {
        try {
            switch ($this->chain) {
                case 'ethereum':
                case 'zcash':
                case 'bitcoin':
                case 'ripple':
                    if (is_null($this->blockNumber)) {
                        $this->blockNumber = self::getBlockNumberByHash();
                    }
                    $latestBlockNumber = self::getNumberOfTheLastBlock();
                    return $latestBlockNumber - $this->blockNumber;
                case 'litecoin':
                case 'bitcoin-cash':
                case 'tezos':
                    return self::getConfirmationCountByHash();
                default:
                    Log::critical('TransactionExplorerService Error', ['message' => 'Unsupported Chain : "' . $this->chain . '"']);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return BlockNumber Property Of Instance.
     *
     * @return mixed
     */
    public function getBlockNumber()
    {
        return $this->blockNumber;
    }

    /**
     * Return Confirmation Count By Transaction Hash.
     *
     * @return mixed
     */
    public function getConfirmationCountByHash()
    {
        try {
            switch ($this->chain) {
                case 'litecoin':
                    return self::getConfirmationCountByHashForLitecoin($this->hash);
                case 'bitcoin-cash':
                    return self::getConfirmationCountByHashForBitcoinCash($this->hash);
                case 'tezos':
                    return self::getConfirmationCountByHashForTezos($this->hash);
                default:
                    Log::critical('TransactionExplorerService Error', ['message' => 'Unsupported Chain : "' . $this->chain . '"']);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Number Of The Last Block In Chain.
     *
     * @return mixed
     */
    public function getNumberOfTheLastBlock()
    {
        try {
            switch ($this->chain) {
                case 'ethereum':
                    return self::getNumberOfTheLastBlockForEthereum();
                case 'zcash':
                    return self::getNumberOfTheLastBlockForZcash();
                case 'bitcoin':
                    return self::getNumberOfTheLastBlockForBitcoin();
                case 'ripple':
                    return self::getNumberOfTheLastBlockForRipple();
                default:
                    Log::critical('TransactionExplorerService Error', ['message' => 'Unsupported Chain : "' . $this->chain . '"']);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Number Of The Last Block In Ethereum Chain.
     *
     * @return mixed
     */
    protected function getNumberOfTheLastBlockForEthereum()
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => config('api.etherscan.baseUri') . '/'
                ]);

                $response = $client->request('GET', '/api', [
                    'query' => [
                        'module' => 'proxy',
                        'action' => 'eth_blockNumber',
                        'apikey' => config('api.etherscan.apikey'),
                    ]
                ]);

            } catch (GuzzleException $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = NULL;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->result);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Number Of The Last Block In Bitcoin Chain.
     *
     * @return mixed
     */
    protected function getNumberOfTheLastBlockForBitcoin()
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => config('api.blockchain_info.baseUri') . '/'
                ]);

                $response = $client->request('GET', 'latestblock');

            } catch (GuzzleException $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = NULL;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->height + 1);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Number Of The Last Block In Zcash Chain.
     *
     * @return mixed
     */
    protected function getNumberOfTheLastBlockForZcash()
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => implode('/', [config('api.zcha.baseUri'), 'v' . config('api.zcha.version'), config('api.zcha.postfix')]) . '/'
                ]);

                $response = $client->request('GET', 'network', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ]
                ]);

            } catch (GuzzleException $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = NULL;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->blockNumber);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Number Of The Last Block In Ripple Chain.
     *
     * @return mixed
     */
    protected function getNumberOfTheLastBlockForRipple()
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => config('api.xrpscan.baseUri') . '/'
                ]);

                $response = $client->request('GET', implode('/', ['v' . config('api.xrpscan.version'), 'ledgers']), [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);

            } catch (GuzzleException $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = NULL;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->current_ledger);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Block Number By Transaction Hash.
     *
     * @return mixed
     */
    public function getBlockNumberByHash()
    {
        try {
            switch ($this->chain) {
                case 'ethereum':
                    return self::getBlockNumberByHashForEthereum($this->hash);
                case 'zcash':
                    return self::getBlockNumberByHashForZcash($this->hash);
                case 'bitcoin':
                    return self::getBlockNumberByHashForBitcoin($this->hash);
                case 'ripple':
                    return self::getBlockNumberByHashForRipple($this->hash);
                default:
                    Log::critical('TransactionExplorerService Error', ['message' => 'Unsupported Chain : "' . $this->chain . '"']);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Block Number By Transaction Hash In Ethereum Chain.
     *
     * @return mixed
     */
    protected function getBlockNumberByHashForEthereum($hash)
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => config('api.etherscan.baseUri') . '/'
                ]);

                $response = $client->request('GET', '/api', [
                    'query' => [
                        'module' => 'proxy',
                        'action' => 'eth_getTransactionByHash',
                        'txhash' => $hash,
                        'apikey' => config('api.etherscan.apikey'),
                    ]
                ]);

            } catch (GuzzleException $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = NULL;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->result->blockNumber);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Block Number By Transaction Hash In Zcash Chain.
     *
     * @return mixed
     */
    protected function getBlockNumberByHashForZcash($hash)
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => implode('/', [config('api.zcha.baseUri'), 'v' . config('api.zcha.version'), config('api.zcha.postfix')]) . '/'
                ]);

                $response = $client->request('GET', implode('/', ['transactions', $hash]), [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ]
                ]);

            } catch (GuzzleException $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = NULL;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->blockHeight);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Block Number By Transaction Hash In Ripple Chain.
     *
     * @return mixed
     */
    protected function getBlockNumberByHashForRipple($hash)
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => config('api.xrpscan.baseUri') . '/'
                ]);

                $response = $client->request('GET', implode('/', ['v' . config('api.xrpscan.version'), 'tx', $hash]), [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);

            } catch (GuzzleException $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = NULL;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->ledger_index);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Block Number By Transaction Hash In Bitcoin Chain.
     *
     * @return mixed
     */
    protected function getBlockNumberByHashForBitcoin($hash)
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => config('api.blockchain_info.baseUri') . '/'
                ]);

                $response = $client->request('GET', implode('/', ['rawtx', $hash]));

            } catch (GuzzleException $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = NULL;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->block_height);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Confirmation Count By Transaction Hash In Litecoin Chain.
     *
     * @return mixed
     */
    protected function getConfirmationCountByHashForLitecoin($hash)
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => config('api.blockcypher.baseUri') . '/'
                ]);

                $response = $client->request('GET', implode('/', ['v' . config('api.blockcypher.version'), 'ltc', 'main', 'txs', $hash]));

            } catch (GuzzleException $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = NULL;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->confirmations);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Confirmation Count By Transaction Hash In Bitcoin-Cash Chain.
     *
     * @return mixed
     */
    protected function getConfirmationCountByHashForBitcoinCash($hash)
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => config('api.bch_chain.baseUri') . '/'
                ]);

                $response = $client->request('GET', implode('/', ['v' . config('api.bch_chain.version'), 'tx', $hash]));

            } catch (GuzzleException $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = NULL;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->data->confirmations);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * Return Confirmation Count By Transaction Hash In Tezos Chain.
     *
     * @return mixed
     */
    protected function getConfirmationCountByHashForTezos($hash)
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => config('api.tzstats.baseUri') . '/'
                ]);

                $response = $client->request('GET', implode('/', ['explorer', 'op', $hash]));
            } catch (GuzzleException $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = NULL;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content[0]->confirmations);
            }
        } catch (Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }
}

