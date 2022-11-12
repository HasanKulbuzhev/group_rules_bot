<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public function welcome()
    {
        return view('welcome');
    }
}
