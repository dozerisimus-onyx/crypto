<?php

namespace App\Jobs;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;
use App\Models\ExchangeRate;
use App\Models\Currency;

class UpdateFiatExchangeRates implements ShouldQueue, ShouldBeUnique
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
     * Base currency for exchange rates
     *
     * @var string
     */
    protected $BASE_CURRENCY;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->BASE_CURRENCY = env('BASE_FIAT_CURRENCY', 'USD');
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

            try {
                $response = $client->request('GET', 'exchange-rates', [
                    'query' => ['currency' => $this->BASE_CURRENCY]
                ]);

                if ($response->getStatusCode() !== 200) {
                    Log::critical('Update Fiat Exchange Rates Request Failed', ['statusCode' => $response->getStatusCode(), 'message' => $response->getReasonPhrase()]);
                    $response = null;
                }
            } catch (GuzzleException $e) {
                if ($e->getCode() !== 404) {
                    Log::critical('Update Fiat Exchange Rates Request Failed', ['statusCode' => $e->getCode(), 'message' => $e->getMessage()]);
                }
                $response = null;
            }

            if ($response && $bodyJson = $response->getBody()) {
                $currencies = Currency::all();
                $base_currency_id = Currency::firstWhere('code', $this->BASE_CURRENCY)->id;
                $contents = json_decode($bodyJson->getContents());
                $rates = (array)$contents->data->rates;

                foreach ($currencies as $currency) {
                    $quote_currency_id = Currency::firstWhere('code', $currency->code)->id;
                    $exchangeRate = ExchangeRate::firstOrNew(
                        [
                            'base_currency_id' => $base_currency_id,
                            'quote_currency_id' => $quote_currency_id
                        ]
                    );
                    $exchangeRate->exchange_rate = floatval($rates[$currency->code]);
                    $exchangeRate->save();
                }
            }
        } catch (Exception $e) {
            Log::critical('Update Fiat Exchange Rates Failed', ['message' => $e->getMessage()]);
        }
    }
}
