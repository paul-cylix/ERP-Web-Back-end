<?php

namespace App\Traits;
use Illuminate\Support\Facades\DB;


trait HumanResourceTrait
{

  public function getOt($processId) 
  {
    $ot = DB::select("SELECT *,(SELECT b.`project_name` FROM general.`setup_project` b WHERE b.`project_id` = a.`PRJID` ) AS 'PRJNAME', (SELECT c.`id` FROM general.`users` c WHERE c.`UserFull_name` = a.`reporting_manager` LIMIT 1) AS 'IDOFRM' FROM humanresource.`overtime_request` a WHERE a.`main_id` = '".$processId."' AND a.`status` <> 'Removed'");
    return $ot;
  }


}