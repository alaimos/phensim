<?php

namespace App\Http\Controllers;

use App\Models\Simulation;
use App\PHENSIM\Reader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class PathwayController extends Controller
{
    /**
     * Display a pathway
     *
     * @param  \App\Models\Simulation  $simulation
     * @param  string  $pathway
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \App\Exceptions\FileSystemException
     * @throws \JsonException
     */
    public function show(Simulation $simulation, string $pathway): Factory|View|Application
    {
        abort_if(!auth()->user()->is_admin && $simulation->user_id !== auth()->id(), 403);
        abort_unless((new Reader($simulation->output_file))->hasPathway($pathway), 404);

        return view('simulations.pathways.show', compact('simulation', 'pathway'));
    }
}
