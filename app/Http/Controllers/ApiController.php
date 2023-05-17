<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use App\Traits\AccountingTrait;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Traits\HumanResourceTrait;

class ApiController extends Controller
{
    use ApiResponser, AccountingTrait, GeneralTrait, HumanResourceTrait;
}
