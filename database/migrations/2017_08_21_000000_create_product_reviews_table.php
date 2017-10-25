<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductReviewsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'product_reviews';

    /**
     * Run the migrations.
     * @table product_reviews
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('user_id')->default('0');
            $table->string('ip_address', 15)->default('');
            $table->string('name')->nullable();
            $table->string('location')->nullable();
            $table->decimal('rating', 8, 4)->default('0.0');
            $table->longText('review')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(["product_id", "ip_address", "user_id"], 'product_reviews_product_user');

            $table->index(["product_id"], 'product_reviews_product_id');
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
