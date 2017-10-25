<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderShipmentsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'order_shipments';

    /**
     * Run the migrations.
     * @table order_shipments
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sequence')->nullable();
            $table->unsignedInteger('order_id');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('desired_delivery_date')->nullable();
            $table->string('tax_desc')->nullable();
            $table->boolean('tax_shipping')->nullable();
            $table->decimal('tax_rate', 8, 4)->nullable();
            $table->decimal('tax_amount', 15, 4)->nullable();
            $table->string('shipping_carrier')->nullable();
            $table->string('shipping_service_code')->nullable();
            $table->string('shipping_service_name')->nullable();
            $table->decimal('shipping_service_amount', 15, 4)->nullable();
            $table->decimal('shipping_discount_amount', 15, 4)->nullable();
            $table->integer('business_transit_days')->nullable();
            $table->integer('box_count')->nullable();
            $table->string('packaging')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('company')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip', 32)->nullable();
            $table->string('country')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index(["order_id"], 'order_shipments_order_id');
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
