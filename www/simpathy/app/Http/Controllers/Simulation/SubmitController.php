<?php

namespace App\Http\Controllers\Simulation;

use App\Http\Controllers\Controller;
use App\Models\Organism;
use Illuminate\View\View;

class SubmitController extends Controller
{

    /**
     * @return \Illuminate\View\View
     */
    public function submitSimple(): View
    {
        return view('simulation.submit_simple', [
            'organisms' => Organism::pluck('name', 'accession'),

        ]);
    }

    public function submitEnriched(): View
    {
        return view('simulation.submit_enriched', [

        ]);
    }

}
