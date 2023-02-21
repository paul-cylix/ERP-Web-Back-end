<?php

namespace App\Http\Controllers\API\Master\Vendor;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends ApiController
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
        $data = DB::table('general.business_list')->select('Business_Number','ACCOUNTNUMBER','business_fullname')->where('type','SUPPLIER')->get();
        return response()->json($data);
    }
    public function getRelatedCustomer() {
        $data = DB::table('general.business_list')->select('Business_Number','ACCOUNTNUMBER','business_fullname')->where('type','CLIENT')->get();
        return response()->json($data);
    }
    public function getDraftVendorByUserID($userid) {
        DB::beginTransaction();
        try{  
            // check if there's a draft
            $businessList = DB::table('general.business_list')->select('Business_Number')->where(['status' => 'Draft', 'type' => 'SUPPLIER', 'encodedby' => $userid])->get();
           
            $insertDraft = array(
                'type' => 'SUPPLIER',
                'status' => 'Draft',
                'encodedby' => $userid
            );

            if(count($businessList) >= 1) {
                // if there's a draft in db - delete then insert
                $businessListDetail = DB::table('general.business_list_detail')->select('BusinessNumber')->where(['status' => 'Draft', 'BusinessNumber' => $businessList[0]->Business_Number])->get();
                $businessContacts = DB::table('general.businesscontacts')->select('BusinessNumber')->where(['status' => 'Draft', 'BusinessNumber' => $businessList[0]->Business_Number])->get();
                
                if(count($businessListDetail) >= 1) {
                    DB::table('general.business_list_detail')->where('BusinessNumber', $businessListDetail[0]->BusinessNumber)->delete();
                }
                DB::table('general.business_list')->where('Business_Number', $businessList[0]->Business_Number)->delete();

                if(count($businessContacts) >= 1) {
                    DB::table('general.businesscontacts')->where('BusinessNumber', $businessList[0]->Business_Number)->delete();
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
                'address_line' => $request->line1,
                'address_line2' => $request->line2,
                'Notes' => $request->notes,
                'Status' => 'Draft',
                'Preferred_Billing' => $request->preferredBilling,
                'Preferred_Shippping' => $request->preferredShipping,
                'GUID' => $this->getGuid(),
                'street' => $request->line1,
                'barangay' => $request->line2,
                'DefaultAddress' => 1,
                'AddressCode' => '',
                'imported_from_excel' => '',
                'business_name' => $request->businessName,
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
                'street' => $request->line1,
                'barangay' => $request->line2,
                'business_name' => $request->businessName,
            );

            DB::table('general.business_list_detail')->where('ID', $request->id) ->limit(1)->update($updateData);
            return response()->json('Address has been updated');
            // return response()->json($updateData);
        } catch (\Exception $e) {
            return response()->json($e, 500);
        }
    }
    public function getAddressWorkFromByBusinessNumber($draftID) {
        $workFrom = DB::table('general.business_list_detail')->selectRaw('ID, CONCAT(address_line, " ", city, " ", province, " ", zip, " ", country) AS workfrom')->where(['status' => 'Draft', 'BusinessNumber' => $draftID])->get();
        return response()->json($workFrom);
    }
    public function saveBusinessContacts(Request $request) {
        try {
            $insertData = array(
                'BusinessNumber' => $request->draftID,
                'Number' => $request->mobileNumber,
                'EmailAdd' => $request->email,
                'Prefix' => $request->prefix_name,
                'FirstName' => $request->firstName,
                'LastName' => $request->lastName,
                'Suffix' => $request->suffix,
                'nickname' => $request->nickName,
                'Department' => $request->department,
                'MobileNoSubs' => $request->mobileNumberCheckbox,
                'EmailAddsSubs' => $request->emailCheckbox,
                'WorksFromID' => $request->worksFromID,
                'WorksFrom' => $request->worksFromName,
                'Remarks' => $request->notes,
                'Status' => 'Draft',
                'GUID' => $this->getGuid(),
                'ContactName' => $request->prefix_name. ' ' .$request->firstName. ' ' .$request->lastName. ' ' .$request->suffix,
                'display_to' => '',
                'contactType' => '',
                'CountryCode' => '',
                'AreaCode' => '',
                'Position' => $request->designation,
                'imported_from_excel' => '',
            );

            DB::table('general.businesscontacts')->insert($insertData);
            return response()->json('Contact has been added');
        } catch (\Exception $e) {
            return response()->json($e, 500);
        }
    }
    public function getContactsByBusinessNumber($draftID) {
        $address = DB::table('general.businesscontacts')->select('*')->where(['status' => 'Draft', 'BusinessNumber' => $draftID])->get();
        return response()->json($address);
    }
    public function deleteContactByID(Request $request) {
        DB::table('general.businesscontacts')->where('ID', $request->id)->delete();
        return response()->json('Data has been deleted!');
    }
    public function updateContact(Request $request) {
        try {
            $updateData = array(
                'BusinessNumber' => $request->draftID,
                'Number' => $request->mobileNumber,
                'EmailAdd' => $request->email,
                'Prefix' => $request->prefix_name,
                'FirstName' => $request->firstName,
                'LastName' => $request->lastName,
                'Suffix' => $request->suffix,
                'nickname' => $request->nickName,
                'Department' => $request->department,
                'MobileNoSubs' => $request->mobileNumberCheckbox,
                'EmailAddsSubs' => $request->emailCheckbox,
                'WorksFromID' => $request->worksFromID,
                'WorksFrom' => $request->worksFromName,
                'Remarks' => $request->notes,
                'Status' => 'Draft',
                'GUID' => $this->getGuid(),
                'ContactName' => $request->prefix_name. ' ' .$request->firstName. ' ' .$request->lastName. ' ' .$request->suffix,
                'Position' => $request->designation,
            );

            DB::table('general.businesscontacts')->where('ID', $request->id) ->limit(1)->update($updateData);
            return response()->json('Data has been updated');
            // return response()->json($updateData);
        } catch (\Exception $e) {
            return response()->json($e, 500);
        }
    }
    //save master vendor 
    public function saveBusinessList(Request $request) {
        DB::beginTransaction();
        try {
            $updateBusinessListData = array(
                'business_fullname' => $request->businessName,
                'title_id' => $request->companyId,
                'status' => "Active",
                'website' => $request->website,
                'Vendor_Type' => $request->vendorType,
                'InBusinessSince' => $request->inBusinessSince,
                'term_type' => $request->paymentTerms,
                'credit_limit' => $request->creditLimit,
                'default_currency' => $request->currency,
                'vat' => $request->vatStatus,
                'tin_number' => $request->tinNumber,
                '_2303' => $request->twentyThreeZeroThree,
                'secnumber' => $request->secNumber,
                'notes' => $request->notes,
                'business_type' => $request->businessType,
                'business_nature' => $request->businessNature,
                'AuthorizedCapitalStock' => $request->authorizedCapitalStock,
                'PaidUpCapitalStock' => $request->paidCapitalStock,
                'BusinessDescription' => $request->descriptionLineOfBusiness,
                'ContactPersonPurcID' => $request->contactPersonID,
                'ContactPersonPurcName' => $request->contactPersonName,
                'POSentVia' => $request->poSentVia,
                'OrderingPref' => $request->orderingPreferences,
                'ShippingRef' => $request->shippingPreferences,
                'DocumentRef' => $request->documentationPreferences,
                'PaymentRef' => $request->paymentPreferences,
                'ATC' => $request->atc,
                'WarrantyInfo' => $request->warrantyInformation,
                'RMAPolicy' => $request->rmaPolicy,
            );
            DB::table('general.business_list')->where('Business_Number', $request->draftID) ->limit(1)->update($updateBusinessListData);

            $boArray = $request->businessOfficer;
            $boArray = json_decode($boArray, true);
            if (count($boArray) > 0) {
                for ($i = 0; $i < count($boArray); $i++) {
                    $insertBusinessOfficerData[] = [
                        'business_number' => $request->draftID,
                        'Prefix'          => $boArray[$i]['prefix_name'],
                        'FName'           => $boArray[$i]['firstName'],
                        'Lname'           => $boArray[$i]['lastName'],
                        'Suffix'          => $boArray[$i]['suffix'],
                        'Designation'     => $boArray[$i]['designation'],
                        'Encodeby'       => $request->loggedUserId,
                    ];
                }
                DB:: table('general.business_officer')->insert($insertBusinessOfficerData);
            }

            $insertBusinessProductOfferedData = array(
                'business_number' => $request->draftID,
                'brand' => $request->brand,
                'product_line' => $request->productLine,
                'encodeby' => $request->loggedUserId,
            );
            DB::table('general.business_product_offered')->insert($insertBusinessProductOfferedData);

            $insertBusinessServiceOfferedData = array(
                'business_number' => $request->draftID,
                'service' => $request->services,
                'encodeby' => $request->loggedUserId,
            );
            DB::table('general.business_service_offered')->insert($insertBusinessServiceOfferedData);

            $bbiArray = $request->businessBankInfo;
            $bbiArray = json_decode($bbiArray, true);
            if (count($bbiArray) > 0) {
                for ($i = 0; $i < count($bbiArray); $i++) {
                    $insertBusinessBankInfoData[] = [
                        'business_number' => $request->draftID,
                        'bankname'          => $bbiArray[$i]['bankName'],
                        'account_num'           => $bbiArray[$i]['bankAcctNumber'],
                        'branch'           => $bbiArray[$i]['bankBranch'],
                        'currency'          => $bbiArray[$i]['bank_currency_name'],
                        'swiftcode'     => $bbiArray[$i]['bankSwiftCode'],
                        'preferbank'     => $bbiArray[$i]['bankPreferred'] == false ? 'False' : 'True',
                        'Encodeby'       => $request->loggedUserId,
                    ];
                }
                DB:: table('general.business_bankinfo')->insert($insertBusinessBankInfoData);
            }

            $rvArray = $request->relatedVendor;
            $rvArray = json_decode($rvArray, true);
            if (count($rvArray) > 0) {
                for ($i = 0; $i < count($rvArray); $i++) {
                    $insertRelatedVendorData[] = [
                        'business_number' => $request->draftID,
                        'relation'          => $rvArray[$i]['vendor_relationship_name'],
                        'vendorid'           => $rvArray[$i]['vendor_id'],
                        'vendor_code'           => $rvArray[$i]['vendor_code'],
                        'vendor_name'          => $rvArray[$i]['vendor_name']
                    ];
                }
                DB:: table('general.business_ralated_vendor')->insert($insertRelatedVendorData);
            }

            $rcArray = $request->relatedCustomer;
            $rcArray = json_decode($rcArray, true);
            if (count($rcArray) > 0) {
                for ($i = 0; $i < count($rcArray); $i++) {
                    $insertRelatedCustomerData[] = [
                        'business_number' => $request->draftID,
                        'relation'          => $rcArray[$i]['customer_relationship_name'],
                        'customerid'           => $rcArray[$i]['customer_id'],
                        'customer_code'           => $rcArray[$i]['customer_code'],
                        'customer_name'          => $rcArray[$i]['customer_name']
                    ];
                }
                DB:: table('general.business_ralated_customer')->insert($insertRelatedCustomerData);
            }

            $bstArray = $request->salesTarget;
            $bstArray = json_decode($bstArray, true);
            if (count($bstArray) > 0) {
                for ($i = 0; $i < count($bstArray); $i++) {
                    $insertSalesTargetData[] = [
                        'business_number' => $request->draftID,
                        'MonthStart'          => $bstArray[$i]['monthStart'],
                        'MonthEnd'           => $bstArray[$i]['monthEnd'],
                        'AnnualTarget'           => $bstArray[$i]['annualTarget'],
                        'Currency'          => $bstArray[$i]['currency_name'],
                        'TotalOrder'          => $bstArray[$i]['totalOrder'],
                        'Encodeby'          => $request->loggedUserId
                    ];
                }
                DB:: table('general.business_sales_target')->insert($insertSalesTargetData);
            }

            DB:: commit();
            return response()->json(["status" => "success", "message" => "Data has been saved"]);
        } catch (\Exception $e) {
            DB:: rollback();
            return response()->json($e, 500);
        }
    }
    // save master vendor attachments
    public function saveVendorAttachment(Request $request) {
        $this->insertVendorAttachments($request);
    }
}
