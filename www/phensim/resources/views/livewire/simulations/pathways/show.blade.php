<div class="card shadow mt-4">
    <div class="card-header border-0">
        <div class="row align-items-center">
            <div class="col-8">
                <h3 class="mb-0">Results</h3>
            </div>
            <div class="col-4 d-flex flex-row-reverse">
                <a href="{{ route('simulations.show', $simulation) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-fw fa-arrow-left"></i>
                    Go back
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
                    <th scope="col" wire:click="sortByColumn('nodeId')">
                        Id
                        @if ($sortColumn === 'nodeId')
                            <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                        @else
                            <i class="fa fa-fw fa-sort"></i>
                        @endif
                    </th>
                    <th scope="col" wire:click="sortByColumn('nodeName')">
                        Name
                        @if ($sortColumn === 'nodeName')
                            <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down' }}"></i>
                        @else
                            <i class="fa fa-fw fa-sort"></i>
                        @endif
                    </th>
                    <th scope="col" wire:click="sortByColumn('isEndpoint')">
                        Endpoint?
                        @if ($sortColumn === 'isEndpoint')
                            <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down' }}"></i>
                        @else
                            <i class="fa fa-fw fa-sort"></i>
                        @endif
                    </th>
                    <th scope="col" wire:click="sortByColumn('activityScore')">
                        Activity Score
                        @if ($sortColumn === 'activityScore')
                            <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                        @else
                            <i class="fa fa-fw fa-sort"></i>
                        @endif
                    </th>
                    <th scope="col" wire:click="sortByColumn('averagePerturbation')">
                        Perturbation
                        @if ($sortColumn === 'averagePerturbation')
                            <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                        @else
                            <i class="fa fa-fw fa-sort"></i>
                        @endif
                    </th>
                    <th scope="col" wire:click="sortByColumn('pValue')">
                        p-value
                        @if ($sortColumn === 'pValue')
                            <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                        @else
                            <i class="fa fa-fw fa-sort"></i>
                        @endif
                    </th>
                    <th scope="col" wire:click="sortByColumn('FDR')">
                        FDR
                        @if ($sortColumn === 'FDR')
                            <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                        @else
                            <i class="fa fa-fw fa-sort"></i>
                        @endif
                    </th>
                </tr>
                <tr>
                    <td>
                        <input type="text" class="form-control form-control-sm" wire:model="searchColumns.nodeId"/>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" wire:model="searchColumns.nodeName"/>
                    </td>
                    <td>
                        <select class="form-control form-control-sm" wire:model="searchColumns.isEndpoint">
                            <option value="">-- No filter --</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </td>
                    @foreach(['activityScore','averagePerturbation','pValue','FDR'] as $field)
                        <td>
                            <div class="row">
                                <div class="col-5 m-0">
                                    <select class="form-control form-control-sm"
                                            wire:model="searchColumns.{{$field}}.operator">
                                        <option value="<">&lt;</option>
                                        <option value="<=">&leq;</option>
                                        <option value="=">=</option>
                                        <option value="!=">&ne;</option>
                                        <option value=">">&gt;</option>
                                        <option value=">=">&geq;</option>
                                    </select>
                                </div>
                                <div class="col m-0">
                                    <input type="text" class="form-control form-control-sm"
                                           wire:model="searchColumns.{{$field}}.value"/>
                                </div>
                            </div>
                        </td>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($nodes as $node)
                    <tr>
                        <td>{{ $node['nodeId'] }}</td>
                        <td>{{ preg_split("/,\\s+/", $node['nodeName'])[0] }}</td>
                        <td class="text-center">
                            <i class="fas fa-fw fa-{{ $node['isEndpoint'] ? 'check' : 'times' }}"></i>
                        </td>
                        <td class="text-right">{{ \App\PHENSIM\Utils::formatDouble($node['activityScore']) }}</td>
                        <td class="text-right">{{ \App\PHENSIM\Utils::formatDouble($node['averagePerturbation']) }}</td>
                        <td class="text-right">{{ \App\PHENSIM\Utils::formatDouble($node['pValue']) }}</td>
                        <td class="text-right">{{ \App\PHENSIM\Utils::formatDouble($node['FDR']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            There are no nodes here!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer py-4">
        <nav class="d-flex justify-content-end">
            {{ $nodes->links() }}
        </nav>
    </div>
</div>
