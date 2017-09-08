<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ApiController extends Controller
{

    public function help(): View
    {
        return view('api.index');
    }

}
