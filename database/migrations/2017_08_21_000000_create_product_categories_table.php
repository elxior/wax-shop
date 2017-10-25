<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductCategoriesTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'product_categories';

    /**
     * Run the migrations.
     * @table product_categories
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id');
            $table->string('name');
            $table->string('handler')->nullable();
            $table->string('breadcrumb')->nullable();
            $table->longText('description')->nullable()->default(null);
            $table->text('short_description')->nullable();
            $table->string('image')->nullable();
            $table->text('image_metadata')->nullable();
            $table->unsignedInteger('cms_sort_id')->default('0');
            $table->string('url_slug')->nullable();
            $table->boolean('url_lock')->default('0');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index(["parent_id"], 'product_categories_parent_id');
            $table->softDeletes();
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
