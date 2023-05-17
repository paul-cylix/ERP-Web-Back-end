<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use App\Models\General\SetupProject;

trait GeneralTrait
{
  public function getBusinesses($companyId){
    $businesses = DB::select("SELECT a.`Business_Number` AS code, a.`business_fullname` AS 'name' FROM general.`business_list` a WHERE a.`status` LIKE 'Active%' AND a.`title_id` = $companyId AND a.`Type` = 'CLIENT' ORDER BY a.`business_fullname` ASC");
    return $businesses;
  }

  public function getClient($prjid)
  {
      $client = DB::select("SELECT Business_Number as 'clientID', ifnull(business_fullname, '') AS 'clientName', (SELECT Main_office_id FROM general.`setup_project` WHERE `project_id` = '" . $prjid . "' LIMIT 1) as 'mainID' FROM general.`business_list` WHERE Business_Number IN (SELECT `ClientID` FROM general.`setup_project` WHERE `project_id` = '" . $prjid . "')");
      return $client;
  }

  public function getReportingManagers($loggedUserId)
  {
      $mgrs = DB::select("SELECT RMID as 'code', RMName as 'name' FROM general.`systemreportingmanager` WHERE UID = $loggedUserId ORDER BY RMName");
      return $mgrs;
  }

  public function getProjectsList()
  {
      $projects = SetupProject::select('project_id AS code','project_name AS name')
      ->where('project_type','!=','MAIN OFFICE')
      ->where('status','=','Active')
      ->orderBy('project_name')
      ->get();
      return $projects;
  }

  public function getProjectsListAndSoid($companyId) 
  {
    $projects = SetupProject::select('project_id AS code','project_name AS name','SOID')
      ->where('project_type','!=','MAIN OFFICE')
      ->where('status','=','Active')
      ->where('title_id','=',$companyId)
      ->orderBy('project_name')
      ->get();
      return $projects;
  }




}