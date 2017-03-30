<?php

namespace App\Http\Controllers\Simulation;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Organism;
use Datatables;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SubmitController extends Controller
{

    /**
     * @return \Illuminate\View\View
     */
    public function submitSimple(): View
    {
        return view('simulation.submit_simple', [
            'organisms' => Organism::all(),
            
        ]);
    }

    public function submitEnriched(): View
    {
        return view('simulation.submit_enriched', [

        ]);
    }

}
