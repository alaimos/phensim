<div>
    <form wire:submit.prevent="update" autocomplete="off">
        <h6 class="heading-small text-muted mb-4">{{ __('User information') }}</h6>

        @if (session()->has('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
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
            <div class="form-group @error('state.affiliation') has-danger @enderror">
                <label class="form-control-label"
                       for="input-affiliation">{{ __('Affiliation') }}</label>
                <input type="text" name="affiliation" id="input-affiliation"
                       class="form-control form-control-alternative @error('state.affiliation') is-invalid @enderror"
                       placeholder="{{ __('Affiliation') }}"
                       wire:model.defer="state.affiliation" required>

                @error('state.affiliation')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group @error('state.email') has-danger @enderror">
                <label class="form-control-label" for="input-email">{{ __('Email') }}</label>
                <input type="email" name="email" id="input-email"
                       class="form-control form-control-alternative @error('state.email') is-invalid @enderror"
                       placeholder="{{ __('Email') }}"
                       wire:model.defer="state.email" required>

                @error('state.email')
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
