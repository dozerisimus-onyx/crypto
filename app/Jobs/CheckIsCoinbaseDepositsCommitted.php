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

class CheckIsCoinbaseDepositsCommitted implements ShouldQueue, ShouldBeUnique
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
        $client = new Client([
            'base_uri' => implode('/', [config('api.coinBaseApi.baseUri'), 'v' . config('api.coinBaseApi.version')]) . '/'
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . config('api.coinBaseApi.bearer'),
            'CB-VERSION' => config('api.coinBaseApi.CB_VERSION'),
        ];
        $uncheckedDeposits = Deposit::all();
        foreach ($uncheckedDeposits as $deposit) {
            $accountId = $deposit->account_id;
            $depositId = $deposit->cb_id;
            $requestUri = "accounts/${accountId}/deposits/${depositId}";
            try {
                $response = $client->request('GET', $requestUri, [
                    'headers' => $headers,
                ]);

                if ($response->getStatusCode() !== 200) {
                    Log::critical('Coinbase Deposit Resource Request Failed', ['statusCode' => $response->getStatusCode(), 'message' => $response->getReasonPhrase()]);
                    $response = null;
                }
            } catch (GuzzleException $e) {
                if ($e->getCode() !== 404) {
                    Log::critical('Coinbase Deposit Resource Request Failed', ['statusCode' => $e->getCode(), 'message' => $e->getMessage()]);
                }
                $response = null;
            }
            if ($response && $bodyJson = $response->getBody()) {
                $contents = json_decode($bodyJson->getContents());
                $actualDepositData = $contents->data;
                if ($actualDepositData->committed) {
                    //Do something
                }
            }
        }
    }
}
