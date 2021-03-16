<div class="card card-profile shadow">
    <div class="card-header text-center border-0 pt-8 pt-md-4 pb-0 pb-md-4">
        <h3 class="mb-0">{{ __('Manage API Tokens') }}</h3>
    </div>
    <div class="card-body pt-0 pt-md-4">
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
