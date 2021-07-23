<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TransactionExplorerService
{
    private $chain;
    private $blockNumber;
    private $hash;

    /**
     * @throws \Exception
     */
    function __construct($chain, $hash = null, $blockNumber = null)
    {
        if (is_null($blockNumber) && is_null($hash)) {
            Log::critical('TransactionExplorerService Error', ['message' => 'The Both Transaction Hash And Block Number Are Empty']);
            throw new \Exception('App\Services\TransactionExplorerServiceThe The Both Transaction Hash And Block Number Are Empty');
        }

        $this->chain = $chain;
        $this->blockNumber = $blockNumber;
        $this->hash = $hash;
    }

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
        } catch (\Exception $exception) {
            Log::critical('convertHexToDecimal Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
            return FALSE;
        }
    }

    /**
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
                    $blockNumber = is_null($this->blockNumber) ? self::getBlockNumberByHash() : $this->blockNumber;
                    $latestBlockNumber = self::getNumberOfTheLastBlock();
                    return $latestBlockNumber - $blockNumber;
                case 'litecoin':
                case 'bitcoin-cash':
                case 'tezos':
                    return self::getConfirmationCountByHash();
                default:
                    Log::critical('TransactionExplorerService Error', ['message' => 'Unknown Chain "' . $this->chain . '"']);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
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
                    Log::critical('TransactionExplorerService Error', ['message' => 'Unknown Chain "' . $this->chain . '"']);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
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
                    Log::critical('TransactionExplorerService Error', ['message' => 'Unknown Chain "' . $this->chain . '"']);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
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

            } catch (\Exception $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = null;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->result);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
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

            } catch (\Exception $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = null;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->height + 1);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
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

            } catch (\Exception $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = null;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->blockNumber);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * @return mixed
     */
    protected function getNumberOfTheLastBlockForRipple()
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => config('api.rest_cryptoapis.baseUri') . '/'
                ]);

                $response = $client->request('GET', implode('/', ['v' . config('api.rest_cryptoapis.version'), 'blockchain-data', 'xrp-specific', 'testnet', 'blocks', 'last']), [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'X-API-Key' => config('api.rest_cryptoapis.key'),
                    ]
                ]);

            } catch (\Exception $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = null;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->data->item->blockHeight);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
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
                    Log::critical('TransactionExplorerService Error', ['message' => 'Unknown Chain "' . $this->chain . '"']);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
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

            } catch (\Exception $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = null;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->result->blockNumber);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
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

            } catch (\Exception $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = null;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->blockHeight);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
     * @return mixed
     */
    protected function getBlockNumberByHashForRipple($hash)
    {
        try {
            try {
                $client = new Client([
                    'base_uri' => config('api.rest_cryptoapis.baseUri') . '/'
                ]);

                $response = $client->request('GET', implode('/', ['v' . config('api.rest_cryptoapis.version'), 'blockchain-data', 'xrp-specific', 'testnet', 'transactions', $hash]), [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'X-API-Key' => config('api.rest_cryptoapis.key'),
                    ]
                ]);

            } catch (\Exception $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = null;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->data->item->minedInBlockHeight);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
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

            } catch (\Exception $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = null;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->block_height);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
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

            } catch (\Exception $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = null;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->confirmations);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
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

            } catch (\Exception $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = null;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content->data->confirmations);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }

    /**
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
            } catch (\Exception $exception) {
                Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
                $response = null;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $content = json_decode($bodyJson->getContents());

                return self::convertHexToDecimal($content[0]->confirmations);
            }
        } catch (\Exception $exception) {
            Log::critical('TransactionExplorerService Ethereum Explore Error', ['statusCode' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }

        return FALSE;
    }
}

