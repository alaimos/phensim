<?php

namespace App\Http\Livewire\Simulations\Create;

use App\Models\Organism;
use App\Models\Simulation;
use App\PHENSIM\Launcher;
use App\PHENSIM\Utils;
use App\Rules\InputFileRule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Redirector;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Advanced extends Component
{
    use WithFileUploads;

    protected $listeners = ['simulationSubmit'];

    public array $state = [
        'name'           => '',
        'organism'       => '',
        'filter'         => '',
        'epsilon'        => 0.001,
        'seed'           => '',
        'fdr'            => Launcher::FDR_BH,
        'reactome'       => false,
        'fast'           => true,
        'miRNAs'         => true,
        'miRNAsEvidence' => Launcher::EVIDENCE_STRONG,
    ];


    /**
     * @var TemporaryUploadedFile
     */
    public $simulationParametersFile;
    /**
     * @var TemporaryUploadedFile
     */
    public $enrichmentDatabaseFile;
    /**
     * @var TemporaryUploadedFile
     */
    public $nonExpressedNodesFile;
    /**
     * @var TemporaryUploadedFile
     */
    public $knockoutNodesFile;
    /**
     * @var TemporaryUploadedFile
     */
    public $customNodeTypesFile;
    /**
     * @var TemporaryUploadedFile
     */
    public $customEdgeTypesFile;
    /**
     * @var TemporaryUploadedFile
     */
    public $customEdgeSubtypesFile;

    /**
     * Validation rules for the form
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'state.name'               => ['required', 'max:255'],
            'state.organism'           => ['required', Rule::exists('organisms', 'id')],
            'state.filter'             => ['string'],
            'state.epsilon'            => ['numeric'],
            'state.seed'               => ['numeric'],
            'state.fdr'                => ['string', Rule::in(Launcher::SUPPORTED_FDRS)],
            'state.reactome'           => ['boolean'],
            'state.fast'               => ['boolean'],
            'state.miRNAs'             => ['boolean'],
            'state.miRNAsEvidence'     => ['string', Rule::in(Launcher::SUPPORTED_EVIDENCES)],
            'simulationParametersFile' => [new InputFileRule(true, [Utils::class, 'checkInputFile'])],
            'enrichmentDatabaseFile'   => [new InputFileRule(validationFunction: [Utils::class, 'checkDbFile'])],
            'nonExpressedNodesFile'    => [new InputFileRule(validationFunction: [Utils::class, 'checkListFile'])],
            'knockoutNodesFile'        => [new InputFileRule(validationFunction: [Utils::class, 'checkListFile'])],
            'customNodeTypesFile'      => [new InputFileRule(validationFunction: [Utils::class, 'checkNodeTypeFile'])],
            'customEdgeTypesFile'      => [new InputFileRule(validationFunction: [Utils::class, 'checkEdgeTypeFile'])],
            'customEdgeSubtypesFile'   => [new InputFileRule(validationFunction: [Utils::class, 'checkEdgeSubTypeFile'])],
        ];
    }

    /**
     * Save the simulation
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \App\Exceptions\FileSystemException
     */
    public function save(): void
    {
        $validatedData = $this->validate();
        $state = $validatedData['state'];
        if ($this->knockoutNodesFile) {
            $knockout = array_filter(array_map("trim", explode("\n", $this->knockoutNodesFile->get())));
            $this->knockoutNodesFile->delete();
        } else {
            $knockout = [];
        }
        $simulation = Simulation::create(
            [
                'name'        => $state['name'],
                'user_id'     => auth()->id(),
                'organism_id' => $state['organism'],
                'status'      => Simulation::READY,
                'parameters'  => [
                    'epsilon'        => (!empty($state['epsilon'])) ? $state['epsilon'] : 0.001,
                    'seed'           => (!empty($state['seed'])) ? $state['seed'] : null,
                    'fdr'            => $state['fdr'],
                    'reactome'       => $state['reactome'],
                    'fast'           => $state['fast'],
                    'enrichMiRNAs'   => $state['miRNAs'],
                    'miRNAsEvidence' => $state['miRNAsEvidence'] ?? Launcher::EVIDENCE_STRONG,
                    'remove'         => $knockout,
                ],
            ]
        );
        $jobDirRelative = $simulation->jobDirectoryRelative();
        $jobDirAbsolute = $simulation->jobDirectory() . DIRECTORY_SEPARATOR;
        $filename = basename($this->simulationParametersFile->store($jobDirRelative));
        $simulation->input_parameters_file = $jobDirAbsolute . $filename;
        if ($this->enrichmentDatabaseFile) {
            $filename = basename($this->enrichmentDatabaseFile->store($jobDirRelative));
            $simulation->enrichment_database_file = $jobDirAbsolute . $filename;
        }
        if ($this->nonExpressedNodesFile) {
            $filename = basename($this->nonExpressedNodesFile->store($jobDirRelative));
            $simulation->non_expressed_nodes_file = $jobDirAbsolute . $filename;
        }
        if ($this->customNodeTypesFile) {
            $filename = basename($this->customNodeTypesFile->store($jobDirRelative));
            $simulation->node_types_file = $jobDirAbsolute . $filename;
        }
        if ($this->customEdgeTypesFile) {
            $filename = basename($this->customEdgeTypesFile->store($jobDirRelative));
            $simulation->edge_types_file = $jobDirAbsolute . $filename;
        }
        if ($this->customEdgeSubtypesFile) {
            $filename = basename($this->customEdgeSubtypesFile->store($jobDirRelative));
            $simulation->edge_subtypes_file = $jobDirAbsolute . $filename;
        }
        $simulation->save();

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
     * Render the form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
     */
    public function render(): Factory|View|Application
    {
        return view(
            'livewire.simulations.create.advanced',
            [
                'organisms' => Organism::select(['id', 'name'])->pluck('name', 'id'),
            ]
        );
    }
}
