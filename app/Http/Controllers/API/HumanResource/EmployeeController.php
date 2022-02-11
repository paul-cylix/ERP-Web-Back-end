<?php

namespace App\Http\Controllers\API\HumanResource;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\HumanResource\Employee;
use Illuminate\Http\Request;
use App\Models\User;

class EmployeeController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return 'test';
        $data = Employee::all();
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
     * @param  \App\Models\HumanResource\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Employee::findOrFail($id)->user;
        return $this->showOne($user);
        // dd($user);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HumanResource\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HumanResource\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HumanResource\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        //
    }
}
