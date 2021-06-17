<div>
    <form
        wire:submit.prevent="save" ù
        autocomplete="off"
        x-data="{ isUploading: false, progress: 0 }"
        x-on:livewire-upload-start="isUploading = true"
        x-on:livewire-upload-finish="isUploading = false"
        x-on:livewire-upload-error="isUploading = false"
        x-on:livewire-upload-progress="isUploading = true; progress = $event.detail.progress">

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

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
            <div class="form-group @error('state.organism') has-danger @enderror">
                <label class="form-control-label" for="input-organism">{{ __('Organism') }}</label>
                <select id="input-organism"
                        class="form-control form-control-alternative @error('state.organism') is-invalid @enderror"
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
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group @error('simulationParametersFile') has-danger @enderror">
                        <label class="form-control-label"
                               for="input-simulationParametersFile">{{ __('Simulation Parameters') }}</label>
                        <input type="file"
                               class="form-control form-control-alternative @error('simulationParametersFile') is-invalid @enderror"
                               id="input-simulationParametersFile"
                               wire:model="simulationParametersFile">
                        @error('simulationParametersFile')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group @error('nonExpressedNodesFile') has-danger @enderror">
                        <label class="form-control-label"
                               for="input-nonExpressedNodesFile">{{ __('Non-expressed nodes') }}</label>
                        <input type="file"
                               class="form-control form-control-alternative @error('nonExpressedNodesFile') is-invalid @enderror"
                               id="input-nonExpressedNodesFile"
                               wire:model="nonExpressedNodesFile">
                        @error('nonExpressedNodesFile')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group @error('enrichmentDatabaseFile') has-danger @enderror">
                        <label class="form-control-label"
                               for="input-enrichmentDatabaseFile">{{ __('Enrichment Database File') }}</label>
                        <input type="file"
                               class="form-control form-control-alternative @error('enrichmentDatabaseFile') is-invalid @enderror"
                               id="input-enrichmentDatabaseFile"
                               wire:model="enrichmentDatabaseFile">
                        @error('enrichmentDatabaseFile')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group @error('state.filter') has-danger @enderror">
                        <label class="form-control-label" for="input-db-filter">{{ __('Database Filter') }}</label>
                        <input type="text" name="filter" id="input-db-filter"
                               class="form-control form-control-alternative @error('state.filter') is-invalid @enderror"
                               wire:model.defer="state.filter">
                        @error('state.filter')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group @error('customNodeTypesFile') has-danger @enderror">
                        <label class="form-control-label"
                               for="input-customNodeTypesFile">{{ __('Custom Node Types File') }}</label>
                        <input type="file"
                               class="form-control form-control-alternative @error('customNodeTypesFile') is-invalid @enderror"
                               id="input-customNodeTypesFile"
                               wire:model="customNodeTypesFile">
                        @error('customNodeTypesFile')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group @error('customEdgeTypesFile') has-danger @enderror">
                        <label class="form-control-label"
                               for="input-customEdgeTypesFile">{{ __('Custom Edge Types File') }}</label>
                        <input type="file"
                               class="form-control form-control-alternative @error('customEdgeTypesFile') is-invalid @enderror"
                               id="input-customEdgeTypesFile"
                               wire:model="customEdgeTypesFile">
                        @error('customEdgeTypesFile')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group @error('customEdgeSubtypesFile') has-danger @enderror">
                        <label class="form-control-label"
                               for="input-customEdgeSubtypesFile">{{ __('Custom Edge Subtypes File') }}</label>
                        <input type="file"
                               class="form-control form-control-alternative @error('customEdgeSubtypesFile') is-invalid @enderror"
                               id="input-customEdgeSubtypesFile"
                               wire:model="customEdgeSubtypesFile">
                        @error('customEdgeSubtypesFile')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group @error('knockoutNodesFile') has-danger @enderror">
                        <label class="form-control-label"
                               for="input-knockoutNodesFile">{{ __('Knocked-out nodes') }}</label>
                        <input type="file"
                               class="form-control form-control-alternative @error('knockoutNodesFile') is-invalid @enderror"
                               id="input-knockoutNodesFile"
                               wire:model="knockoutNodesFile">
                        @error('knockoutNodesFile')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            </div>
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
            <div class="text-center" x-show="!isUploading">
                <button type="submit" class="btn btn-success mt-4">{{ __('Create simulation') }}</button>
            </div>
            <div class="text-center" x-show="isUploading">
                <button type="submit" class="btn btn-success mt-4" disabled>{{ __('Uploading...Please wait...') }}</button>
            </div>
            <div class="text-center" x-show="isUploading">
                <progress max="100" x-bind:value="progress"></progress>
            </div>
        </div>

    </form>
</div>
