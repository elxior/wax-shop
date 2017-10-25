<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductCustomizationTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'product_customization';

    /**
     * Run the migrations.
     * @table product_customization
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cms_sort_id')->default('0');
            $table->unsignedInteger('product_id')->default('0');
            $table->boolean('required')->default('1');
            $table->string('name')->default('');
            $table->string('type')->default('');
            $table->string('min')->default('');
            $table->string('max')->default('');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index(["product_id"], 'product_customization_product_id');
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
