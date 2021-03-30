<?php

namespace App\Http\Livewire\Simulations\Pathways;

use App\Models\Simulation;
use App\PHENSIM\Reader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public Simulation $simulation;
    public string $pathway;
    public $sortColumn = 'FDR';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $searchColumns = [
        'nodeId'              => '',
        'nodeName'            => '',
        'isEndpoint'          => '',
        'activityScore'       => [
            'operator' => '=',
            'value'    => '',
        ],
        'averagePerturbation' => [
            'operator' => '=',
            'value'    => '',
        ],
        'pValue'              => [
            'operator' => '=',
            'value'    => '',
        ],
        'FDR'                 => [
            'operator' => '=',
            'value'    => '',
        ],
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

    /**
     * Get the data and apply all filters
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     * @throws \App\Exceptions\FileSystemException
     * @throws \JsonException
     */
    private function getNodes(): LengthAwarePaginator
    {
        $reader = new Reader($this->simulation->output_file);
        $nodes = $reader->readPathway($this->pathway);
        foreach ($this->searchColumns as $column => $value) {
            if (is_array($value) && $value['value'] !== '') {
                $nodes = $nodes->where($column, $value['operator'], (double)$value['value']);
            }

            if (!is_array($value)) {
                if ($column === 'isEndpoint' && $value !== '') {
                    $nodes = $nodes->where($column, (bool)$value);
                } elseif (!empty($value)) {
                    $nodes = $nodes->filter(fn($data) => (false !== stripos($data[$column], $value)));
                }
            }
        }
        $nodes = $nodes->sortBy($this->sortColumn, SORT_REGULAR, $this->sortDirection === 'desc');

        return $nodes->paginate($this->perPage);
    }

    /**
     * Render this component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
     * @throws \App\Exceptions\FileSystemException
     * @throws \JsonException
     */
    public function render(): Factory|View|Application
    {
        return view(
            'livewire.simulations.pathways.show',
            [
                'nodes' => $this->getNodes(),
            ]
        );
    }
}
