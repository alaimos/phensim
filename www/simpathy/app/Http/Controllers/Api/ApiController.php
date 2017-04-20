<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Pathway;
use App\SIMPATHY\Reader;
use Datatables;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\Datatables\Engines\CollectionEngine;

class ApiController extends Controller
{

    public function help(): View {
        return view('api.index');
    }

}
