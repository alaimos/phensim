<div class="table-responsive">
    <table class="table align-items-center table-flush">
        <thead class="thead-light">
            <tr>
                <th scope="col" wire:click="sortByColumn('pathwayId')">
                    Id
                    @if ($sortColumn === 'pathwayId')
                        <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                    @else
                        <i class="fa fa-fw fa-sort"></i>
                    @endif
                </th>
                <th scope="col" wire:click="sortByColumn('pathwayName')">
                    Name
                    @if ($sortColumn === 'pathwayName')
                        <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down' }}"></i>
                    @else
                        <i class="fa fa-fw fa-sort"></i>
                    @endif
                </th>
                <th scope="col" wire:click="sortByColumn('pathwayActivityScore')">
                    Activity Score
                    @if ($sortColumn === 'pathwayActivityScore')
                        <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                    @else
                        <i class="fa fa-fw fa-sort"></i>
                    @endif
                </th>
                <th scope="col" wire:click="sortByColumn('averagePathwayPerturbation')">
                    Perturbation
                    @if ($sortColumn === 'averagePathwayPerturbation')
                        <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                    @else
                        <i class="fa fa-fw fa-sort"></i>
                    @endif
                </th>
                <th scope="col" wire:click="sortByColumn('pathwayPValue')">
                    p-value
                    @if ($sortColumn === 'pathwayPValue')
                        <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                    @else
                        <i class="fa fa-fw fa-sort"></i>
                    @endif
                </th>
                <th scope="col" wire:click="sortByColumn('pathwayFDR')">
                    FDR
                    @if ($sortColumn === 'pathwayFDR')
                        <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
                    @else
                        <i class="fa fa-fw fa-sort"></i>
                    @endif
                </th>
                <th scope="col">
                    &nbsp;
                </th>
            </tr>
            <tr>
                <td>
                    <input type="text" class="form-control form-control-sm" wire:model="searchColumns.pathwayId"/>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" wire:model="searchColumns.pathwayName"/>
                </td>
                @foreach(['pathwayActivityScore','averagePathwayPerturbation','pathwayPValue','pathwayFDR'] as $field)
                    <td>
                        <div class="row">
                            <div class="col-5">
                                <select class="form-control form-control-sm"
                                        wire:model="searchColumns.{{$field}}.operator">
                                    <option value="<">&lt;</option>
                                    <option value="<=">&leq;</option>
                                    <option value="=">=</option>
                                    <option value=">">&gt;</option>
                                    <option value=">=">&geq;</option>
                                </select>
                            </div>
                            <div class="col">
                                <input type="text" class="form-control form-control-sm"
                                       wire:model="searchColumns.{{$field}}.value"/>
                            </div>
                        </div>
                    </td>
                @endforeach
                <td>
                </td>
            </tr>
        </thead>
        <tbody>
            @forelse($pathways as $pathway)
                <tr>
                    <td>{{ $pathway['pathwayId'] }}</td>
                    <td>{{ $pathway['pathwayName'] }}</td>
                    <td>{{ \App\PHENSIM\Utils::formatDouble($pathway['pathwayActivityScore']) }}</td>
                    <td>{{ \App\PHENSIM\Utils::formatDouble($pathway['averagePathwayPerturbation']) }}</td>
                    <td>{{ \App\PHENSIM\Utils::formatDouble($pathway['pathwayPValue']) }}</td>
                    <td>{{ \App\PHENSIM\Utils::formatDouble($pathway['pathwayFDR']) }}</td>
                    <td>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        There are no pathways here!
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $pathways->links() }}
</div>
