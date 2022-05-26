<?php

namespace App\Http\Controllers\API\SupplyChain;

use App\Http\Controllers\ApiController;
use App\Models\SupplyChain\Cart;
use Illuminate\Http\Request;

class CartController extends ApiController
{
    public function store(Request $request) {
        $cart            = new Cart();
        $cart->userId    = $request->loggedUserId;
        $cart->companyId = $request->companyId;
        $cart->group_detail_id = $request->id;
        $cart->uom_id    = $request->uomId;
        $cart->uom_name  = $request->uomName;
        $cart->save();
        return response()->json('Item has beed added to your cart' , 200);
    }

    public function showCartOne($loggedUserId, $companyId){
        $cartData = Cart::where('userId', $loggedUserId)
        ->where('companyId', $companyId)
        ->whereIn('status', [1,2])->get();
        return response()->json($cartData);
    }

    public function showCart($loggedUserId, $companyId, $status) {
        $cartData = Cart::where('userId', $loggedUserId)
        ->where('companyId', $companyId)
        ->where('status', $status)->get();
        return response()->json($cartData);
    }

}
