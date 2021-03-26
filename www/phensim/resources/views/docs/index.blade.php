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
                        <div class="text-center">
                            <img src="{{ asset('assets/img/screens/simulations_table.png') }}" class="img-fluid rounded"
                                 alt="The My Profile page">
                        </div>
                        <p>
                            The simulation panel is the most important part of PHENSIM user interface. It enables the
                            submission of new simulations and the visualization and download of completed simulations.
                            The panel mainly contains a table that lists all simulations, sorted by creation date.
                            Above the table, there are two buttons for submitting new simulation. Their function is
                            described in the following sections. For each simulation, the table list its name, status,
                            creation date, and buttons to perform actions on the simulation. The status can be one of
                            five values:
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

                        <h3>The <em>Show simulation</em> panel</h3>
                        <div class="text-center">
                            <img src="{{ asset('assets/img/screens/show_simulation.png') }}" class="img-fluid rounded"
                                 style="width: 50%" alt="The Show Simulation page">
                        </div>
                        <p>
                            This panel allows you to get all the results of a completed simulation. It is divided
                            into three section: <strong>Input Parameters</strong>, <strong>Results by pathway</strong>,
                            and <strong>Download Results</strong>. The <strong>Input Parameters</strong> section can be
                            used to download a compressed ZIP archive containing all the input files and command line
                            arguments for the PHENSIM analysis. The <strong>Download Results</strong> panel is used to
                            download all output files produced by PHENSIM, such as the raw results, the pathway and
                            nodes matrices containing all random values generated during the simulation process,
                            an SBML representation of the raw results, and an extended SIF file containing the network
                            and the simulation results that can be loaded in software such as Cytoscape or GEPHI.
                        </p>
                        <p>
                            The <strong>Results by pathway</strong> shows a table listing the results for each pathway.
                            Each row of the table correspond to a pathway. For each pathway, we report its identifier
                            in the source database (KEGG or REACTOME), its name, the activity score computed by PHENSIM,
                            the average perturbation predicted during the simulation, the p-value, and its FDR. For more
                            details on these values, please refer to the PHENSIM publication. Finally, at the end of
                            each row, the <strong>Show Pathway</strong> button <a href="#">
                                <i class="fas fa-eye fa-fw"></i></a> can be used to get all the details for the
                            biological elements (genes, miRNAs, or metabolites) contained in the selected pathway.
                        </p>

                        <h3>The <em>Show pathway</em> panel</h3>
                        <div class="text-center">
                            <img src="{{ asset('assets/img/screens/show_pathway.png') }}" class="img-fluid rounded"
                                 style="width: 50%" alt="The Show Pathway page">
                        </div>
                        <p>
                            This panel shows the predictions made for a single pathway of a completed simulation. It is
                            divided into two section: <strong>Results</strong>, and <strong>Download Results</strong>.
                            The <strong>Download Results</strong> panel is used to get an image of the pathway overlaid
                            with the predictions made by PHENSIM. Each biological element of the pathway is colored in
                            red for up-regulated elements, and blue for down-regulated ones.
                        </p>
                        <p>
                            The <strong>Results</strong> section contains a table listing the predictions for each
                            element. Each row of the table correspond to a biological element. Therefore, we report its
                            identifier, its name, the activity score computed by PHENSIM, the average perturbation
                            predicted during the simulation, the p-value, and its FDR. For more details on these values,
                            please refer to the PHENSIM publication. We also report if the biological element is an
                            endpoint (<i class="fas fa-fw fa-check"></i>) or not (<i class="fas fa-fw fa-times"></i>).
                        </p>

                        <h3>The <em>Simple Simulation</em> form</h3>
                        <p>
                            This form can be employed to submit new simulations on biological elements already contained
                            in pathway (genes, microRNAs, or metabolites) through a guided procedure.
                        </p>
                        <p>
                            In the first two step, you have to provide a name for the simulation and select the organism
                            against which the simulation will be performed. If the organism does not appear in our
                            list, please make a request by opening a new issue in our GitHub repository (<a
                                href="https://github.com/alaimos/phensim/issues">https://github.com/alaimos/phensim/issues</a>).
                            We will try to add the organism with the next PHENSIM upgrade (usually performed monthly).
                        </p>
                        <div class="text-center">
                            <img src="{{ asset('assets/img/screens/simple_001.png') }}" class="img-fluid rounded"
                                 alt="The First Two Step">
                        </div>
                        <p>
                            In the third step, you can use selection table to add parameters to the simulation. The
                            table lists all biological elements contained within our database. By clicking on the
                            buttons beside each gene you can set it as up-regulated (<i
                                class="fas fa-level-up-alt fa-fw"></i>), down-regulated (<i
                                class="fas fa-level-down-alt fa-fw"></i>), non-expressed (<i
                                class="fas fa-ban fa-fw"></i>), or
                            knocked-out (<i class="fas fa-times fa-fw"></i>) in the simulation. For the simulation to
                            start, you need at least one gene marked as up- or down-regulated.
                        </p>
                        <div class="text-center">
                            <img src="{{ asset('assets/img/screens/simple_002.png') }}" class="img-fluid rounded"
                                 alt="The Third Step">
                        </div>
                        <p>
                            In the fourth step, some advanced simulation parameters can be altered:</p>
                        <ul>
                            <li>
                                <strong>Epsilon</strong>: A range around zero within which a node is considered
                                non-expressed;
                            </li>
                            <li>
                                <strong>RNG seed</strong>: an integer number used as seed for the random number
                                generator;
                            </li>
                            <li>
                                <strong>FDR method</strong>: the algorithm used to compute FDR for p-values;
                            </li>
                            <li>
                                <strong>Add REACTOME pathway</strong>: select this option to use both KEGG and REACTOME
                                pathways to build the PHENSIM meta-pathway environment;
                            </li>
                            <li>
                                <strong>Add miRNAs to pathway</strong>: select this option to add miRNA-target and
                                TF-miRNA interactions to the meta-pathway environment;
                            </li>
                            <li>
                                <strong>miRNAs Evidence Level</strong>: the level of reliability used for the
                                detection of miRNA target interactions. <strong>Strong</strong> interactions are
                                validated through methods such as Western blot or reporter assay. <strong>Weak</strong>
                                interactions use method such as Microarray or NGS for their validation;
                            </li>
                            <li>
                                <strong>Use the fast algorithm</strong>: select this option to enable a faster version
                                of the PHENSIM algorithm. This algorithm uses multi-threading to speed-up the
                                computation
                                of the perturbation. However, two runs of the simulation with the same parameters and
                                RNG seed might produce slightly different results due to uncontrollable OS scheduling
                                events.
                            </li>
                        </ul>
                        <p>
                            Finally, the user can save the simulation by clicking on the <strong>Create
                                simulation</strong> button.
                        </p>
                        <div class="text-center">
                            <img src="{{ asset('assets/img/screens/simple_003.png') }}" class="img-fluid rounded"
                                 alt="The Final Steps">
                        </div>


                        <h3>The <em>Advanced Simulation</em> form</h3>
                        <div class="text-center">
                            <img src="{{ asset('assets/img/screens/advanced.png') }}" class="img-fluid rounded"
                                 alt="The advanced simulation form" style="width: 50%">
                        </div>
                        <p>
                            The advanced simulation form allows you to perform simulations by manually specifying all
                            parameters in the format required by PHENSIM command line algorithm. Although more complex,
                            this feature offers greater flexibility than the previous one. Indeed, the user can upload
                            a database for custom pathway enrichment (adding items such as drugs, exogenous elements,
                            vesicles, exosomes, or other elements for which the user knows interactions with genes
                            within pathways). The database can be used entirely, or filtered through the <strong>Database
                                Filter</strong> field. New types of nodes and edges can also be added by providing
                            specific enrichment files. More details on the input files are available in the following
                            sections.
                        </p>
                        <p>All other parameters are identical to those in the <em>Simple Simulation</em> form.</p>

                        <h4>Simulation Parameters File</h4>
                        <p>
                            The simulation parameter file specifies the list of de-regulated biological elements to be
                            simulated. It is tab-separated textual file, where each line specifies the identifier of a
                            biological element with the pathways (or the enrichment database) together with type of
                            deregulation (<strong>OVEREXPRESSION</strong>, <strong>UNDEREXPRESSION</strong>).
                        </p>
                        <x-docs.api.code-block class="mx-4"
                                               title="Example of Simulation Parameters File:">{{ "7157\tOVEREXPRESSION\n2475\tUNDEREXPRESSION\nhsa-miR-7-5p\tOVEREXPRESSION" }}</x-docs.api.code-block>

                        <h4 class="mt-4">Enrichment Database File</h4>
                        <p>
                            The enrichment database file is a tab-separated text file that contains a row for each edge
                            that should be added to PHENSIM meta-pathway. Each row contains 9 fields:</p>
                        <ul>
                            <li><strong>source node id</strong>: an arbitrarily chosen identifier which is used to
                                uniquely represent the new node;
                            </li>
                            <li><strong>source node name</strong>: a name displayed to the user;</li>
                            <li><strong>source node type</strong>: a type for the node (use one from below or specify
                                custom ones using the appropriate file);
                            </li>
                            <li><strong>destination node id</strong>: an identifier for the destination node (it must be
                                already present in the meta-pathway);
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
                            <li><strong>optional filter</strong>: a string that can be used in the <strong>Database
                                    Filter</strong> field to select only some parts of this file.
                            </li>
                        </ul>
                        <p>
                            For the enrichment process to be successful, the destination node must be already within the
                            pathway. If the destination node is not found, the edge will not be added. The source node
                            can be absent. It will be automatically created in the enrichment phase.
                        </p>

                        <x-docs.api.code-block class="mx-4"
                                               title="Example of Enrichment Database File:">{{
                         "VES1\tVesicle 1\tVESICLE\t7157\tTP53\tGENE\tVESICLE_EDGE\tACTIVATION\tVesicle1\n".
                         "VES1\tVesicle 1\tVESICLE\t2475\tMTOR\tGENE\tVESICLE_EDGE\tINHIBITION\tVesicle1\n".
                         "VES1\tVesicle 1\tVESICLE\thsa-miR-7-5p\t\tMIRNA\tVESICLE_EDGE\tINHIBITION\tVesicle1\n".
                         "VES2\tVesicle 2\tVESICLE\thsa-let-7e-5p\t\tMIRNA\tVESICLE_EDGE\tINHIBITION\tVesicle2\n".
                         "VES2\tVesicle 2\tVESICLE\thsa-miR-21-3p\t\tMIRNA\tVESICLE_EDGE\tACTIVATION\tVesicle2\n".
                         "VES2\tVesicle 2\tVESICLE\t57527\tRPTOR\tGENE\tVESICLE_EDGE\tACTIVATION\tVesicle2\n"
                        }}</x-docs.api.code-block>

                        <p>
                            The previous sample file contains two different vesicles. To add only the first one for the
                            simulation process, you can specify the filter value <strong>Vescicle1</strong> in the
                            <strong>Database Filter</strong> field of the <strong>Advanced Simulation</strong> form.
                        </p>

                        <h4 class="mt-4">Custom Node Type File</h4>
                        <p>
                            The custom node type file is an optional tab-separated text file where each row represents
                            a new node type. Each node type consists of a name and a sign used for the computation of
                            pathway accumulator. The sign represents the action of such an element on the whole pathway.
                            If the elements is an activator of the pathway the sign should be positive, negative
                            otherwise.
                        </p>

                        <x-docs.api.code-block class="mx-4"
                                               title="Example:">{{"VESICLE\t+1\nDRUG\t-1"}}</x-docs.api.code-block>

                        <h4 class="mt-4">Custom Edge Type File</h4>
                        <p>
                            The custom edge type file is an optional tab-separated text file where each row represents
                            a new edge type. Only the name of the edge type is needed. The edge subtype represents the
                            action undertaken by the edge.
                        </p>

                        <x-docs.api.code-block class="mx-4"
                                               title="Example:">{{"VESICLE_EDGE\nDRUG_EDGE"}}</x-docs.api.code-block>

                        <h4 class="mt-4">Custom Edge Subtypes File</h4>
                        <p>
                            The custom edge subtypes file is an optional tab-separated text file where each row
                            represents a new edge subtype. The edge subtype explain the effect that the source node
                            has on the target node (eg activation or inhibition), and is fundamental for the correct
                            computation of PHENSIM perturbation and activity score.
                            Each row in the enrichment file has 2 fields: the name of the subtype, a weight used
                            to represent the effect (+1 activation, -1 inhibition, 0 no effect).
                            An optional third field might be specified. The third field represents the priority of
                            the edge when two edges between the same nodes are present. If between two nodes we have
                            multiple interactions, only the ones with the highest priority will be kept in the
                            meta-pathway.
                        </p>
                        <x-docs.api.code-block class="mx-4"
                                               title="Example:">{{"VESICLE_CONTAINS\t0\nVESCICLE_ACTIVATE\t+1\nVESICLE_INHIBIT\t-1\nDRUG_INHIBIT\t-1"}}</x-docs.api.code-block>

                        <h4 class="mt-4">Default Node Types</h4>
                        <div class="mx-8">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td>GENE</td>
                                        <td>A gene identified by Entrez Id</td>
                                    </tr>
                                    <tr>
                                        <td>COMPOUND</td>
                                        <td>A compound identified by KEGG Id or CHEBI Id</td>
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
                        </div>

                        <h4 class="mt-4">Default Edge Types</h4>
                        <div class="mx-8">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td>ECREL</td>
                                        <td>enzyme-enzyme relation, indicating two enzymes catalyzing successive
                                            reaction
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
                        </div>

                        <h4 class="mt-4">Default Edge Subtypes</h4>
                        <div class="mx-8">
                            <table class="table">
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
        </div>

        @include('layouts.footers.auth')
    </div>

@endsection
