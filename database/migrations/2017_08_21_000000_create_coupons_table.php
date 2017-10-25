<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'coupons';

    /**
     * Run the migrations.
     * @table coupons
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 32);
            $table->timestamp('expired_at')->nullable();
            $table->string('title')->nullable();
            $table->decimal('dollars', 15, 4)->nullable();
            $table->unsignedInteger('percent')->nullable();
            $table->decimal('minimum_order', 15, 4)->nullable();
            $table->tinyInteger('one_time')->default('1');
            $table->tinyInteger('include_shipping')->default('0');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index(["code"], 'coupons_code');
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
