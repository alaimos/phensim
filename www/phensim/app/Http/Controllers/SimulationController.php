<?php

namespace App\Http\Controllers;

use App\Models\Organism;
use App\Models\Simulation;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    /**
     * Display a simulation
     *
     * @param  \App\Models\Simulation  $simulation
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show(Simulation $simulation): Factory|View|Application
    {
        abort_if(!auth()->user()->is_admin && $simulation->user_id !== auth()->id(), 403);

        return view('simulations.show', compact('simulation'));
    }


}
