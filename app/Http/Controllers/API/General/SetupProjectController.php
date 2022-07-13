<?php

namespace App\Http\Controllers\API\General;

use App\Http\Controllers\ApiController;
use App\Models\General\SetupProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SetupProjectController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = SetupProject::select('project_id','project_name')
        ->where('project_type','!=','MAIN OFFICE')
        ->where('status','=','Active')
        ->where('title_id','=','1')
        ->orderBy('project_name')
        ->get();
        return $this->showAll($data);
    }

    public function getprojects($companyId) {
        $data = SetupProject::select('project_id','project_name')
        ->where('project_type','!=','MAIN OFFICE')
        ->where('status','=','Active')
        ->where('title_id','=',$companyId)
        ->orderBy('project_name')
        ->get();
        return $this->showAll($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\General\SetupProject  $setupProject
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $response = DB::select("SELECT * FROM general.`setup_project` a
        INNER JOIN general.`business_list` b
        ON a.`ClientID` = b.`Business_Number`
        WHERE a.`SOID` = '".$id."'");
        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\General\SetupProject  $setupProject
     * @return \Illuminate\Http\Response
     */
    public function edit(SetupProject $setupProject)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\General\SetupProject  $setupProject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SetupProject $setupProject)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\General\SetupProject  $setupProject
     * @return \Illuminate\Http\Response
     */
    public function destroy(SetupProject $setupProject)
    {
        //
    }
}
