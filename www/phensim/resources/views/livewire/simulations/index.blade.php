<div>
    <div class="card shadow">
        <div class="card-header border-0">
            <div class="row align-items-center">
                <div class="col-4 text-left">
                    <a href="{{ route('simulations.create.simple') }}" class="btn btn-sm btn-primary">
                        New simple simulation
                    </a>
                </div>
                <div class="col">
                    &nbsp;
                </div>
                <div class="col-4 d-flex flex-row-reverse">
                    <a href="{{ route('simulations.create.advanced') }}" class="btn btn-sm btn-primary">
                        New advanced simulation
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
                        <th scope="col" wire:click="sortByColumn('id')">
                            #
                            @if ($sortColumn === 'id')
                                <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                            @else
                                <i class="fa fa-fw fa-sort"></i>
                            @endif
                        </th>
                        <th scope="col" wire:click="sortByColumn('name')">
                            Name
                            @if ($sortColumn === 'name')
                                <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                            @else
                                <i class="fa fa-fw fa-sort"></i>
                            @endif
                        </th>
                        <th scope="col" wire:click="sortByColumn('status')">
                            Status
                            @if ($sortColumn === 'status')
                                <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down' }}"></i>
                            @else
                                <i class="fa fa-fw fa-sort"></i>
                            @endif
                        </th>
                        @if (auth()->user()->is_admin)
                            <th scope="col" wire:click="sortByColumn('user_name')">
                                User
                                @if ($sortColumn === 'user_name')
                                    <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down' }}"></i>
                                @else
                                    <i class="fa fa-fw fa-sort"></i>
                                @endif
                            </th>
                        @endif
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
                        <td></td>
                        <td>
                            <input type="text" class="form-control form-control-sm" wire:model="searchColumns.name"/>
                        </td>
                        <td>
                            <select class="form-control form-control-sm" wire:model="searchColumns.status">
                                <option value="-1">-- Choose Status --</option>
                                @foreach(\App\Models\Simulation::STATE_NAMES as $value => $name)
                                    <option value="{{ $value }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </td>
                        @if (auth()->user()->is_admin)
                            <td><input type="text" class="form-control form-control-sm"
                                       wire:model="searchColumns.user_name"/></td>
                        @endif
                        <td>
                        </td>
                        <td>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    @forelse($simulations as $simulation)
                        <tr>
                            <td>{{ $simulation->id }}</td>
                            <td>{{ $simulation->name }}</td>
                            <td>{{ \App\Models\Simulation::STATE_NAMES[$simulation->status] }}</td>
                            @if (auth()->user()->is_admin)
                                <td>{{ $simulation->user_name }}</td>
                            @endif
                            <td>{{ $simulation->created_at->diffForHumans() }}</td>
                            <td>
                                @if($simulation->isReady())
                                    <a href="#" wire:click.prevent="confirmSimulationSubmission({{ $simulation->id }})"
                                       data-tippy-content="Submit simulation"
                                       title="Submit simulation">
                                        <i class="fas fa-play fa-fw"></i>
                                    </a>
                                @elseif ($simulation->isFailed() || ($simulation->isCompleted() && auth()->user()->is_admin))
                                    <a href="#"
                                       wire:click.prevent="confirmSimulationReSubmission({{ $simulation->id }})"
                                       data-tippy-content="Resubmit simulation"
                                       title="Resubmit simulation">
                                        <i class="fas fa-redo fa-fw"></i>
                                    </a>
                                @endif
                                @if($simulation->hasLogs())
                                    <a href="#" wire:click.prevent="displayLogs({{ $simulation->id }})"
                                       data-tippy-content="Show logs"
                                       title="Show logs">
                                        <i class="fas fa-file-alt fa-fw"></i>
                                    </a>
                                @endif
                                @if($simulation->isCompleted())
                                    <a href="{{ route('simulations.show', $simulation) }}"
                                       data-tippy-content="Show simulation"
                                       title="Show simulation">
                                        <i class="fas fa-eye fa-fw"></i>
                                    </a>
                                @endif
                                @if($simulation->canBeDeleted())
                                    <a href="#" wire:click.prevent="confirmSimulationDeletion({{ $simulation->id }})"
                                       class="text-danger"
                                       data-tippy-content="Delete"
                                       title="Delete">
                                        <i class="fas fa-trash fa-fw"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                There are no simulations here!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-4">
            <nav class="d-flex justify-content-end">
                {{ $simulations->links() }}
            </nav>
        </div>
    </div>
    <x-modal wire:model="displayingLog" scrollable="true" width="xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Logs</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if($displayingLog && $currentSimulation)
                    @if ($currentSimulation->isProcessing())
                        <pre wire:poll.30s>{{ $currentSimulation->logs }}</pre>
                    @else
                        <pre>{{ $currentSimulation->logs }}</pre>
                    @endif
                @endif
            </div>
            <div class="modal-footer justify-content-between align-items-center">
                <div class="text-left">
                    @if($currentSimulation && $currentSimulation->isProcessing())
                        <i class="fa fa-sync fa-spin fa-fw"></i> Working...
                    @endif
                </div>
                <div class="text-right">
                    <button type="button" wire:click="$set('displayingLog', false)" wire:loading.attr="disabled"
                            class="btn btn-secondary">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </x-modal>
</div>

@push('js')
    <script>
        document.addEventListener('livewire:load', () => {
            tippy('[data-tippy-content]');
            Livewire.hook('message.processed', (message, component) => {
                tippy('[data-tippy-content]');
            });
        });
    </script>
@endpush
