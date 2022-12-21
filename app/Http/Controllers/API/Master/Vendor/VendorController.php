<?php

namespace App\Http\Controllers\API\Master\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    protected function getGuid(){
        mt_srand((double)microtime()*10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);
        $GUID = '';
        $GUID = chr(123)
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);
        $GUID = trim($GUID, '{');
        $GUID = trim($GUID, '}');
        return $GUID;
    }
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
    public function getDraftVendorByUserID($userid) {
        DB::beginTransaction();
        try{  
            // check if there's a draft
            $businessList = DB::table('general.business_list')->select('Business_Number')->where(['status' => 'Draft', 'type' => 'SUPPLIER', 'encodedby' => $userid])->get();
            $businessListDetail = DB::table('general.business_list_detail')->select('BusinessNumber')->where('status','Draft')->get();
            // return response()->json($businessListDetail);

            $insertDraft = array(
                'type' => 'SUPPLIER',
                'status' => 'Draft',
                'encodedby' => $userid
            );

            if(count($businessList) >= 1) {
                // if there's a draft in db - delete then insert
                DB::table('general.business_list')->where('Business_Number', $businessList[0]->Business_Number)->delete();

                if(count($businessListDetail) >= 1) {
                    DB::table('general.business_list_detail')->where('BusinessNumber', $businessListDetail[0]->BusinessNumber)->delete();
                }

                $draftID = DB::table('general.business_list')->insertGetId($insertDraft);
            }
            else {
                // just insert if there's no existing draft
                $draftID = DB::table('general.business_list')->insertGetId($insertDraft);
            }

            DB::commit();
            return response()->json(['draftID' => $draftID]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json($e, 500);
        }
    }
    public function saveBusinessListDetail(Request $request) {
        try {
            $insertData = array(
                'BusinessNumber' => $request->draftID,
                'TypeOfAddress' => $request->addressType_name,
                'city' => $request->city,
                'province' => $request->province,
                'zip' => $request->zipcode,
                'country' => $request->countries_name,
                'business_hours' => $request->businessHours,
                'short_name' => $request->shortName,
                'address_line' => $request->line1,
                'address_line2' => $request->line2,
                'PhoneNum' => $request->phoneNumber,
                'EmailAdd' => $request->email,
                'Notes' => $request->notes,
                'Status' => 'Draft',
                'Preferred_Billing' => $request->preferredBilling,
                'Preferred_Shippping' => $request->preferredShipping,
                'Fax' => $request->fax,
                'GUID' => $this->getGuid(),

                // 'street' => '',
                // 'barangay' => '',
                // 'DefaultAddress' => '',
                // 'AddressCode' => '',
                // 'imported_from_excel' => '',
                // 'business_name' => '',
            );

            DB::table('general.business_list_detail')->insert($insertData);
            return response()->json('Address has been added');
        } catch (\Exception $e) {
            return response()->json($e, 500);
        }
    }
    public function getAddressByBusinessNumber($draftID) {
        $address = DB::table('general.business_list_detail')->select('*')->where(['status' => 'Draft', 'BusinessNumber' => $draftID])->get();
        return response()->json($address);
    }
    public function deleteAddressByID(Request $request) {
        DB::table('general.business_list_detail')->where('ID', $request->id)->delete();
        return response()->json('Data has been deleted!');
    }
    public function updateBusinessListDetail(Request $request) {
        try {
            $updateData = array(
                'BusinessNumber' => $request->draftID,
                'TypeOfAddress' => $request->addressType_name,
                'city' => $request->city,
                'province' => $request->province,
                'zip' => $request->zipcode,
                'country' => $request->countries_name,
                'business_hours' => $request->businessHours,
                'short_name' => $request->shortName,
                'address_line' => $request->line1,
                'address_line2' => $request->line2,
                'PhoneNum' => $request->phoneNumber,
                'EmailAdd' => $request->email,
                'Notes' => $request->notes,
                'Status' => 'Draft',
                'Preferred_Billing' => $request->preferredBilling,
                'Preferred_Shippping' => $request->preferredShipping,
                'Fax' => $request->fax,
                'GUID' => $this->getGuid(),

                // 'street' => '',
                // 'barangay' => '',
                // 'DefaultAddress' => '',
                // 'AddressCode' => '',
                // 'imported_from_excel' => '',
                // 'business_name' => '',
            );

            DB::table('general.business_list_detail')->where('ID', $request->id) ->limit(1)->update($updateData);
            return response()->json('Address has been updated');
            // return response()->json($updateData);
        } catch (\Exception $e) {
            return response()->json($e, 500);
        }
    }
    public function getAddressWorkFromByBusinessNumber($draftID) {
        $workFrom = DB::table('general.business_list_detail')->selectRaw('CONCAT(address_line, " ", city, " ", province, " ", zip, " ", country) AS workfrom')->where(['status' => 'Draft', 'BusinessNumber' => $draftID])->get();
        return response()->json($workFrom);
    }
}
