@extends('layouts.app')

@section('content')
    <!-- Main Container -->
    <main id="main-container">
        <!-- Story Content -->
        <div class="bg-white">
            <section class="content content-boxed">
                <!-- Section Content -->
                <h1 class="text-black text-center push-30-t push-10">SIMPATHY User Manual</h1>
                <div class="row push-50-t push-50 nice-copy-story">
                    <div class="col-sm-8 col-sm-offset-2 text-justify">
                        <h1 class="font-w400 text-black push-20">Introduction</h1>

                        <p>SIMPATHY is a software developed to simulate the de-regulation of elements on pathways.
                            The tool, available as a web application, provides a convenient GUI for submitting
                            and displaying simulations, and an API for submitting batch jobs. The features offered by
                            the two interfaces are almost identical.</p>
                        <p>The only prerequisite of the simulation model is that the simulated nodes must
                            be statistically independent. That is, in the pathway there must be no directed paths
                            connecting the nodes to be simulated. However, this limitation can be overcome by enriching
                            the pathways with a dummy element linking the two nodes. The simulation can therefore be
                            performed on the dummy element, avoiding the need for statistical independence.</p>
                        <p>To perform a simulation, you must first register to identify each request. After
                            registration, you can submit any job without limitation. Any submission is kept private,
                            and no other user can view it.
                        <p>
                        <p>Below there are details of the main parts of the web interface. For APIs, see the following
                            <a href="{{ url('/home/api') }}" class="link-effect">page</a>.</p>

                        <h1 class="font-w400 text-black push-20">User Panel</h1>
                        <p>The User Panel is the main page that allows you to access all the services provided by
                            SIMPATHY. From there, the user can submit new jobs by choosing an activity from the
                            <strong>Analysis</strong> panel on the left.
                        <p>In the <strong>Analysis History</strong> panel, you can see an history of all simulations,
                            check their logs (<a href="Javascript:;" title="View log"
                                                 class="btn btn-xs btn-primary btn-view-job">
                                <i class="fa fa-file-text" aria-hidden="true"></i>
                            </a> button), view the results (<a href="Javascript:;" title="View results"
                                                               class="btn btn-xs btn-success">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                            </a> button), or erase it (<a href="Javascript:;" title="Delete"
                                                          class="btn btn-xs btn-danger">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </a> button). For each job, the user will be able to see its type, current state, and
                            submission date.</p>
                        <p>The <strong>Your API Tokens</strong> panel allows you to generate keys for authentication via
                            the API interface. For more details on using the API, see the following <a
                                    href="{{ url('/home/api') }}" class="link-effect">page</a>. The
                            <a type="button" title="Create New Token"><i class="si si-plus"></i></a>
                            button at the top of the panel adds new token. The token will only be displayed once.
                            The user mast take note of its value and keep it in a safe place as it allows access to all
                            his submission.
                        </p>

                        <h1 class="font-w400 text-black push-20">Simple Simulation Form</h1>
                        <p>
                            The form submits a new simulation on elements already present in pathways (genes,
                            microRNAs, or metabolites) through a wizard. The user first specifies the organism. It is
                            then asked to choose one or more over-expressed nodes and/or one or more under-expressed
                            nodes, zero or more non-expressed nodes. Therefore, the simulation job is ready to be
                            submitted.</p>
                        <p>The last page of the wizard allows you to specify some advanced parameters:</p>
                        <ul>
                            <li>
                                <strong>Epsilon</strong>: A range around zero within which a node is considered
                                non-expressed;
                            </li>
                            <li>
                                <strong>RNG seed</strong>: an integer number used as seed for the random number
                                generator (specify only to ensure perfect reproducibility of the results);
                            </li>
                            <li>
                                <strong>Enrich pathways with miRNAs</strong>: select to enable the automatic addition of
                                miRNAs in pathways;
                            </li>
                            <li>
                                <strong>Compute simulations on the meta-pathway</strong>: activate to perform the
                                simulation on the meta-pathway obtained by merging all biological pathways of the
                                organism. If your want to simulate the impact on one or more pathway in isolation,
                                deselect this option.
                            </li>
                        </ul>
                        <h1 class="font-w400 text-black push-20">Advanced Simulation Form</h1>
                        <p>The advanced simulation form allows you to perform a simulation by manually specifying all
                            the parameters in the format required by the SIMPATHY application. The user can also upload
                            a database for custom pathway enrichment (adding items such as drugs, exogenous elements,
                            vesicles, exosomes). The database can be used completely, or filtered through an optional
                            string. You can also provide files that contain new node types, new edge types, and new
                            edgesubtypes.</p>
                        <p>More details on the input files are available below.</p>
                        <p>All other parameters are identical to those in the simplified submission form.</p>
                        <h2 class="font-w400 text-black push-30">Simulation Parameters File</h2>
                        <p>
                            The simulation parameter file specifies the list of deregulations to simulate.
                            The file is tab-separated textual file. Each line specifies the identifier of a node
                            together with type of deregulation (<strong>OVEREXPRESSION</strong>,
                            <strong>UNDEREXPRESSION</strong>).
                        </p>
                        <p class="font-w400">Example of Simulation Parameters File</p>
                        <pre>{{ "7157\tOVEREXPRESSION\n2475\tUNDEREXPRESSION\nhsa-miR-7-5p\tOVEREXPRESSION" }}</pre>

                        <h1 class="font-w400 text-black push-20">Advanced Simulation Form</h1>
                        <p>The advanced simulation form allows you to perform a simulation by manually specifying all
                            the parameters in the format required by the SIMPATHY application. The user can also upload
                            a database for custom pathway enrichment (adding items such as drugs, exogenous elements,
                            vesicles, exosomes). The database can be used completely, or filtered through an optional
                            string. You can also provide files that contain new node types, new edge types, and new
                            edgesubtypes.</p>
                        <p>More details on the input files are available below.</p>
                        <p>All other parameters are identical to those in the simplified submission form.</p>
                        <h2 class="font-w400 text-black push-30">Enrichment Database File</h2>
                        <p>
                            The enrichment database file is a tab-separated text file that contains a row for each edge
                            to add to pathways.
                            Each row consists of 9 fields: source node id, source node name, source node type,
                            destination node id, destination node name, destination node type, edge type , edge subtype,
                            and an optional filter.
                            In order for the enrichment process to be successful, the destination node must already be
                            present in the pathway. The source node may also be absent, and will be added during the
                            enrichment phase. The following is an example of enrichment file.</p>
                        <pre>{{ "VES1\tVescicle 1\tVESCICLE\t7157\tTP53\tVESCICLE_EDGE\tACTIVATION\tVescicle1\n".
                         "VES1\tVescicle 1\tVESCICLE\t2475\tMTOR\tVESCICLE_EDGE\tINHIBITION\tVescicle1\n".
                         "VES1\tVescicle 1\tVESCICLE\thsa-miR-7-5p\t\tVESCICLE_EDGE\tINHIBITION\tVescicle1\n".
                         "VES2\tVescicle 2\tVESCICLE\thsa-let-7e-5p\t\tVESCICLE_EDGE\tINHIBITION\tVescicle2\n".
                         "VES2\tVescicle 2\tVESCICLE\thsa-miR-21-3p7157\t\tVESCICLE_EDGE\tACTIVATION\tVescicle2\n".
                         "VES2\tVescicle 2\tVESCICLE\t57527\tRPTOR\tVESCICLE_EDGE\tACTIVATION\tVescicle2\n"}}</pre>
                        <p>The previous sample file contains two different vescicles. We can enrich a pathway with
                            only selecting the first vescicle by specifying <strong>Vescicle1</strong> in the
                            <strong>Optional Db Filter</strong> field or the second vesicle by specifying <strong>Vescicle2</strong>.
                        </p>
                        <p>The last field in the enrichment file is the value to be used in the <strong>Optional Db
                                Filter</strong> field.</p>
                        <h2 class="font-w400 text-black push-30">Custom Node Type File</h2>
                        <p>
                            The custom node type file is an optional tab-separated text file where each row represents
                            a new node type.
                            Each node type consists of a name and a sign used for the computation of pathway
                            accumulator.
                            The sign represents the action of such elements on the whole pathway. If elements of such
                            a type tend to activate the pathway the sign will be positive, negative otherwise.</p>
                        <p>Example:</p>
                        <pre>{{ "VESCICLE\t+1\nDRUG\t-1"}}</pre>

                        <h2 class="font-w400 text-black push-30">Custom Edge Type File</h2>
                        <p>
                            The custom node type file is an optional tab-separated text file where each row represents
                            a new edge type. Only the name of the edge type is needed. The edge subtype represents the
                            action undertaken by the edge.</p>
                        <p>Example:</p>
                        <pre>{{ "VESCICLE_EDGE\nDRUG_EDGE"}}</pre>

                        <h2 class="font-w400 text-black push-30">Custom Edge SubTypes File</h2>
                        <p>
                            The custom edge subtypes file is an optional tab-separated text file where each row
                            represents a new edge subtype. The edge subtype consists of the effect that the source node
                            has on the target node (eg activation or inhibition).
                            Each row in the enrichment file has 2 fields: the name of the subtype, a weight used
                            to represent the effect (+1 activation, -1 inhibition, 0 no effect).</p>
                        <p>An optional third field might be specified. The third field represents the priority of
                        the edge when merging pathways.</p>
                        <p>Example:</p>
                        <pre>{{ "VESCICLE_CONTAINS\t0\nVESCICLE_ACTIVATE\t+1\nVESCICLE_INHIBIT\t-1\nDRUG_INHIBIT\t-1"}}</pre>


                    </div>
                </div>
                <!-- END Section Content -->
            </section>
        </div>
        <!-- END Story Content -->

    </main>
    <!-- END Main Container -->
@endsection