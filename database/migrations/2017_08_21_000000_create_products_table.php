<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'products';

    /**
     * Run the migrations.
     * @table products
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('brand')->nullable()->default('0');
            $table->string('model', 100)->nullable();
            $table->string('name', 250);
            $table->longText('meta_description')->default('');
            $table->longText('meta_keywords')->default('');
            $table->text('description');
            $table->text('short_description');
            $table->boolean('active')->default('1');
            $table->decimal('msrp', 15, 4)->default('0.00');
            $table->decimal('price', 15, 4)->default('0.00');
            $table->decimal('weight', 12, 4)->default('0');
            $table->decimal('dim_l', 12, 4)->default('0');
            $table->decimal('dim_w', 12, 4)->default('0');
            $table->decimal('dim_h', 12, 4)->default('0');
            $table->unsignedInteger('inventory')->default('0');
            $table->boolean('featured')->default('0');
            $table->boolean('taxable')->default('1');
            $table->boolean('discountable')->default('1');
            $table->boolean('one_per_user')->default('0');
            $table->string('sku', 100)->default('');
            $table->decimal('rating', 8, 4)->nullable();
            $table->unsignedInteger('rating_count')->default('0');
            $table->boolean('shipping_enable_rate_lookup')->default('1');
            $table->decimal('shipping_flat_rate', 15, 4)->default('0.00');
            $table->boolean('shipping_disable_free_shipping')->default('0');
            $table->boolean('shipping_enable_tracking_number')->default('1');
            $table->unsignedInteger('category_id')->nullable();
            $table->string('url_slug')->nullable();
            $table->boolean('url_lock')->default('0');
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
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
