<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait GeneralTrait
{
  public function getBusinesses($companyId){
    $businesses = DB::select("SELECT a.`Business_Number` AS code, a.`business_fullname` AS 'name' FROM general.`business_list` a WHERE a.`status` LIKE 'Active%' AND a.`title_id` = $companyId AND a.`Type` = 'CLIENT' ORDER BY a.`business_fullname` ASC");
    return $businesses;
  }
}