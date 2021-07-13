<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangeRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('base_currency_id');
            $table->index('base_currency_id');
            $table->foreign('base_currency_id')->references('id')->on('currencies');
            $table->unsignedBigInteger('quote_currency_id');
            $table->index('quote_currency_id');
            $table->foreign('quote_currency_id')->references('id')->on('currencies');
            $table->float('exchange_rate');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_rates');
    }
}
