<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGiftCardFailuresTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'gift_card_failures';

    /**
     * Run the migrations.
     * @table gift_card_failures
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
            $table->string('ip_address', 15);

            $table->index(["ip_address"], 'gift_card_failures_ipaddress');

            $table->index(["created_at"], 'gift_card_failures_timestamp');

            $table->index(["user_id"], 'gift_card_failures_user_id');
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
