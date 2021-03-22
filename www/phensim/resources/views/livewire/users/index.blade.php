<div>
    <div class="card shadow">
        <div class="card-header border-0">
            <div class="row align-items-center">
                <div class="col">
                    &nbsp;
                </div>
                <div class="col-4 d-flex flex-row-reverse">
                    <a href="#" class="btn btn-sm btn-primary" wire:click.prevent="openModal()">
                        New user
                    </a>
                    <div class="mr-2" wire:loading.delay>
                        <i class="fas fa-spinner fa-pulse"></i> Loading...
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center table-flush">
                <thead class="thead-light">
                    <tr>
                        <th scope="col" wire:click="sortByColumn('name')">
                            Name
                            @if ($sortColumn === 'name')
                                <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                            @else
                                <i class="fa fa-fw fa-sort"></i>
                            @endif
                        </th>
                        <th scope="col" wire:click="sortByColumn('email')">
                            E-Mail
                            @if ($sortColumn === 'status')
                                <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down' }}"></i>
                            @else
                                <i class="fa fa-fw fa-sort"></i>
                            @endif
                        </th>
                        <th scope="col" wire:click="sortByColumn('created_at')">
                            Creation Date
                            @if ($sortColumn === 'created_at')
                                <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                            @else
                                <i class="fa fa-fw fa-sort"></i>
                            @endif
                        </th>
                        <th scope="col">
                            Actions
                        </th>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" class="form-control form-control-sm" wire:model="searchColumns.name"/>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" wire:model="searchColumns.email"/>
                        </td>
                        <td>
                        </td>
                        <td>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="#" data-toggle="tooltip" data-placement="top"
                                   wire:click.prevent="openModal({{ $user->id }})"
                                   title="Edit">
                                    <i class="fas fa-pencil-alt fa-fw"></i>
                                </a>
                                @if (auth()->id() !== $user->id)
                                    <a href="#" data-toggle="tooltip" data-placement="top"
                                       wire:click.prevent="confirmUserDeletion({{ $user->id }})"
                                       class="text-danger"
                                       title="Delete">
                                        <i class="fas fa-trash fa-fw"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                There are no users here!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-4">
            <nav class="d-flex justify-content-end">
                {{ $users->links() }}
            </nav>
        </div>
    </div>
    <x-modal wire:model="displayingModal" scrollable="true" width="xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@if ($currentUserId) Edit User @else New User @endif</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" wire:submit.prevent="saveUser">
                    <div class="form-group @error('currentUser.name')  has-danger @enderror">
                        <div class="input-group input-group-alternative mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="ni ni-hat-3"></i></span>
                            </div>
                            <input class="form-control @error('currentUser.name') is-invalid @enderror"
                                   placeholder="{{ __('Name') }}" type="text" name="name"
                                   wire:model.lazy="currentUser.name" required autofocus>
                        </div>
                        @error('currentUser.name')
                        <span class="invalid-feedback" style="display: block;" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group @error('currentUser.affiliation')  has-danger @enderror">
                        <div class="input-group input-group-alternative mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="ni ni-building"></i></span>
                            </div>
                            <input class="form-control @error('currentUser.affiliation') is-invalid @enderror"
                                   placeholder="{{ __('Affiliation') }}" type="text" name="affiliation"
                                   wire:model.lazy="currentUser.affiliation" required autofocus>
                        </div>
                        @error('currentUser.affiliation')
                        <span class="invalid-feedback" style="display: block;" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group @error('currentUser.email')  has-danger @enderror">
                        <div class="input-group input-group-alternative mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                            </div>
                            <input class="form-control @error('currentUser.email') is-invalid @enderror"
                                   placeholder="{{ __('Email') }}" type="email" name="email"
                                   wire:model.lazy="currentUser.email" required>
                        </div>
                        @error('currentUser.email')
                        <span class="invalid-feedback" style="display: block;" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group @error('currentUser.password')  has-danger @enderror">
                        <div class="input-group input-group-alternative">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                            </div>
                            <input class="form-control @error('currentUser.password') is-invalid @enderror"
                                   placeholder="{{ __('Password') }}" type="password" name="password"
                                   wire:model="currentUser.password"
                                   @if ($currentUserId) required @endif>
                        </div>
                        @error('currentUser.password')
                        <span class="invalid-feedback" style="display: block;" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between align-items-center">
                <div class="text-left">
                    <button type="button" wire:click="saveUser"
                            wire:loading.attr="disabled"
                            class="btn btn-success">
                        Save
                    </button>
                </div>
                <div class="text-right">
                    <button type="button" wire:click="$set('displayingModal', false)"
                            wire:loading.attr="disabled"
                            class="btn btn-secondary">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </x-modal>

</div>
