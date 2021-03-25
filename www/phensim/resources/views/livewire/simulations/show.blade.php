<div class="card shadow mt-4">
    <div class="card-header border-0">
        <div class="row">
            <div class="col-6">
                <h3 class="mb-0">Results by pathway</h3>
            </div>
            <div class="col-6 text-right">
                <div wire:loading.delay>
                    <i class="fas fa-spinner fa-pulse"></i> Loading...
                </div>
            </div>
        </div>
    </div>
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
                    <td>
                    </td>
                </tr>
            </thead>
            <tbody>
                @forelse($pathways as $pathway)
                    <tr>
                        <td>{{ $pathway['pathwayId'] }}</td>
                        <td>{{ $pathway['pathwayName'] }}</td>
                        <td class="text-right">{{ \App\PHENSIM\Utils::formatDouble($pathway['pathwayActivityScore']) }}</td>
                        <td class="text-right">{{ \App\PHENSIM\Utils::formatDouble($pathway['averagePathwayPerturbation']) }}</td>
                        <td class="text-right">{{ \App\PHENSIM\Utils::formatDouble($pathway['pathwayPValue']) }}</td>
                        <td class="text-right">{{ \App\PHENSIM\Utils::formatDouble($pathway['pathwayFDR']) }}</td>
                        <td class="text-center">
                            <a href="{{ route('simulations.pathways.show', [$simulation, $pathway['pathwayId']]) }}"
                               data-tippy-content="Show pathway"
                               title="Show pathway">
                                <i class="fas fa-eye fa-fw"></i>
                            </a>
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
    </div>
    <div class="card-footer py-4">
        <nav class="d-flex justify-content-end">
            {{ $pathways->links() }}
        </nav>
    </div>
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
