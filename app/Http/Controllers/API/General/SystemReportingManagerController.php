<?php

namespace App\Http\Controllers\API\General;

use App\Http\Controllers\ApiController;
use App\Models\General\SystemReportingManager;
use Illuminate\Http\Request;

class SystemReportingManagerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = SystemReportingManager::all();
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
     * @param  \App\Models\General\SystemReportingManager  $systemReportingManager
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = SystemReportingManager::select('RMID','RMName')
            ->where('UID',$id)
            ->orderBy('RMName')
            ->get();
        return $this->showAll($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\General\SystemReportingManager  $systemReportingManager
     * @return \Illuminate\Http\Response
     */
    public function edit(SystemReportingManager $systemReportingManager)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\General\SystemReportingManager  $systemReportingManager
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SystemReportingManager $systemReportingManager)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\General\SystemReportingManager  $systemReportingManager
     * @return \Illuminate\Http\Response
     */
    public function destroy(SystemReportingManager $systemReportingManager)
    {
        //
    }
}
