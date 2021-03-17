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
                <th scope="col" wire:click="sortByColumn('status')">
                    Status
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
                    <select class="form-control form-control-sm" wire:model="searchColumns.status">
                        <option value="-1">-- Choose Status --</option>
                        @foreach(\App\Models\Simulation::STATE_NAMES as $value => $name)
                            <option value="{{ $value }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                </td>
                <td>
                </td>
            </tr>
        </thead>
        <tbody>
            @forelse($simulations as $simulation)
                <tr>
                    <td>{{ $simulation->name }}</td>
                    <td>{{ \App\Models\Simulation::STATE_NAMES[$simulation->status] }}</td>
                    <td>{{ $simulation->created_at->diffForHumans() }}</td>
                    <td>
                        @if($simulation->isReady())
                            <a href="#"
                               wire:click.prevent="submitSimulation({{ $simulation->id }})"
                               title="Submit simulation">
                                <i class="fas fa-play fa-fw"></i>
                            </a>
                        @endif
                        @if($simulation->canBeDeleted())
                            <a href="#"
                               wire:click.prevent="confirmSimulationDeletion({{ $simulation->id }})"
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
                        There are no simulations here!
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $simulations->links() }}
</div>
