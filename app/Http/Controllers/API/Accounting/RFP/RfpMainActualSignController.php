<?php

namespace App\Http\Controllers\API\Accounting\RFP;

use App\Http\Controllers\ApiController;
use App\Models\Accounting\RFP\RfpMain;
use Illuminate\Http\Request;

class RfpMainActualSignController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
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
     * @param  \App\Models\Accunting\RFP\RfpMain  $rfpMain
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $actualSign = RfpMain::
        findOrFail($id)->actualSign->where('FRM_NAME','=','Request for Payment');
        return $this->showAll($actualSign);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Accunting\RFP\RfpMain  $rfpMain
     * @return \Illuminate\Http\Response
     */
    public function edit(RfpMain $rfpMain)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accunting\RFP\RfpMain  $rfpMain
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RfpMain $rfpMain)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Accunting\RFP\RfpMain  $rfpMain
     * @return \Illuminate\Http\Response
     */
    public function destroy(RfpMain $rfpMain)
    {
        //
    }
}
