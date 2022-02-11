<?php

namespace App\Http\Controllers\API\General;

use App\Http\Controllers\ApiController;
use App\Models\General\ActualSign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActualSignController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = ActualSign::all();
        return $this->showAll($data);
    }

    public function getActualsign($id,$frmname,$compid){
        $post = DB::select("SELECT * FROM general.`actual_sign` a WHERE a.`PROCESSID` = $id AND a.`FRM_NAME` = '".$frmname."' AND a.`COMPID` = $compid");
        return response()->json($post);
    }


}
