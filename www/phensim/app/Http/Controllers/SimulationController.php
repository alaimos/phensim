<?php

namespace App\Http\Controllers;

use App\Models\Simulation;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    /**
     * Download the input zip archive of a simulation
     *
     * @param  \App\Models\Simulation  $simulation
     *
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadInput(Simulation $simulation): RedirectResponse|BinaryFileResponse
    {
        abort_if(!auth()->user()->is_admin && $simulation->user_id !== auth()->id(), 403);

        $zipArchive = $simulation->output_file . '.zip';

        if (file_exists($zipArchive)) {
            $filename = $simulation->id . '-' . Str::slug($simulation->name) . '-input.zip';

            return response()->download($zipArchive, $filename);
        }

        return redirect()->route('simulations.show', $simulation)->with('download-status', 'Input ZIP archive does not exist.');
    }

    /**
     * Download the RAW results of a simulation
     *
     * @param  \App\Models\Simulation  $simulation
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadOutput(Simulation $simulation): BinaryFileResponse
    {
        abort_if(!auth()->user()->is_admin && $simulation->user_id !== auth()->id(), 403);

        $filename = $simulation->id . '-' . Str::slug($simulation->name) . '-output.tsv';

        return response()->download($simulation->output_file, $filename);
    }

    /**
     * Download the pathway matrix built from a simulation
     *
     * @param  \App\Models\Simulation  $simulation
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadPathway(Simulation $simulation): BinaryFileResponse
    {
        abort_if(!auth()->user()->is_admin && $simulation->user_id !== auth()->id(), 403);

        $filename = $simulation->id . '-' . Str::slug($simulation->name) . '-pathway-matrix.tsv';

        return response()->download($simulation->pathway_output_file, $filename);
    }

    /**
     * Download the nodes matrix built from a simulation
     *
     * @param  \App\Models\Simulation  $simulation
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadNode(Simulation $simulation): BinaryFileResponse
    {
        abort_if(!auth()->user()->is_admin && $simulation->user_id !== auth()->id(), 403);

        $filename = $simulation->id . '-' . Str::slug($simulation->name) . '-nodes-matrix.tsv';

        return response()->download($simulation->nodes_output_file, $filename);
    }

}
