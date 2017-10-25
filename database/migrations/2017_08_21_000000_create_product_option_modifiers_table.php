<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductOptionModifiersTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'product_option_modifiers';

    /**
     * Run the migrations.
     * @table product_option_modifiers
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->string('values', 128);
            $table->string('sku')->nullable();
            $table->decimal('price', 15, 4)->nullable();
            $table->unsignedInteger('inventory')->nullable();
            $table->decimal('weight', 12, 4)->nullable();
            $table->boolean('disable')->default('0');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->unique(["product_id", "values"], 'product_option_modifiers_product_values');
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
