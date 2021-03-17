<?php


namespace App\Services;


use App\Exceptions\ServiceException;
use App\Models\Simulation;
use JetBrains\PhpStorm\ArrayShape;

class DashboardService
{

    /**
     * Count all simulations grouped by their state
     *
     * @return int[]
     */
    #[ArrayShape([
        Simulation::QUEUED     => "int",
        Simulation::PROCESSING => "int",
        Simulation::COMPLETED  => "int",
        Simulation::FAILED     => "int",
    ])] public function getCounts()
    {
        $user = auth()->user();
        if ($user === null) {
            throw new ServiceException('User is not logged in');
        }
        $data = Simulation::visible()
                          ->groupBy('status')
                          ->selectRaw('status, count(*) as count')
                          ->pluck('count', 'status');

        return [
            Simulation::QUEUED     => $data[Simulation::QUEUED] ?? 0,
            Simulation::PROCESSING => $data[Simulation::PROCESSING] ?? 0,
            Simulation::COMPLETED  => $data[Simulation::COMPLETED] ?? 0,
            Simulation::FAILED     => $data[Simulation::FAILED] ?? 0,
        ];
    }

    public function getLatestUpdates(): array
    {
        //@todo
        return [];
    }
}
