<?php

namespace App\Jobs;

use App\Models\Simulation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SimulationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * A simulation associated to this job
     *
     * @var \App\Models\Simulation
     */
    protected Simulation $simulation;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\Simulation  $simulation
     */
    public function __construct(Simulation $simulation)
    {
        $this->simulation = $simulation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            // Delete this job from the queue. If the job fails no other attempts should be made
            $this->delete();
            if (in_array($this->simulation->status, [Simulation::PROCESSING, Simulation::COMPLETED], true)) {
                // This job has been completed or is being processed! Why am I running?
                return;
            }
            $this->simulation->update(['logs' => '', 'status' => Simulation::PROCESSING]);
            //@todo prepare PHENSIM input file, run the algorithm, process the results and save the job
            $this->simulation->update(['status' => Simulation::COMPLETED]);
        } catch (Throwable $e) {
            $this->simulation->status = Simulation::FAILED;
            $this->simulation->appendLog("\nAn error occurred: ".$e->getMessage());
            $this->fail($e);
        }
    }
}
