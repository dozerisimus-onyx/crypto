<?php

namespace App\Jobs;

use App\Models\Deposit;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckIsCoinbaseTransactionsConfirmed implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 100;
    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $client = new Client([
                'base_uri' => implode('/', [config('api.coinBaseApi.baseUri'), 'v' . config('api.coinBaseApi.version')]) . '/'
            ]);
            $headers = [
                'Authorization' => 'Bearer ' . config('api.coinBaseApi.bearer'),
                'CB-VERSION' => config('api.coinBaseApi.CB_VERSION'),
            ];

            Deposit::chunkById(200, function ($deposits) use($client, $headers) {
                $deposits->each(function ($deposit, $key) use($client, $headers){
                    $accountId = $deposit->account_id;
                    $transactionId = $deposit->cb_id;
                    $requestUri = "accounts/${accountId}/transactions/${transactionId}";
                    try {
                        $response = $client->request('GET', $requestUri, [
                            'headers' => $headers,
                        ]);

                        if ($response->getStatusCode() !== 200) {
                            Log::critical('Coinbase Transaction Resource Request Failed', ['statusCode' => $response->getStatusCode(), 'message' => $response->getReasonPhrase()]);
                            $response = null;
                        }
                    } catch (GuzzleException $e) {
                        if ($e->getCode() !== 404) {
                            Log::critical('Coinbase Transaction Resource Request Failed', ['statusCode' => $e->getCode(), 'message' => $e->getMessage()]);
                        }
                        $response = null;
                    }

                    try {
                        if ($response && $bodyJson = $response->getBody()) {
                            $contents = json_decode($bodyJson->getContents());
                            $actualDepositData = $contents->data;
                            if (
                                isset($actualDepositData->network) &&
                                isset($actualDepositData->network->status) &&
                                $actualDepositData->network->status === 'confirmed'
                            ) {
                                //
                                //Do something
                                //
                            }
                        }
                    } catch (\Exception $e) {
                        Log::critical('Coinbase Transaction Content Parse Error', ['statusCode' => $e->getCode(), 'message' => $e->getMessage()]);
                    }
                });
            }, $column = 'id');
        } catch (\Exception $e) {
            Log::critical('Error While Checking Coinbase Transaction Status', ['statusCode' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}
