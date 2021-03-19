<?php

namespace App\Http\Livewire\Simulations;

use App\Models\Simulation;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['receivedConfirmation'];

    public $sortColumn = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $searchColumns = [
        'name'   => '',
        'status' => -1,
    ];
    public $displayingLog = false;
    public $currentSimulationId;

    public function sortByColumn($column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->reset('sortDirection');
            $this->sortColumn = $column;
        }
    }

    public function updating($value, $name): void
    {
        $this->resetPage();
    }

    private function buildQuery(): Builder
    {
        $simulations = Simulation::visible()->select(
            [
                'id',
                'name',
                'status',
                'created_at',
            ]
        );
        foreach ($this->searchColumns as $column => $value) {
            if ($column === 'name' && !empty($value)) {
                $simulations->where($column, 'LIKE', '%' . $value . '%');
            } elseif ($column === 'status' && in_array((int)$value, Simulation::VALID_STATES, true)) {
                $simulations->where($column, (int)$value);
            }
        }
        $simulations->orderBy($this->sortColumn, $this->sortDirection);

        return $simulations;
    }

    public function displayLogs(int $simulationId): void
    {
        $this->displayingLog = true;
        $this->currentSimulationId = $simulationId;
    }

    /**
     * Confirm that the given simulation should be deleted.
     *
     * @param  int  $simulationId
     *
     * @return void
     */
    public function confirmSimulationDeletion(int $simulationId): void
    {
        $this->dispatchBrowserEvent(
            'swal:confirm',
            [
                'type'  => 'delete',
                'icon'  => 'warning',
                'title' => __('Delete Simulation'),
                'text'  => __('Are you sure you would like to delete this simulation?'),
                'id'    => $simulationId,
            ]
        );
    }

    /**
     * Confirm that the given simulation should be submitted.
     *
     * @param  int  $simulationId
     *
     * @return void
     */
    public function confirmSimulationSubmission(int $simulationId): void
    {
        $this->dispatchBrowserEvent(
            'swal:confirm',
            [
                'type'  => 'submit',
                'icon'  => 'info',
                'title' => __('Submit Simulation'),
                'text'  => __('Are you sure you wish to submit this simulation?'),
                'id'    => $simulationId,
            ]
        );
    }

    /**
     * Confirm that the given simulation should be resubmitted.
     *
     * @param  int  $simulationId
     *
     * @return void
     */
    public function confirmSimulationReSubmission(int $simulationId): void
    {
        $this->dispatchBrowserEvent(
            'swal:confirm',
            [
                'type'  => 'resubmit',
                'icon'  => 'info',
                'title' => __('Submit Simulation'),
                'text'  => __('Are you sure you wish to submit this simulation?'),
                'id'    => $simulationId,
            ]
        );
    }

    /**
     * Delete the simulation.
     *
     * @param  \App\Models\Simulation  $simulation
     * @param  string  $type
     *
     * @return void
     * @throws \Exception
     */
    public function receivedConfirmation(Simulation $simulation, string $type): void
    {
        if ($type === 'delete' && $simulation->canBeDeleted()) {
            $simulation->deleteJobDirectory();
            $simulation->delete();
        } elseif ($type === 'submit' && $simulation->isReady()) {
            $simulation->submit();
        } elseif ($type === 'resubmit' && $simulation->isFailed()) {
            $simulation->reSubmit();
        }
    }

    public function render(): Factory|View|Application
    {
        $simulations = $this->buildQuery();

        return view(
            'livewire.simulations.index',
            [
                'simulations'       => $simulations->paginate($this->perPage),
                'currentSimulation' => ($this->displayingLog) ? Simulation::where('id', $this->currentSimulationId)->firstOrFail() : null,
            ]
        );
    }
}
