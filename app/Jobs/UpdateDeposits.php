<?php
namespace App\Jobs;
use Exception;
use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Deposit;
use App\Services\Coinbase;
class UpdateDeposits implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return 'update-deposits';
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Deposit::with(['owner', 'account.currency', 'transaction'])
            ->whereNotNull('cb_id')
            ->whereNull('confirmed_at')
            ->chunkById(200, function ($deposits) {
                foreach ($deposits as $deposit) {
                    try {
                        $response = Coinbase::getAccountTransaction(
                            $deposit->account->currency->code,
                            $deposit->cb_id
                        );
                        if (! $response || ! isset($response['id'])) {
                            throw new Exception('Wrong response!');
                        }
                        if ($response['status'] === 'completed') {
                            // Confirm deposit
                            $deposit->update(['confirmed_at' => now()]);
                            // Confirm transaction
                            $deposit->transaction->update([
                                'confirmed_at' => now(),
                                'completed_at' => now(),
                            ]);
                            // Update balance
                            $deposit->account->updateBalance();
                            // Mark user
                            if (! $deposit->owner->has_deposit) {
                                $deposit->owner->update(['has_deposit' => true]);
                            }
                        }
                    } catch (Throwable $e) {
                        Log::critical('App\Jobs\UpdateDeposits failed!', [
                            'id' => $deposit->id,
                            'error' => $e->getMessage(),
                        ]);
                        continue;
                    }
                }
            });
    }
}
