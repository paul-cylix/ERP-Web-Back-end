<?php

namespace App\Http\Controllers\API\General;

use App\Http\Controllers\ApiController;
use App\Models\General\SetupProject;
use Illuminate\Http\Request;

class ProjectBusinessController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $setupProject = SetupProject::find($id);
        $businessList = $setupProject->businessList;
  
        // return response()->json($businessList);

        return $this->showOne($businessList);
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
