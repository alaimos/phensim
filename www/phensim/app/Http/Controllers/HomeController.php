<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @param  \App\Services\DashboardService  $service
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(DashboardService $service): Factory|View|Application
    {
        $counts = $service->getCounts();
        return view('dashboard', compact('counts'));
    }
}
