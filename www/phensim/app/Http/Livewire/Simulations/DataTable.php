<?php

namespace App\Http\Livewire\Simulations;

use App\Models\Simulation;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class DataTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteSimulation'];

    public $categories = [];
    public $sortColumn = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $searchColumns = [
        'name'   => '',
        'status' => -1,
    ];

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
            'swal:confirm:delete',
            [
                'type'  => 'warning',
                'title' => __('Delete Simulation'),
                'text'  => __('Are you sure you would like to delete this simulation?'),
                'id'    => $simulationId,
            ]
        );
    }

    /**
     * Delete the simulation.
     *
     * @param  \App\Models\Simulation  $simulation
     *
     * @return void
     * @throws \Exception
     */
    public function deleteSimulation(Simulation $simulation): void
    {
        if ($simulation->canBeDeleted()) {
            $simulation->delete();
        }
    }

    public function render(): Factory|View|Application
    {
        $simulations = $this->buildQuery();

        return view(
            'livewire.simulations.data-table',
            [
                'simulations' => $simulations->paginate($this->perPage),
            ]
        );
    }
}
