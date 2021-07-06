<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHasOfferAndPriceToUserBagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_bags', function (Blueprint $table) {
            $table->boolean('has_offer')->default(0)->after('is_final_bag');
            $table->double('price_after_offer')->nullable()->after('has_offer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_bags', function (Blueprint $table) {
            $table->dropColumn('has_offer');
            $table->dropColumn('price_after_offer');
        });
    }
}
