<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductSizeColorQuantitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_size_color_quantities', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('size_id');
            $table->unsignedBigInteger('id');
            $table->string('color');
            $table->integer('quantity');
            $table->primary(['product_id','size_id','id']);
            $table->unique(['product_id','size_id','color']);
            $table->foreign(['product_id','size_id'])
                  ->references(['product_id','id'])->on('product_sizes')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_size_color_quantities');
    }
}
