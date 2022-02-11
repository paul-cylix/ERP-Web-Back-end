<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ApiController extends Controller
{
    use ApiResponser;
}
