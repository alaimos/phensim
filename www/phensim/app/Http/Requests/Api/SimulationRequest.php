<?php

namespace App\Http\Requests\Api;

use App\PHENSIM\Launcher;
use App\PHENSIM\Utils;
use App\Rules\InputFileRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimulationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'                     => ['required', 'max:255'],
            'organism'                 => ['required', Rule::exists('organisms', 'accession')],
            'filter'                   => ['filled', 'string'],
            'epsilon'                  => ['filled', 'numeric'],
            'seed'                     => ['filled', 'numeric'],
            'fdr'                      => ['filled', 'string', Rule::in(Launcher::SUPPORTED_FDRS)],
            'reactome'                 => ['filled', 'boolean'],
            'fast'                     => ['filled', 'boolean'],
            'miRNAs'                   => ['filled', 'boolean'],
            'miRNAsEvidence'           => ['filled', 'string', Rule::in(Launcher::SUPPORTED_EVIDENCES)],
            'submit'                   => ['filled', 'boolean'],
            'callback'                 => ['filled', 'active_url'],
            'nodes.overExpressed'      => ['required_without_all:nodes.underExpressed,simulationParametersFile', 'array'],
            'nodes.overExpressed.*'    => [Rule::exists('nodes', 'accession')],
            'nodes.underExpressed'     => ['required_without_all:nodes.overExpressed,simulationParametersFile', 'array'],
            'nodes.underExpressed.*'   => [Rule::exists('nodes', 'accession')],
            'nodes.nonExpressed'       => ['filled', 'array'],
            'nodes.nonExpressed.*'     => [Rule::exists('nodes', 'accession')],
            'nodes.knockout'           => ['filled', 'array'],
            'nodes.knockout.*'         => [Rule::exists('nodes', 'accession')],
            'simulationParametersFile' => [
                'required_without_all:nodes.underExpressed,nodes.overExpressed',
                new InputFileRule(true, [Utils::class, 'checkInputFile']),
            ],
            'enrichmentDatabaseFile'   => ['filled', new InputFileRule(validationFunction: [Utils::class, 'checkDbFile'])],
            'nonExpressedNodesFile'    => ['filled', new InputFileRule(validationFunction: [Utils::class, 'checkListFile'])],
            'knockoutNodesFile'        => ['filled', new InputFileRule(validationFunction: [Utils::class, 'checkListFile'])],
            'customNodeTypesFile'      => ['filled', new InputFileRule(validationFunction: [Utils::class, 'checkNodeTypeFile'])],
            'customEdgeTypesFile'      => ['filled', new InputFileRule(validationFunction: [Utils::class, 'checkEdgeTypeFile'])],
            'customEdgeSubtypesFile'   => ['filled', new InputFileRule(validationFunction: [Utils::class, 'checkEdgeSubTypeFile'])],
        ];
    }
}
