<?php


namespace App\Services;


use App\Exceptions\ServiceException;
use App\Models\Message;
use App\Models\Simulation;
use Illuminate\Database\Eloquent\Collection;
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
    ])] public function getCounts(): array
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

    /**
     * Get the latest updates
     *
     * @return \Illuminate\Database\Eloquent\Collection|Message[]
     */
    public function getLatestUpdates(): Collection|array
    {
        return Message::latest()->take(5)->get();
    }
}
