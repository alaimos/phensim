<div>
    <form wire:submit.prevent="update" autocomplete="off">
        <h6 class="heading-small text-muted mb-4">{{ __('Password') }}</h6>

        @if (session()->has('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="pl-lg-4">
            <div class="form-group @error('state.old_password') has-danger @enderror">
                <label class="form-control-label"
                       for="input-current-password">{{ __('Current Password') }}</label>
                <input type="password" name="old_password" id="input-current-password"
                       class="form-control form-control-alternative @error('state.old_password') is-invalid @enderror"
                       placeholder="{{ __('Current Password') }}"
                       wire:model.defer="state.old_password"
                       required>

                @error('state.old_password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group @error('state.password') has-danger @enderror">
                <label class="form-control-label"
                       for="input-password">{{ __('New Password') }}</label>
                <input type="password" name="password" id="input-password"
                       class="form-control form-control-alternative @error('state.password') is-invalid @enderror"
                       placeholder="{{ __('New Password') }}" wire:model.defer="state.password" required>
                @error('state.password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-control-label"
                       for="input-password-confirmation">{{ __('Confirm New Password') }}</label>
                <input type="password" name="password_confirmation" id="input-password-confirmation"
                       class="form-control form-control-alternative"
                       placeholder="{{ __('Confirm New Password') }}" wire:model.defer="state.password_confirmation"
                       required>
            </div>

            <div class="text-center">
                <button type="submit"
                        class="btn btn-success mt-4">{{ __('Change password') }}</button>
            </div>
        </div>
    </form>
</div>
