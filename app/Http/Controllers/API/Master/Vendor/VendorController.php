<?php

namespace App\Http\Controllers\API\Master\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    public function getBusinessTerms() {
        $data = DB::table('general.business_terms')->select('id','description')->get();
        return response()->json($data);
    }
    public function getCurrencies() {
        $data = DB::table('general.setup_dropdown_items')->select('id','item')->where('type','currency')->get();
        return response()->json($data);
    }
    public function getBusinessTaxStatus() {
        $data = DB::table('general.business_tax')->select('id','vatcode')->get();
        return response()->json($data);
    }
    public function getBusinessType() {
        $data = DB::table('general.business_type')->select('ID','BusinessType')->get();
        return response()->json($data);
    }
    public function getBusinessNature() {
        $data = DB::table('general.business_nature')->select('ID','Nature')->get();
        return response()->json($data);
    }
    public function getPrefixes() {
        $data = DB::table('general.setup_dropdown_items')->select('id','item')->where('type','prefix')->get();
        return response()->json($data);
    }
    public function getCountries() {
        $data = DB::table('general.country')->select('id','Description')->get();
        return response()->json($data);
    }
    public function getATC() {
        $data = DB::table('accounting.atc_setup')->select('ID','DESCRIPTION','ATC','RATE')->get();
        return response()->json($data);
    }
    public function getRelationship() {
        $data = DB::table('general.affiliate_type')->select('type')->get();
        return response()->json($data);
    }
    public function getRelatedSupplier() {
        $data = DB::table('general.business_list')->select('ACCOUNTNUMBER','business_fullname')->where('type','SUPPLIER')->get();
        return response()->json($data);
    }
    public function getRelatedCustomer() {
        $data = DB::table('general.business_list')->select('ACCOUNTNUMBER','business_fullname')->where('type','CLIENT')->get();
        return response()->json($data);
    }
}
