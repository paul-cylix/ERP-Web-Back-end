<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SupplyChain\Cart;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id('cart_id');
            $table->integer('cart_userid');
            $table->integer('cart_companyid');
            $table->integer('cart_group_detail_id');
            $table->integer('cart_uom_id');
            $table->string('cart_uom_name');
            $table->integer('cart_status')->default(Cart::UNCHECK)->nullable();
            $table->integer('cart_quantity')->default(Cart::AVAILABLE)->nullable();
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
        Schema::dropIfExists('carts');
    }
}
