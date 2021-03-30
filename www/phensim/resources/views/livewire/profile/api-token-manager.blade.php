<div class="card card-profile shadow my-4">
    <div class="card-body pt-0 pt-md-4">
        <div class="row">
            <div class="col-md-12">
                <div class="px-4 px-sm-0">
                    <h3 class="h5">{{ __('Create API Token') }}</h3>

                    <p class="mt-1 text-muted">
                        {{ __('API tokens allow third-party services to authenticate with our application on your behalf.') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form wire:submit.prevent="createApiToken" autocomplete="off">
                    <div class="alert alert-success small" role="alert" x-data="{ shown: false, timeout: null }"
                         x-init="@this.on('tokenCreated', () => { clearTimeout(timeout); shown = true; timeout = setTimeout(() => { shown = false }, 2000);  })"
                         x-show.transition.opacity.out.duration.1500ms="shown"
                         style="display: none;">
                        <div class="alert-body">
                            {{ __('The token has been created') }}
                        </div>
                    </div>

                    <div class="pl-lg-4">
                        <div class="form-group @error('createApiTokenForm.name') has-danger @enderror">
                            <label class="form-control-label" for="input-token-name">{{ __('Name') }}</label>
                            <input type="text" id="input-token-name"
                                   class="form-control form-control-alternative @error('createApiTokenForm.name') is-invalid @enderror"
                                   placeholder="{{ __('Name') }}"
                                   wire:model.defer="createApiTokenForm.name">
                            @error('createApiTokenForm.name')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-success mt-4">{{ __('Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @if (auth()->user()->tokens->isNotEmpty())
            <hr class="my-4"/>
            <div class="row">
                <div class="col-md-12">
                    <div class="px-4 px-sm-0">
                        <h3 class="h5">{{ __('Manage API Tokens') }}</h3>

                        <p class="mt-1 text-muted">
                            {{ __('You may delete any of your existing tokens if they are no longer needed.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div>
                        @foreach (auth()->user()->tokens->sortBy('name') as $token)
                            <div class="d-flex justify-content-between">
                                <div>
                                    {{ $token->name }}
                                </div>

                                <div class="d-flex">
                                    @if ($token->last_used_at)
                                        <div class="text-sm text-muted">
                                            {{ __('Last used') }} {{ $token->last_used_at->diffForHumans() }}
                                        </div>
                                    @endif

                                    <button class="btn btn-link text-danger text-decoration-none"
                                            wire:click="confirmApiTokenDeletion({{ $token->id }})">
                                        {{ __('Delete') }}
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>


    <x-modal wire:model="displayingToken">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('API Token') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    {{ __('Please copy your new API token. For your security, it won\'t be shown again.') }}
                </div>

                <div class="form-group">
                    <input x-ref="plaintextToken" type="text" readonly value="{{ $plainTextToken }}"
                           autofocus autocomplete="off" autocorrect="off" autocapitalize="off"
                           spellcheck="false" class="form-control"
                           @showing-token-modal.window="setTimeout(() => $refs.plaintextToken.select(), 250)">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" wire:click="$set('displayingToken', false)" wire:loading.attr="disabled"
                        class="btn btn-secondary">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </x-modal>

</div>
