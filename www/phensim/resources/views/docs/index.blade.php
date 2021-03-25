@extends('layouts.app', ['title' => __('User Manual')])

@section('content')
    <x-page-header class="col-lg-12" gradient="bg-gradient-orange">
        User Manual
    </x-page-header>

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-body">
                        <h3>Introduction</h3>
                        <p>PHENSIM is a software developed to simulate the de-regulation of pathways biological
                            elements.
                            This web application provides a convenient GUI for submitting and displaying simulations,
                            and APIs for submitting batch jobs. The features offered by the two interfaces are almost
                            identical. For more details about the model please refer to out publication ().</p>
                        <p>Registration is needed to identify each request, and avoid SPAM. After registration, no
                            limitations are applied to the users. Any submission is kept private, and no other user can
                            view it.</p>
                        <p>
                            All elements within pathways are identified using the following accession numbers:
                        </p>
                        <ul>
                            <li><strong>Entrez Id</strong> for genes (eg 7157 for TP53);</li>
                            <li><strong>miRBase mature Id</strong> for miRNAs (eg hsa-let-7f-5p);</li>
                            <li><strong>KEGG or CHEBI Identifiers</strong> for other compounds (eg
                                cpd:C00047 for Lysine acid, chebi:31011 for Valerate).
                            </li>
                        </ul>
                        <p>
                            Our main dashboard has three sections: the navigation bar on the left, the
                            user menu on the top, and the main content.
                        </p>
                        <div class="text-center">
                            <img src="{{ asset('assets/img/screens/screen_main.png') }}" class="img-fluid rounded"
                                 alt="The dashboard">
                        </div>
                        <p>
                            By clicking on the <em>user name</em> or <em>icon</em> in the top right corner, the
                            <strong>user menu</strong> will be shown. There, you will find the <strong>My Profile
                            </strong> and <strong>Logout</strong> buttons. By choosing <strong>My Profile</strong>,
                            you can modify your account details, change your password, and create new user tokens
                            for API access.
                        </p>
                        <div class="text-center">
                            <img src="{{ asset('assets/img/screens/user_menu.png') }}" class="img-fluid rounded"
                                 alt="The dashboard">
                        </div>

                        <h3>The <em>My Profile</em> panel</h3>
                        <div class="text-center">
                            <img src="{{ asset('assets/img/screens/user_profile.png') }}" class="img-fluid rounded"
                                 style="width: 50%" alt="The My Profile page">
                        </div>
                        <p>The Profile is the main page from which you can change all details for your account, such as
                            name, e-mail, affiliation, and password. You can also create new access token for enabling
                            access to the API interface.
                        </p>
                        <p>
                            The <strong>User Information</strong> form enables you to change name, affiliation and
                            e-mail address. The <strong>Password</strong> form allows you to change password. All
                            password must have at least 8 characters with lowercase, uppercase letters, and numbers.
                        </p>

                        <p>
                            The <strong>API tokens</strong> panel can be used to create new API tokens, or delete issued
                            tokens. To issue a new token, write a name in the text field and click the <em>Save</em>
                            button. The token will be immediately created and displayed through a modal window. You will
                            need to copy its value, since as soon as the window is closed, there is no way to recover
                            it.
                            <strong>The key must be safely stored since it allows unrestricted access to your
                                account.</strong>
                        </p>
                        <div class="row p-4">
                            <div class="col-6 text-center">
                                <img src="{{ asset('assets/img/screens/create_token_1.png') }}"
                                     class="img-fluid rounded"
                                     alt="Create token">
                            </div>
                            <div class="col-6 text-center">
                                <img src="{{ asset('assets/img/screens/create_token_2.png') }}"
                                     class="img-fluid rounded"
                                     alt="Create token">
                            </div>
                        </div>


                        <h3>The <em>Simulation</em> panel</h3>
                        <p>
                            The simulation panel is the most important part of PHENSIM user interface. It enables the
                            submission of new simulations and the visualization and download of completed simulations.
                            The panel mainly contains a table that lists all simulations, sorted by creation date.
                            Above the table, there are two buttons for submitting new simulation. Their function is
                            described in the following sections.
                        </p>
                        <div class="text-center">
                            <img src="{{ asset('assets/img/screens/simulations_table.png') }}" class="img-fluid rounded"
                                 alt="The My Profile page">
                        </div>
                        <p>
                            For each simulation, the table list its name, status, creation date, and buttons to
                            perform actions on the simulation. The status can be one of five values:
                        </p>
                        <ul>
                            <li><strong>Ready</strong>: the simulation is saved and ready to be submitted for
                                computation;
                            </li>
                            <li><strong>Queued</strong>: the simulation is queued for computation;</li>
                            <li><strong>Running</strong>: the simulation is being processed;</li>
                            <li><strong>Completed</strong>: the simulation has been completed and the results are
                                available;
                            </li>
                            <li><strong>Failed</strong>: the simulation has been completed with errors, the log might
                                contain more details.
                            </li>
                        </ul>
                        <p>
                            The action buttons are the following:
                        </p>
                        <ul>
                            <li>
                                <strong>Submit simulation</strong>
                                <a href="#"><i class="fas fa-play fa-fw"></i></a>: submit the simulation to the
                                analysis queue. It is available only for ready simulations.
                            </li>
                            <li>
                                <strong>Resubmit simulation</strong>
                                <a href="#"><i class="fas fa-redo fa-fw"></i></a>: allows resubmission of failed
                                simulations.
                            </li>
                            <li>
                                <strong>Show logs</strong>
                                <a href="#"><i class="fas fa-file-alt fa-fw"></i></a>: display all the logs of the
                                simulation. It might be helpful to discover error messages. The logs are constantly
                                updated for a running simulation.
                            </li>
                            <li>
                                <strong>Show simulation</strong>
                                <a href="#"><i class="fas fa-eye fa-fw"></i></a>: shows the results of a completed
                                simulation.
                            </li>
                            <li>
                                <strong>Delete</strong>
                                <a href="#" class="text-danger"><i class="fas fa-trash fa-fw"></i></a>: delete
                                the simulation. A queued or running simulation cannot be deleted.
                            </li>
                        </ul>

                        <h3>The <em>Simple Simulation</em> form</h3>
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
                                    <td>gene expression interaction, indicating relation of transcription factor and
                                        target
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
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>

@endsection
