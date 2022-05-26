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
            $table->id();
            $table->integer('userId');
            $table->integer('companyId');
            $table->integer('group_detail_id');
            $table->integer('uom_id');
            $table->string('uom_name');
            $table->integer('status')->default(Cart::UNCHECK)->nullable();
            $table->integer('quantity')->default(Cart::AVAILABLE)->nullable();
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
