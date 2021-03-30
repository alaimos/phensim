@extends('layouts.app', ['title' => __('User Manual')])

@section('content')
    <x-page-header class="col-lg-12" gradient="bg-gradient-orange">
        API User Manual
    </x-page-header>
    @php $url = url('/') @endphp
    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-body">
                        <h3>Introduction</h3>
                        <div class="mx-4">
                            <p>
                                This is a brief specification of PHENSIM REST APIs. To access APIs, you will need to
                                generate an access token from the <strong>My Profile</strong> panel that you can access
                                from the menu at the top-right corner of this page.
                            </p>
                            <p>
                                All Requests MUST contain the following headers for authentication:
                            </p>
                            <x-docs.api.code-block class="mx-4">
                                {{ "Accept: application/json\nAuthorization: Bearer YOUR_API_AUTHENTICATION_TOKEN" }}
                            </x-docs.api.code-block>
                        </div>
                        <h3 class="mt-4">HTTP Return Codes</h3>
                        <div class="mx-4">
                            <p>HTTP return codes are used to identify the request state:</p>
                            <x-docs.api.table :headers="[ 'Code', 'Description' ]" :rows="[
                                [200, 'Your request has been completed correctly. The payload will contain all the data.'],
                                [401, 'You are not authorized to use API. Did you forget your authentication token?'],
                                [403, 'You are not allowed to perform the specified action. The payload might contain more details.'],
                                [404, 'The resource you are looking for was not found. Did you specify the correct URL?'],
                                [422, 'Some validation error were found when sending your request. The payload will contain more details.'],
                                [500, 'An error occurred during processing. The payload will contain more details.'],
                            ]"></x-docs.api.table>
                        </div>
                        <h3 class="mt-4">API endpoints</h3>
                        <div class="mx-4">
                            <h3 class="mt-4">Test Endpoints</h3>
                            <div class="mx-4">
                                @include('docs.partials.action', [
        'title' => 'Test API Authentication',
        'description' => 'Tests whether your API token is working by returning the details about your user account.',
        'method' => 'GET',
        'url' => '/api/v1/ping',
        'queryParameters' => null,
        'postParameters' => null,
        'responseDescription' => 'An object containing your account details.',
        'responseParams' => [
            ['id', 'integer', 'An unique identifier assigned to your user account'],
            ['name', 'string', 'Your name'],
            ['email', 'string', 'Your e-mail address'],
            ['affiliation', 'string', 'Your affiliation'],
            ['created_at', 'string', 'The timestamp (date and time) of your account creation'],
            ['updated_at', 'string', 'The timestamp (date and time) of your account last update'],
        ],
        'example' => <<<JSON
{
    "id": 1,
    "name": "Saro Falsaperla",
    "email": "saro@falsaperla.it",
    "affiliation": "University of Catania",
    "created_at": "2021-03-16T09:10:35.000000Z",
    "updated_at": "2021-03-16T17:13:14.000000Z"
}
JSON
    ])
                            </div>
                            <h3 class="mt-4">Simulations Endpoints</h3>
                            <div class="mx-4">
                                @include('docs.partials.action', [
        'title' => 'List all simulations',
        'description' => 'List all simulations that are available for your account.',
        'method' => 'GET',
        'url' => '/api/v1/simulations',
        'queryParameters' => [
            ['per_page', 'integer', 'The maximum number of elements to display for each page (Optional, default=10)'],
            ['page', 'integer', 'The current page (OPTIONAL, default=0)'],
        ],
        'postParameters' => null,
        'responseDescription' => 'An object containing the results of your request.',
        'responseParams' => [
            ['data', 'array', 'An array containing simulations object'],
            ['data.*', 'object', 'A simulation object'],
            ['data.*.id', 'integer', 'A unique identifier assigned to a simulation'],
            ['data.*.name', 'string', 'The name of the simulation'],
            ['data.*.status', 'integer', 'A numeric value representing the state of the simulation (0=ready, 1=queued, 2=processing, 3=completed, 4=pending)'],
            ['data.*.readable_status', 'string', 'A human readable representation of the simulation state'],
            ['data.*.parameters', 'object', 'Simulation parameters (see "Create Simulation" endpoint for more details)'],
            ['data.*.logs', 'string', 'All messages reported by the PHENSIM algorithm during execution'],
            ['data.*.organism', 'string', 'The organism used for the simulation (the code is the KEGG accession number for the species)'],
            ['data.*.created_at', 'string', 'The timestamp (date and time) of simulation creation'],
            ['data.*.updated_at', 'string', 'The timestamp (date and time) of simulation last update'],
            ['data.*.links', 'object', 'An object containing links to API endpoints for simulation input/output files download (NULL if no file is available)'],
            ['data.*.links.input_parameters', 'string', 'API endpoint link for input parameters download'],
            ['data.*.links.enrichment_database', 'string', 'API endpoint link for enrichment database download'],
            ['data.*.links.node_types', 'string', 'API endpoint link for node types download'],
            ['data.*.links.edge_types', 'string', 'API endpoint link for edge types download'],
            ['data.*.links.edge_subtypes', 'string', 'API endpoint link for edge subtypes download'],
            ['data.*.links.non_expressed_nodes', 'string', 'API endpoint link for non-expressed nodes download'],
            ['data.*.links.removed_nodes', 'string', 'API endpoint link for knocked-out nodes download'],
            ['data.*.links.output_file', 'string', 'API endpoint link for output file download'],
            ['data.*.links.pathway_output', 'string', 'API endpoint link for output pathway matrix download'],
            ['data.*.links.nodes_output', 'string', 'API endpoint link for output nodes matrix download'],
            ['links', 'object', 'An object containing links to API endpoints for pagination of this request (NULL if the link is no pages are available)'],
            ['links.first', 'string', 'API endpoint link for the first page of the results'],
            ['links.last', 'string', 'API endpoint link for the last page of the results'],
            ['links.prev', 'string', 'API endpoint link for the previous page of the results'],
            ['links.next', 'string', 'API endpoint link for the next page of the results'],
            ['meta', 'object', 'An object containing pagination metadata'],
            ['meta.current_page', 'integer', 'The current page'],
            ['meta.from', 'integer', 'The first result index for this page'],
            ['meta.last_page', 'integer', 'The last page number'],
            ['meta.per_page', 'integer', 'The number of simulations per page'],
            ['meta.to', 'integer', 'The last result index for this page'],
            ['meta.total', 'integer', 'The total number of results'],
        ],
        'example' => <<<JSON
{
    "data": [
        {
            "id": 1,
            "name": "Title",
            "status": 3,
            "readable_status": "Completed",
            "parameters": {
                "fast": true,
                "fdr": "BH",
                "epsilon": 0.001,
                "seed": null,
                "reactome": false,
                "enrichMiRNAs": true,
                "miRNAsEvidence": null
            },
            "logs": "Log Messages goes here",
            "organism": "hsa",
            "created_at": "2021-03-17T21:13:44.000000Z",
            "updated_at": "2021-03-18T12:33:06.000000Z",
            "links": {
                "input_parameters": "$url/api/v1/simulations/1/download/input_parameters",
                "enrichment_database": null,
                "node_types": null,
                "edge_types": null,
                "edge_subtypes": null,
                "non_expressed_nodes": "$url/api/v1/simulations/1/download/non_expressed_nodes",
                "removed_nodes": null,
                "output_file": null,
                "pathway_output": "$url/api/v1/simulations/1/download/pathway_output",
                "nodes_output": "$url/api/v1/simulations/1/download/nodes_output"
            }
        },
        ...
    ],
    "links": {
        "first": "$url/api/v1/simulations?page=1",
        "last": "$url/api/v1/simulations?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "per_page": 10,
        "to": 7,
        "total": 7
    }
}
JSON
    ])
                                @include('docs.partials.action', [
        'title' => 'Get a simulation',
        'description' => <<<DESCRIPTION
Get all the details of a single simulation<br>
The <strong>{SIMULATION_ID}</strong> parameter in the url should be replaced with the unique identifier of the simulation.
If an invalid unique identifier is provided a <strong>404</strong> error will be returned.
DESCRIPTION,
        'method' => 'GET',
        'url' => '/api/v1/simulations/{SIMULATION_ID}',
        'queryParameters' => null,
        'postParameters' => null,
        'responseDescription' => 'An object containing the simulation data.',
        'responseParams' => [
            ['data', 'object', 'A simulation object'],
            ['data.id', 'integer', 'A unique identifier assigned to a simulation'],
            ['data.name', 'string', 'The name of the simulation'],
            ['data.status', 'integer', 'A numeric value representing the state of the simulation (0=ready, 1=queued, 2=processing, 3=completed, 4=pending)'],
            ['data.readable_status', 'string', 'A human readable representation of the simulation state'],
            ['data.parameters', 'object', 'Simulation parameters (see "Create Simulation" endpoint for more details)'],
            ['data.logs', 'string', 'All messages reported by the PHENSIM algorithm during execution'],
            ['data.organism', 'string', 'The organism used for the simulation (the code is the KEGG accession number for the species)'],
            ['data.created_at', 'string', 'The timestamp (date and time) of simulation creation'],
            ['data.updated_at', 'string', 'The timestamp (date and time) of simulation last update'],
            ['data.links', 'object', 'An object containing links to API endpoints for simulation input/output files download (NULL if no file is available)'],
            ['data.links.input_parameters', 'string', 'API endpoint link for input parameters download'],
            ['data.links.enrichment_database', 'string', 'API endpoint link for enrichment database download'],
            ['data.links.node_types', 'string', 'API endpoint link for node types download'],
            ['data.links.edge_types', 'string', 'API endpoint link for edge types download'],
            ['data.links.edge_subtypes', 'string', 'API endpoint link for edge subtypes download'],
            ['data.links.non_expressed_nodes', 'string', 'API endpoint link for non-expressed nodes download'],
            ['data.links.removed_nodes', 'string', 'API endpoint link for knocked-out nodes download'],
            ['data.links.output_file', 'string', 'API endpoint link for output file download'],
            ['data.links.pathway_output', 'string', 'API endpoint link for output pathway matrix download'],
            ['data.links.nodes_output', 'string', 'API endpoint link for output nodes matrix download'],
        ],
        'example' => <<<JSON
{
    "data": {
        "id": 1,
        "name": "Title",
        "status": 3,
        "readable_status": "Completed",
        "parameters": {
            "fast": true,
            "fdr": "BH",
            "epsilon": 0.001,
            "seed": null,
            "reactome": false,
            "enrichMiRNAs": true,
            "miRNAsEvidence": null
        },
        "logs": "Log Messages goes here",
        "organism": "hsa",
        "created_at": "2021-03-17T21:13:44.000000Z",
        "updated_at": "2021-03-18T12:33:06.000000Z",
        "links": {
            "input_parameters": "$url/api/v1/simulations/1/download/input_parameters",
            "enrichment_database": null,
            "node_types": null,
            "edge_types": null,
            "edge_subtypes": null,
            "non_expressed_nodes": "$url/api/v1/simulations/1/download/non_expressed_nodes",
            "removed_nodes": null,
            "output_file": null,
            "pathway_output": "$url/api/v1/simulations/1/download/pathway_output",
            "nodes_output": "$url/api/v1/simulations/1/download/nodes_output"
        }
    }
}
JSON
    ])
                                @include('docs.partials.action', [
        'title' => 'Submit a simulation',
        'description' => <<<DESCRIPTION
Submit a ready or failed simulation to the jobs queue.<br>
The <strong>{SIMULATION_ID}</strong> parameter in the url should be replaced with the unique identifier of the simulation.
If an invalid unique identifier is provided a <strong>404</strong> error will be returned.
DESCRIPTION,
        'method' => 'GET',
        'url' => '/api/v1/simulations/{SIMULATION_ID}/submit',
        'queryParameters' => null,
        'postParameters' => null,
        'responseDescription' => <<<RESPONSE_DESCRIPTION
An object containing the submitted simulation. For more details on the format, refer to the
<a href="#endpoint-get-a-simulation">Get a simulation</a> endpoint of the API.
RESPONSE_DESCRIPTION,
        'responseParams' => null,
        'example' => null,
    ])
                                @include('docs.partials.action', [
        'title' => 'Download a file from the simulation',
        'description' => <<<DESCRIPTION
Download an input or output file from the simulation.<br>
The <strong>{SIMULATION_ID}</strong> parameter in the url should be replaced with the unique identifier of the simulation.
If an invalid unique identifier is provided a <strong>404</strong> error will be returned.<br>
The <strong>{TYPE}</strong> parameter can be one of the following values:<br>
<ul>
    <li><strong>input_parameters</strong>: to download the file containing all input parameters for the simulation;</li>
    <li><strong>enrichment_database</strong>: to download the enrichment database;</li>
    <li><strong>node_types</strong>: to download the custom node types file, if provided by the user;</li>
    <li><strong>edge_types</strong>: to download the custom edge types file, if provided by the user;</li>
    <li><strong>edge_subtypes</strong>: to download the custom edge subtypes file, if provided by the user;</li>
    <li><strong>non_expressed_nodes</strong>: to download the non-expressed nodes file, if provided by the user;</li>
    <li><strong>removed_nodes</strong>: to download the knocked-out nodes file, if provided by the user;</li>
    <li><strong>output_file</strong>: to download the output file, if available;</li>
    <li><strong>pathway_output</strong>: to download the pathway output file, if available;</li>
    <li><strong>nodes_output</strong>: to download the nodes output file, if available.</li>
</ul>
A <strong>404</strong> error will be returned if a file is not available. If an invalid type is provides a
<strong>500</strong> error is returned.
DESCRIPTION,
        'method' => 'GET',
        'url' => '/api/v1/simulations/{SIMULATION_ID}/download/{TYPE}',
        'queryParameters' => null,
        'postParameters' => null,
        'responseDescription' => <<<RESPONSE_DESCRIPTION
        The file requested by the user.
        <h5 class="mx--4">Response Content Type</h5>
        <div class="mt-0"><code class="text-dark">text/plain</code></div>
RESPONSE_DESCRIPTION,
        'responseParams' => null,
        'example' => null,
    ])
                                @include('docs.partials.action', [
        'title' => 'Create a new simulation',
        'description' => <<<DESCRIPTION
Submit a ready or failed simulation to the jobs queue.<br>
The <strong>{SIMULATION_ID}</strong> parameter in the url should be replaced with the unique identifier of the simulation.
If an invalid unique identifier is provided a <strong>404</strong> error will be returned.
DESCRIPTION,
        'method' => 'POST',
        'url' => '/api/v1/simulations',
        'queryParameters' => null,
        'postParameters' => [
            ['name', 'string', 'A name for the simulation'],
            ['organism', 'string', 'The organism using KEGG accession number'],
            ['epsilon', 'double', 'An OPTIONAL epsilon value to determine non expressed node'],
            ['seed', 'double', 'An OPTIONAL seed value for RNG to allow reproducibility'],
            ['fdr', 'string', 'An OPTIONAL FDR algorithm (One of: BH, QV, LOC; Default: BH)'],
            ['reactome', 'boolean', 'An OPTIONAL boolean to use reactome together with KEGG'],
            ['fast', 'boolean', 'An OPTIONAL boolean to use the fast method for the perturbation computation (Default: true)'],
            ['miRNAs', 'boolean', 'An OPTIONAL boolean to enable MITHrIL miRNA enrichment feature'],
            ['miRNAsEvidence', 'string', 'An OPTIONAL string to select miRNA-target interactions (One of: STRONG, WEAK, PREDICTION; Default: STRONG)'],
            ['submit', 'boolean', 'An OPTIONAL boolean to automatically submit the simulation after creation (Default: false)'],
            ['nodes.overExpressed', 'array', 'An OPTIONAL set of over-expressed nodes (Required if no under-expressed or simulation parameters file are provided)'],
            ['nodes.underExpressed', 'array', 'An OPTIONAL set of under-expressed nodes (Required if no over-expressed or simulation parameters file are provided)'],
            ['nodes.nonExpressed', 'array', 'An OPTIONAL set of non-expressed nodes'],
            ['nodes.knockout', 'array', 'An OPTIONAL set of knocked-out nodes'],
            ['simulationParametersFile', 'boolean', 'An OPTIONAL file of simulation parameters file (Upload is supported only for multipart/form-data body content-type)'],
            ['enrichmentDatabaseFile', 'boolean', 'An OPTIONAL file of enrichment database file (Upload is supported only for multipart/form-data body content-type)'],
            ['filter', 'string', 'An OPTIONAL filter for the enrichment database'],
            ['nonExpressedNodesFile', 'boolean', 'An OPTIONAL file of non-expressed nodes file (Upload is supported only for multipart/form-data body content-type)'],
            ['knockoutNodesFile', 'boolean', 'An OPTIONAL file of knocked-out nodes file (Upload is supported only for multipart/form-data body content-type)'],
            ['customNodeTypesFile', 'boolean', 'An OPTIONAL file of custom node types file (Upload is supported only for multipart/form-data body content-type)'],
            ['customEdgeTypesFile', 'boolean', 'An OPTIONAL file of custom edge types file (Upload is supported only for multipart/form-data body content-type)'],
            ['customEdgeSubtypesFile', 'boolean', 'An OPTIONAL file of custom edge subtypes file (Upload is supported only for multipart/form-data body content-type)'],
        ],
        'responseDescription' => <<<RESPONSE_DESCRIPTION
An object containing the new simulation. For more details on the format, refer to the
<a href="#endpoint-get-a-simulation">Get a simulation</a> endpoint of the API.
RESPONSE_DESCRIPTION,
        'responseParams' => null,
        'example' => null,
    ])
                                @include('docs.partials.action', [
        'title' => 'Delete a simulation',
        'description' => <<<DESCRIPTION
Delete a simulation. If the simulation is queued or processing it cannot be deleted. The <strong>{SIMULATION_ID}</strong>
parameter in the url should be replaced with the unique identifier of the simulation. If an invalid unique identifier
is provided a <strong>404</strong> error will be returned.
DESCRIPTION,
        'method' => 'DELETE',
        'url' => '/api/v1/simulations/{SIMULATION_ID}',
        'queryParameters' => null,
        'postParameters' => null,
        'responseDescription' => <<<RESPONSE_DESCRIPTION
An object containing the new simulation. For more details on the format, refer to the
<a href="#endpoint-get-a-simulation">Get a simulation</a> endpoint of the API.
RESPONSE_DESCRIPTION,
        'responseParams' => null,
        'example' => null,
    ])
                            </div>


                        </div>

                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection
