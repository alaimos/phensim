<div>
    <form wire:submit.prevent="save" autocomplete="off">

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <h6 class="heading-small mb-4">1. Choose a name for the simulation</h6>

        <div class="pl-lg-4">
            <div class="form-group @error('state.name') has-danger @enderror">
                <label class="form-control-label" for="input-name">{{ __('Name') }}</label>
                <input type="text" name="name" id="input-name"
                       class="form-control form-control-alternative @error('state.name') is-invalid @enderror"
                       placeholder="{{ __('Name') }}"
                       wire:model.defer="state.name"
                       required autofocus>
                @error('state.name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>

        <h6 class="heading-small mb-4 mt-4">2. Select the organism</h6>

        <div class="pl-lg-4">
            <div class="form-group @error('state.organism') has-danger @enderror">
                <select class="form-control form-control-alternative @error('state.organism') is-invalid @enderror"
                        wire:model="state.organism">
                    <option value=""> -- Select an organism --</option>
                    @foreach($organisms as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                @error('state.organism')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>

        @if ($state['organism'] && $nodes)
            <h6 class="heading-small mb-2">3. Select the simulation parameters</h6>
            <p class="text-muted mb-4 pl-lg-4">
                You must select at least one overexpressed or underexpressed node to
                create the simulation.
            </p>
            <div class="pl-lg-4">
                <div class="form-group">
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" wire:click="sortByColumn('accession')">
                                        Accession
                                        @if ($sortColumn === 'accession')
                                            <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                                        @else
                                            <i class="fa fa-fw fa-sort"></i>
                                        @endif
                                    </th>
                                    <th scope="col" wire:click="sortByColumn('name')">
                                        Name
                                        @if ($sortColumn === 'name')
                                            <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down' }}"></i>
                                        @else
                                            <i class="fa fa-fw fa-sort"></i>
                                        @endif
                                    </th>
                                    <th scope="col">&nbsp;</th>
                                    <th scope="col">&nbsp;</th>
                                    <th scope="col">&nbsp;</th>
                                    <th scope="col">&nbsp;</th>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control form-control-sm"
                                               wire:model="searchColumns.accession"/>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm"
                                               wire:model="searchColumns.name"/>
                                    </td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nodes as $node)
                                    <tr>
                                        <td>{{ $node->accession }}</td>
                                        <td>{{ $node->name }}</td>
                                        <td>
                                            @if($canBeShown($node->accession, 'over'))
                                                <a href="#"
                                                   wire:click.prevent="toggleSelection('{{ $node->accession }}', 'over')"
                                                   class="@if($isSelected($node->accession, 'over')) text-muted @else text-red @endif"
                                                   title="Set as overexpressed">
                                                    <i class="fas fa-level-up-alt fa-fw"></i>
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if($canBeShown($node->accession, 'under'))
                                                <a href="#"
                                                   wire:click.prevent="toggleSelection('{{ $node->accession }}', 'under')"
                                                   class="@if($isSelected($node->accession, 'under')) text-muted @else text-blue @endif"
                                                   title="Set as underexpressed">
                                                    <i class="fas fa-level-down-alt fa-fw"></i>
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if($canBeShown($node->accession, 'nonExpressed'))
                                                <a href="#"
                                                   wire:click.prevent="toggleSelection('{{ $node->accession }}', 'nonExpressed')"
                                                   class="@if($isSelected($node->accession, 'nonExpressed')) text-muted @else text-red @endif"
                                                   title="Set as non-expressed">
                                                    <i class="fas fa-ban fa-fw"></i>
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if($canBeShown($node->accession, 'knockout'))
                                                <a href="#"
                                                   wire:click.prevent="toggleSelection('{{ $node->accession }}', 'knockout')"
                                                   class="@if($isSelected($node->accession, 'knockout')) text-muted @else text-black-50 @endif"
                                                   title="Set as knocked-out">
                                                    <i class="fas fa-times fa-fw"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $nodes->links() }}
                    </div>
                    @error('state.nodes.over')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    @error('state.nodes.under')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    @error('state.nodes.nonExpressed')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    @error('state.nodes.knockout')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            @if (count($state['nodes']['over']) > 0 || count($state['nodes']['under']) > 0)
                <h6 class="heading-small mb-4">4. Select optional parameters</h6>
                <div class="pl-lg-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group @error('state.epsilon') has-danger @enderror">
                                <label class="form-control-label"
                                       for="input-epsilon">{{ __('Epsilon Threshold') }}</label>
                                <input type="number" id="input-epsilon"
                                       class="form-control form-control-alternative @error('state.epsilon') is-invalid @enderror"
                                       wire:model.defer="state.epsilon"
                                       min="0" max="1" step="0.00001">
                                @error('state.epsilon')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group @error('state.seed') has-danger @enderror">
                                <label class="form-control-label"
                                       for="input-seed">{{ __('RNG Seed') }}</label>
                                <input type="number" id="input-seed"
                                       class="form-control form-control-alternative @error('state.seed') is-invalid @enderror"
                                       wire:model.defer="state.seed"
                                       step="1">
                                @error('state.seed')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group @error('state.fdr') has-danger @enderror">
                                <label class="form-control-label" for="input-fdr">{{ __('FDR Method') }}</label>
                                <select id="input-fdr"
                                        class="form-control form-control-alternative @error('state.fdr') is-invalid @enderror"
                                        wire:model.defer="state.fdr">
                                    @foreach(\App\PHENSIM\Launcher::FDRS_NAMES as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('state.fdr')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="custom-control custom-control-alternative custom-checkbox mb-3">
                                <input class="custom-control-input" id="input-reactome" type="checkbox"
                                       wire:model.defer="state.reactome">
                                <label class="custom-control-label" for="input-reactome">Add REACTOME pathways?</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-control-alternative custom-checkbox mb-3">
                                <input class="custom-control-input" id="input-miRNAs" type="checkbox"
                                       wire:model="state.miRNAs">
                                <label class="custom-control-label" for="input-miRNAs">Add miRNAs to pathways?</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            @if ($state['miRNAs'])
                                <select
                                    class="form-control form-control-alternative @error('state.miRNAsEvidence') is-invalid @enderror"
                                    wire:model="state.miRNAsEvidence">
                                    <option value=""> -- Select an Evidence Level --</option>
                                    @foreach(\App\PHENSIM\Launcher::SUPPORTED_EVIDENCE_NAMES as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-control-alternative custom-checkbox mb-3">
                                <input class="custom-control-input" id="input-fast" type="checkbox"
                                       wire:model.defer="state.fast">
                                <label class="custom-control-label" for="input-fast">Use the fast algorithm?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <h6 class="heading-small mb-4">5. Submit the simulation</h6>
                <div class="pl-lg-4">
                    <div class="text-center">
                        <button type="submit" class="btn btn-success mt-4">{{ __('Create simulation') }}</button>
                    </div>
                </div>
            @endif
        @endif
    </form>
</div>
