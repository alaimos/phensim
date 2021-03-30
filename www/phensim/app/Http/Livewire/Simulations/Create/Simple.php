<?php

namespace App\Http\Livewire\Simulations\Create;

use App\Jobs\SimulationJob;
use App\Models\Node;
use App\Models\Organism;
use App\Models\Simulation;
use App\PHENSIM\Launcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Redirector;
use Livewire\WithPagination;

class Simple extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['simulationSubmit'];

    public $organisms;
    public array $state = [
        'name'           => '',
        'organism'       => '',
        'nodes'          => [
            'over'         => [],
            'under'        => [],
            'nonExpressed' => [],
            'knockout'     => [],
        ],
        'epsilon'        => 0.001,
        'seed'           => '',
        'fdr'            => Launcher::FDR_BH,
        'reactome'       => false,
        'fast'           => true,
        'miRNAs'         => true,
        'miRNAsEvidence' => Launcher::EVIDENCE_STRONG,
    ];
    public $sortColumn = 'name';
    public $sortDirection = 'asc';
    public $perPage = 5;
    public $searchColumns = [
        'accession' => '',
        'name'      => '',
    ];

    /**
     * Handle all the logic of the simulation parameters selection table
     *
     * @return mixed
     */
    private function handleNodes(): mixed
    {
        if ($this->state['organism']) {
            $nodes = Node::where('organism_id', $this->state['organism'])->select(
                [
                    'name',
                    'accession',
                ]
            );
            foreach ($this->searchColumns as $column => $value) {
                if (!empty($value)) {
                    $nodes->where($column, 'LIKE', '%' . $value . '%');
                }
            }
            $nodes->orderBy($this->sortColumn, $this->sortDirection);

            return $nodes->paginate($this->perPage);
        }

        return null;
    }

    /**
     * Validation rules for the form
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'state.name'                 => ['required', 'max:255'],
            'state.organism'             => ['required', Rule::exists('organisms', 'id')],
            'state.nodes.over'           => ['filled', 'array'],
            'state.nodes.over.*'         => [Rule::exists('nodes', 'accession')],
            'state.nodes.under'          => ['filled', 'array'],
            'state.nodes.under.*'        => [Rule::exists('nodes', 'accession')],
            'state.nodes.nonExpressed'   => ['array'],
            'state.nodes.nonExpressed.*' => [Rule::exists('nodes', 'accession')],
            'state.nodes.knockout'       => ['array'],
            'state.nodes.knockout.*'     => [Rule::exists('nodes', 'accession')],
            'state.epsilon'              => ['numeric'],
            'state.seed'                 => ['numeric'],
            'state.fdr'                  => ['string', Rule::in(Launcher::SUPPORTED_FDRS)],
            'state.reactome'             => ['boolean'],
            'state.fast'                 => ['boolean'],
            'state.miRNAs'               => ['boolean'],
            'state.miRNAsEvidence'       => ['string', Rule::in(Launcher::SUPPORTED_EVIDENCES)],
        ];
    }

    /**
     * Loads the list of organisms when the component is mounted
     */
    public function mount(): void
    {
        $this->organisms = Organism::select(['id', 'name'])->pluck('name', 'id');
    }

    /**
     * Save the simulation
     */
    public function save(): void
    {
        $validatedData = $this->validate();

        $state = $validatedData['state'];

        if (empty($state['nodes']['over']) && empty($state['nodes']['under'])) {
            session()->flash('error', 'You must choose at least one overexpressed/underexpressed node!');
        } else {
            $simulation = Simulation::create(
                [
                    'name'        => $state['name'],
                    'user_id'     => auth()->id(),
                    'organism_id' => $state['organism'],
                    'status'      => Simulation::READY,
                    'parameters'  => [
                        'inputParameters' => [
                            Launcher::OVEREXPRESSION  => $state['nodes']['over'],
                            Launcher::UNDEREXPRESSION => $state['nodes']['under'],
                        ],
                        'epsilon'         => (!empty($state['epsilon'])) ? $state['epsilon'] : 0.001,
                        'seed'            => (!empty($state['seed'])) ? $state['seed'] : null,
                        'fdr'             => $state['fdr'],
                        'reactome'        => $state['reactome'],
                        'fast'            => $state['fast'],
                        'enrichMiRNAs'    => $state['miRNAs'],
                        'miRNAsEvidence'  => $state['miRNAsEvidence'] ?? Launcher::EVIDENCE_STRONG,
                        'nonExpressed'    => $state['nodes']['nonExpressed'],
                        'remove'          => $state['nodes']['knockout'],
                    ],
                ]
            );
            $this->dispatchBrowserEvent(
                'swal:confirm:submit',
                [
                    'type'  => 'success',
                    'title' => 'Simulation created!',
                    'text'  => __('Do you want to submit the job?'),
                    'id'    => $simulation->id,
                ]
            );
        }
    }

    /**
     * Submit the simulation and redirect the user
     *
     * @param  \App\Models\Simulation  $simulation
     * @param  bool  $submit
     *
     * @return \Livewire\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function simulationSubmit(Simulation $simulation, bool $submit): Redirector|RedirectResponse
    {
        if ($submit) {
            $simulation->submit();
        }

        return redirect()->route('simulations.index');
    }

    /**
     * Set the sorting of a column for the parameters selection table
     *
     * @param  string  $column
     */
    public function sortByColumn(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->reset('sortDirection');
            $this->sortColumn = $column;
        }
    }

    /**
     * Handles the selection toggling for the parameters selection table
     *
     * @param  string  $accession
     * @param  string  $type
     */
    public function toggleSelection(string $accession, string $type): void
    {
        if (($position = array_search($accession, $this->state['nodes'][$type], true)) !== false) {
            array_splice($this->state['nodes'][$type], $position, 1);
        } else {
            $this->state['nodes'][$type][] = $accession;
        }
    }

    /**
     * If the organism changes the state should be reset
     *
     * @param $value
     * @param $name
     */
    public function updating($value, $name): void
    {
        if ($value === 'state.organism') {
            $this->state['nodes'] = [
                'over'         => [],
                'under'        => [],
                'nonExpressed' => [],
                'knockout'     => [],
            ];
            $this->resetPage();
        }
    }

    /**
     * Render this component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
     */
    public function render(): Factory|View|Application
    {
        return view(
            'livewire.simulations.create.simple',
            [
                'nodes'      => $this->handleNodes(),
                'isSelected' => function ($accession, $type) {
                    return (in_array($accession, $this->state['nodes'][$type], true));
                },
                'canBeShown' => function ($accession, $type) {
                    foreach ($this->state['nodes'] as $paramType => $selection) {
                        if ($paramType !== $type && in_array($accession, $selection, true)) {
                            return false;
                        }
                    }

                    return true;
                },
            ]
        );
    }
}
