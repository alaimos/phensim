<?php

namespace App\Http\Livewire\Simulations;

use App\Models\Simulation;
use App\PHENSIM\Reader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public Simulation $simulation;
    public $sortColumn = 'pathwayFDR';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $searchColumns = [
        'pathwayId'                  => '',
        'pathwayName'                => '',
        'averagePathwayPerturbation' => [
            'operator' => '=',
            'value'    => '',
        ],
        'pathwayActivityScore'       => [
            'operator' => '=',
            'value'    => '',
        ],
        'pathwayPValue'              => [
            'operator' => '=',
            'value'    => '',
        ],
        'pathwayFDR'                 => [
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
    private function getPathways(): LengthAwarePaginator
    {
        $reader = new Reader($this->simulation->output_file);
        $pathways = $reader->readPathwaysList();
        foreach ($this->searchColumns as $column => $value) {
            if (is_array($value) && $value['value'] !== '') {
                $pathways = $pathways->where($column, $value['operator'], (double)$value['value']);
            } elseif (!is_array($value) && !empty($value)) {
                $pathways = $pathways->filter(fn($data) => (false !== stripos($data[$column], $value)));
            }
        }
        $pathways = $pathways->sortBy($this->sortColumn, SORT_REGULAR, $this->sortDirection === 'desc');

        return $pathways->paginate($this->perPage);
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
            'livewire.simulations.show',
            [
                'pathways' => $this->getPathways(),
            ]
        );
    }
}
