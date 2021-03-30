@extends('layouts.app')

@section('content')
    <!-- Main Container -->
    <main id="main-container">
        <!-- Story Content -->
        <div class="bg-white">
            <section class="content content-boxed">
                <!-- Section Content -->
                <h1 class="text-black text-center push-30-t push-10">PHENSIM User Manual</h1>
                <div class="row push-50-t push-50 nice-copy-story">
                    <div class="col-sm-8 col-sm-offset-2 text-justify">
                        <h1 class="font-w400 text-black push-20">Introduction</h1>

                        <p>PHENSIM is a software developed to simulate the de-regulation of elements on pathways.
                            The tool, available as a web application, provides a convenient GUI for submitting
                            and displaying simulations, and APIs for submitting batch jobs. The features offered by
                            the two interfaces are almost identical.</p>
                        <p>To obtain reliable results, simulated nodes should be statistically independent. That is, in
                            each pathway no directed paths connecting such nodes should be present. However, this
                            limitation can be overcome by enriching pathways with a dummy element linking the each
                            dependent node. The simulation can be performed on the dummy element, avoiding the need for
                            statistical independence.</p>
                        <p>Registration is needed to identify each request, and avoid SPAM. After registration, no
                            limitations are applied to the users. Any submission is kept private, and no other user can
                            view it.</p>
                        <p>
                            All elements within pathways are identified using the following accession numbers:
                        </p>
                        <ul>
                            <li><strong>Entrez Id</strong> for genes (eg 7157 for TP53);</li>
                            <li><strong>miRBase mature Id</strong> for miRNAs (eg hsa-let-7f-5p);</li>
                            <li><strong>KEGG Identifiers</strong> for other elements (eg cpd:C00047 for Lysine acid).
                            </li>
                        </ul>
                        <p>Below a detailed description on the main parts of the web interface is given.
                            For APIs, see the following <a href="{{ url('/home/api') }}" class="link-effect">link</a>.
                        </p>

                        <h1 class="font-w400 text-black push-20">User Panel</h1>
                        <p>The User Panel is the main page from which an user can access all services provided by
                            PHENSIM. From the page, the user can:</p>
                        <ul>
                            <li>submit new simulations by choosing an activity from the <strong>Analysis</strong> panel
                                on the left,
                            </li>
                            <li>get a list of all previously submitted simulations,</li>
                            <li>and create new authentication keys for APIs.</li>
                        </ul>
                        <p>The <strong>Analysis History</strong> panel displays a list of all previously submitted
                            simulation. For each submission, its type, state and insertion date are reported. For each
                            simulation, the user can check its log (<a title="View log"
                                                                       class="btn btn-xs btn-primary btn-view-job">
                                <i class="fa fa-file-text" aria-hidden="true"></i>
                            </a> button), view its results (<a title="View results"
                                                               class="btn btn-xs btn-success">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                            </a> button), or delete it (<a title="Delete"
                                                           class="btn btn-xs btn-danger">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </a> button). A job can be deleted unless it is being processed by our system. In such a
                            case, the user will have to wait for completion.</p>

                        <p>All analysis are kept in our system for <strong>one year</strong> after its completion.</p>

                        <p>The <strong>Your API Tokens</strong> panel is employed to generate authentication keys for
                            the API interface. More details are available <a
                                    href="{{ url('/home/api') }}" class="link-effect">here</a>. The
                            <a title="Create New Token"><i class="si si-plus"></i></a>
                            button at the top of the panel adds new token. The token will only be displayed once.
                            The user must therefore take note of its value and keep it in a safe place as it allows
                            access to all his submission.
                        </p>

                        <h1 class="font-w400 text-black push-20">Simple Simulation Form</h1>
                        <p>
                            This form can be employed to submit new simulations on elements already within pathways
                            (genes, microRNAs, or metabolites) through a simplified wizard. The user first specifies the
                            organism. Than he may choose one or more over-expressed nodes, and/or one or more
                            under-expressed nodes, and zero or more non-expressed nodes.</p>
                        <p>The simulation is ready to be submitted as it is. However, in the last wizard panel some
                            advanced parameters can be altered to better suit the needs of the user:</p>
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
                                <strong>Enrich pathways with miRNAs</strong>: select to enable automatic addition of
                                miRNAs in pathways;
                            </li>
                            <li>
                                <strong>Compute simulations on the meta-pathway</strong>: activate to perform the
                                simulation on the meta-pathway obtained by merging all biological pathways of the
                                organism (More details in <a
                                        href="http://www.mdpi.com/2311-553X/3/2/20/htm#sec4-ncrna-03-00020"
                                        class="link-effect" target="_blank">Alaimo et al., 2017</a>). If your wish
                                to simulate the impact on one or more pathway in isolation, deselect this option.
                            </li>
                        </ul>
                        <h1 class="font-w400 text-black push-20">Advanced Simulation Form</h1>
                        <p>The advanced simulation form allows users to perform simulations by manually specifying all
                            parameters in the format required by PHENSIM. Although more complex, this feature offers
                            greater flexibility than the previous one. Indeed, the user can upload
                            a database for custom pathway enrichment (adding items such as drugs, exogenous elements,
                            vesicles, exosomes, or other elements for which the user knows interactions with genes
                            within pathways). The database can be used entirely, or filtered through the <strong>Optional
                                Db Filter</strong> field. New types of nodes and edges can also be added by providing
                            specific enrichment files.</p>
                        <p>More details on the input files are available below.</p>
                        <p>All other parameters are identical to those in the simplified submission form.</p>
                        <h2 class="font-w400 text-black push-30">Simulation Parameters File</h2>
                        <p>
                            The simulation parameter file specifies the list of deregulations to be simulated.
                            It is tab-separated textual file, where each line specifies the identifier of a node
                            together with type of deregulation (<strong>OVEREXPRESSION</strong>,
                            <strong>UNDEREXPRESSION</strong>).
                        </p>
                        <p class="font-w400">Example of Simulation Parameters File</p>
                        <pre>{{ "7157\tOVEREXPRESSION\n2475\tUNDEREXPRESSION\nhsa-miR-7-5p\tOVEREXPRESSION" }}</pre>

                        <h2 class="font-w400 text-black push-30">Enrichment Database File</h2>
                        <p>
                            The enrichment database file is a tab-separated text file that contains a row for each edge
                            that could be added to pathways.</p>
                        <p>
                            Each row should be divided in 9 fields:</p>
                        <ul>
                            <li><strong>source node id</strong>: an arbitrarily chosen identifier which is used to
                                uniquely represent the new node;
                            </li>
                            <li><strong>source node name</strong>: a name displayed to the user;</li>
                            <li><strong>source node type</strong>: a type for the node (use one from below or specify
                                custom ones using the appropriate file);
                            </li>
                            <li><strong>destination node id</strong>: an identifier for the destination node (it must be
                                already present in the pathway);
                            </li>
                            <li><strong>destination node name</strong>: a name for the destination node (can be empty);
                            </li>
                            <li><strong>destination node type</strong>: a type for the destination node (can be empty);
                            </li>
                            <li><strong>edge type</strong>: the type of edge that will be added to the pathway;</li>
                            <li><strong>edge subtype</strong>: the subtype of the edge that will be added to the
                                pathway (the subtype represents the type of action that the source node has on the
                                destination node, i.e. activation, inhibition);
                            </li>
                            <li><strong>optional filter</strong>: a string that can be used in the <strong>Optional Db
                                    Filter</strong> field to select only some parts of the file.
                            </li>
                        </ul>
                        <p>In order for the enrichment process to be successful, the destination node must be already
                            within the pathway. If the destination node is not found, the edge
                            will not be added. The source node can be absent. It will be automatically created in the
                            enrichment phase. The following is an example of enrichment file.</p>
                        <pre>{{ "VES1\tVescicle 1\tVESCICLE\t7157\tTP53\tGENE\tVESCICLE_EDGE\tACTIVATION\tVescicle1\n".
                         "VES1\tVescicle 1\tVESCICLE\t2475\tMTOR\tGENE\tVESCICLE_EDGE\tINHIBITION\tVescicle1\n".
                         "VES1\tVescicle 1\tVESCICLE\thsa-miR-7-5p\t\tMIRNA\tVESCICLE_EDGE\tINHIBITION\tVescicle1\n".
                         "VES2\tVescicle 2\tVESCICLE\thsa-let-7e-5p\t\tMIRNA\tVESCICLE_EDGE\tINHIBITION\tVescicle2\n".
                         "VES2\tVescicle 2\tVESCICLE\thsa-miR-21-3p\t\tMIRNA\tVESCICLE_EDGE\tACTIVATION\tVescicle2\n".
                         "VES2\tVescicle 2\tVESCICLE\t57527\tRPTOR\tGENE\tVESCICLE_EDGE\tACTIVATION\tVescicle2\n
                         "}}</pre>
                        <p>The previous sample file contains two different vescicles. To enrich each pathway with
                            the edges of the first vescicle, the user can specify <strong>Vescicle1</strong> in the
                            <strong>Optional Db Filter</strong> field of the <strong>Advanced Simulation</strong> form.
                        </p>
                        <h2 class="font-w400 text-black push-30">Custom Node Type File</h2>
                        <p>
                            The custom node type file is an optional tab-separated text file where each row represents
                            a new node type.
                            Each node type consists of a name and a sign used for the computation of pathway
                            accumulator.
                            The sign represents the action of such type of elements on the whole pathway. If they tend
                            to activate the pathway the sign will be positive, negative otherwise.</p>
                        <p>Example:</p>
                        <pre>{{ "VESCICLE\t+1\nDRUG\t-1"}}</pre>

                        <h2 class="font-w400 text-black push-30">Custom Edge Type File</h2>
                        <p>
                            The custom edge type file is an optional tab-separated text file where each row represents
                            a new edge type. Only the name of the edge type is needed. The edge subtype represents the
                            action undertaken by the edge.</p>
                        <p>Example:</p>
                        <pre>{{ "VESCICLE_EDGE\nDRUG_EDGE"}}</pre>

                        <h2 class="font-w400 text-black push-30">Custom Edge SubTypes File</h2>
                        <p>
                            The custom edge subtypes file is an optional tab-separated text file where each row
                            represents a new edge subtype. The edge subtype explain the effect that the source node
                            has on the target node (eg activation or inhibition), ad is fundamental for the correct
                            computation of PHENSIM activity score.</p>
                        <p>Each row in the enrichment file has 2 fields: the name of the subtype, a weight used
                            to represent the effect (+1 activation, -1 inhibition, 0 no effect).</p>
                        <p>An optional third field might be specified. The third field represents the priority of
                            the edge when merging pathways.</p>
                        <p>Example:</p>
                        <pre>{{ "VESCICLE_CONTAINS\t0\nVESCICLE_ACTIVATE\t+1\nVESCICLE_INHIBIT\t-1\nDRUG_INHIBIT\t-1"}}</pre>
                        <h2 class="font-w400 text-black push-30">Default Node Types</h2>
                        <table class="table push-20-l">
                            <tbody>
                            <tr>
                                <td>GENE</td>
                                <td>A gene identified by Entrez Id</td>
                            </tr>
                            <tr>
                                <td>COMPOUND</td>
                                <td>A compound identified by KEGG Id</td>
                            </tr>
                            <tr>
                                <td>MAP</td>
                                <td>A pathway identified by KEGG Id</td>
                            </tr>
                            <tr>
                                <td>REACTION</td>
                                <td>A reaction identified by KEGG Id</td>
                            </tr>
                            <tr>
                                <td>MIRNA</td>
                                <td>A miRNA identified by miRBase mature Id</td>
                            </tr>
                            <tr>
                                <td>ENZYME</td>
                                <td>An enzyme identified by KEGG Id or Entrez Id, if available</td>
                            </tr>
                            </tbody>
                        </table>
                        <h2 class="font-w400 text-black push-30">Default Edge Types</h2>
                        <table class="table push-20-l">
                            <tbody>
                            <tr>
                                <td>ECREL</td>
                                <td>enzyme-enzyme relation, indicating two enzymes catalyzing successive reaction
                                    steps
                                </td>
                            </tr>
                            <tr>
                                <td>PPREL</td>
                                <td>protein-protein interaction, such as binding and modification</td>
                            </tr>
                            <tr>
                                <td>GEREL</td>
                                <td>gene expression interaction, indicating relation of transcription factor and target
                                    gene product
                                </td>
                            </tr>
                            <tr>
                                <td>PCREL</td>
                                <td>protein-compound interaction</td>
                            </tr>
                            <tr>
                                <td>MGREL</td>
                                <td>miRNA-gene interaction</td>
                            </tr>
                            <tr>
                                <td>REACTION</td>
                                <td>a reaction between substrates that produce a specific product</td>
                            </tr>
                            </tbody>
                        </table>
                        <h2 class="font-w400 text-black push-30">Default Edge Subtypes</h2>
                        <table class="table push-20-l">
                            <tbody>
                            <tr>
                                <td>COMPOUND</td>
                                <td>Some chemical compound interaction</td>
                            </tr>
                            <tr>
                                <td>REACTION_SUBSTRATE</td>
                                <td>The substrate of a reaction</td>
                            </tr>
                            <tr>
                                <td>REACTION_PRODUCT</td>
                                <td>The product of a reaction</td>
                            </tr>
                            <tr>
                                <td>ACTIVATION</td>
                                <td>A positive effects which may be associated with molecular binding</td>
                            </tr>
                            <tr>
                                <td>INHIBITION</td>
                                <td>A negative effects which may be associated with molecular binding</td>
                            </tr>
                            <tr>
                                <td>EXPRESSION</td>
                                <td>Activation via DNA binding</td>
                            </tr>
                            <tr>
                                <td>REPRESSION</td>
                                <td>Inhibition via DNA binding</td>
                            </tr>
                            <tr>
                                <td>MIRNA_INHIBITION</td>
                                <td>A miRNA-mRNA inhibition interaction</td>
                            </tr>
                            <tr>
                                <td>TFMIRNA_ACTIVATION</td>
                                <td>A transcription factor which activates miRNA transcription</td>
                            </tr>
                            <tr>
                                <td>TFMIRNA_INHIBITION</td>
                                <td>A transcription factor which inhibits miRNA transcription</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END Section Content -->
            </section>
        </div>
        <!-- END Story Content -->
    </main>
    <!-- END Main Container -->
@endsection