<?php

namespace App\Http\Controllers\API\Accounting;

use App\Http\Controllers\ApiController;
use App\Models\Accounting\CurrencySetup;
use Illuminate\Http\Request;

class CurrencySetupController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = CurrencySetup::all();
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
     * @param  \App\Models\Accounting\CurrencySetup  $currencySetup
     * @return \Illuminate\Http\Response
     */
    public function show(CurrencySetup $currencySetup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Accounting\CurrencySetup  $currencySetup
     * @return \Illuminate\Http\Response
     */
    public function edit(CurrencySetup $currencySetup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accounting\CurrencySetup  $currencySetup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CurrencySetup $currencySetup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Accounting\CurrencySetup  $currencySetup
     * @return \Illuminate\Http\Response
     */
    public function destroy(CurrencySetup $currencySetup)
    {
        //
    }
}
