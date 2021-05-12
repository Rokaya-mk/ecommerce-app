<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('date_sent');
            $table->dateTime('date_target');
            $table->boolean('hasCoupon');
            $table->string('couponDiscount')->nullable();
            $table->unsignedBigInteger('coupon_id');
            $table->boolean('money_payement')->default(0);
            $table->boolean('is_order_sent')->default(0);
            $table->foreign('user_id')->references('id')->on('users') ->onDelete('cascade');
            $table->foreign('coupon_id')->references('id')->on('coupons') ->onDelete('cascade');
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
        Schema::dropIfExists('orders');
    }
}
