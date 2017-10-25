<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingRatesTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'shipping_rates';

    /**
     * Run the migrations.
     * @table shipping_rates
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shipment_id');
            $table->string('carrier');
            $table->string('service_code');
            $table->string('service_name');
            $table->unsignedInteger('business_transit_days')->nullable();
            $table->decimal('amount', 15, 4);
            $table->unsignedInteger('box_count');
            $table->string('packaging');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index(["shipment_id"], 'shipping_rates_shipment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists($this->set_schema_table);
     }
}
