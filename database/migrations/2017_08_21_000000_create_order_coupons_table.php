<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderCouponsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'order_coupons';

    /**
     * Run the migrations.
     * @table order_coupons
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->string('code', 32);
            $table->string('title')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->decimal('dollars', 15, 4)->nullable();
            $table->unsignedInteger('percent')->nullable();
            $table->decimal('minimum_order', 15, 4)->nullable();
            $table->boolean('one_time')->default('1');
            $table->boolean('include_shipping')->default('0');
            $table->decimal('calculated_value', 15, 4)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
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
