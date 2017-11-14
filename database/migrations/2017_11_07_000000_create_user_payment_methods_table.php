<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPaymentMethodsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'user_payment_methods';

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
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('payment_profile_id')->nullable();

            $table->string('brand')->nullable();
            $table->string('masked_card_number');
            $table->string('expiration_date');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('address');
            $table->string('zip');
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
