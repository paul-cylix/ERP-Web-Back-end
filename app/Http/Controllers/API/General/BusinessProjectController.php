<?php

namespace App\Http\Controllers\API\General;

use App\Http\Controllers\ApiController;
use App\Models\General\BusinessList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessProjectController extends ApiController
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
     * @param  \App\Models\General\BusinessList  $businessList
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $setupProject = BusinessList::find($id)->setupProject;
        return $this->showAll($setupProject);
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\General\BusinessList  $businessList
     * @return \Illuminate\Http\Response
     */
    public function edit(BusinessList $businessList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\General\BusinessList  $businessList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BusinessList $businessList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\General\BusinessList  $businessList
     * @return \Illuminate\Http\Response
     */
    public function destroy(BusinessList $businessList)
    {
        //
    }
}
