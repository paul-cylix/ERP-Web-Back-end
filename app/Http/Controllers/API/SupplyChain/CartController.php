<?php

namespace App\Http\Controllers\API\SupplyChain;

use App\Http\Controllers\ApiController;
use App\Models\SupplyChain\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CartController extends ApiController
{
    public function store(Request $request) {
        $cart            = new Cart();
        $cart->cart_userid    = $request->loggedUserId;
        $cart->cart_companyid = $request->companyId;
        $cart->cart_group_detail_id = $request->id;
        $cart->cart_uom_id    = $request->uomId;
        $cart->cart_uom_name  = $request->uomName;
        $cart->save();
        return response()->json('Item has beed added to your cart' , 200);
    }



    public function destroy(Request $request) {
        Cart::whereIn('cart_id', $request->productId)->delete();
        return response()->json('Item has beed deleted successfully' , 200);
    }


    public function showCartOne($loggedUserId, $companyId){
        $cartData = Cart::where('cart_userid', $loggedUserId)
        ->where('cart_companyid', $companyId)
        ->whereIn('cart_status', [1,2])->get();
        return response()->json($cartData);
    }

    public function showCart($loggedUserId, $companyId, $status) {
        $cartData = DB::table('carts AS c')
        ->join('procurement.setup_group_detail AS s', 'c.cart_group_detail_id', '=', 's.group_detail_id')
        ->join('procurement.setup_brand AS b', 'b.id', '=', 's.brand_id')
        ->join('procurement.setup_group_type AS cat', 'cat.id', '=', 's.category_id')
        // ->join('procurement.setup_group AS subcat', 'subcat.group_id', '=', 's.sub_category_id')
        ->where('c.cart_userId', $loggedUserId)
        ->where('cart_companyId', $companyId)
        ->where('cart_status', $status)->get();
        return response()->json($cartData);
    }

    public function checkout(Request $request) {

        DB:: beginTransaction();
        try{  

            Cart:: whereIn('cart_status', [Cart::CHECKED,Cart::UNCHECK])
            ->where('cart_userid', $request->loggedUserId)
            ->where('cart_companyid', $request->companyId)
            ->delete();
    
            $updatedCart = $request->updatedCart;
            
            $arrayCart = [];
            foreach ($updatedCart as $key => $value) {

                $cartData = [
                    'cart_userid' => $request->loggedUserId,
                    'cart_companyid' => $request->companyId,
                    'cart_group_detail_id' => $value['cart_group_detail_id'],
                    'cart_uom_id' => $value['cart_uom_id'],
                    'cart_uom_name' => $value['cart_uom_name'],
                    'cart_status' => $value['cart_status'],
                    'cart_quantity' => $value['cart_quantity'],
                ];

                array_push($arrayCart, $cartData);
            }

            Cart:: insert($arrayCart);


            DB:: commit();
           return response()->json('Products are ready for Materials Request!' , 200);
        }catch(\Exception $e){
            DB:: rollback();
            // throw error response
            return response()->json($e, 500);
        }


    }

}
