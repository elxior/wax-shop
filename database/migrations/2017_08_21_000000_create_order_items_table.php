<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'order_items';

    /**
     * Run the migrations.
     * @table order_items
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('shipment_id');
            $table->unsignedInteger('quantity')->nullable()->default('1');
            $table->unsignedInteger('product_id');
            $table->string('brand')->nullable();
            $table->string('sku')->nullable();
            $table->string('name')->nullable();
            $table->decimal('price', 15, 4)->nullable();
            $table->decimal('shipping_flat_rate', 15, 4)->nullable();
            $table->boolean('shipping_enable_rate_lookup')->nullable();
            $table->boolean('shipping_disable_free_shipping')->nullable();
            $table->boolean('shipping_enable_tracking_number')->nullable();
            $table->float('dim_l')->nullable();
            $table->float('dim_w')->nullable();
            $table->float('dim_h')->nullable();
            $table->float('weight')->nullable();
            $table->boolean('one_per_user')->nullable();
            $table->boolean('taxable')->nullable();
            $table->boolean('discountable')->nullable();
            $table->decimal('discount_amount', 15, 4)->nullable();
            $table->unsignedInteger('bundle_id')->nullable();
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
