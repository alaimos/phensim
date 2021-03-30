<div>
    <div class="card shadow">
        <div class="card-header border-0">
            <div class="row align-items-center">
                <div class="col">
                    &nbsp;
                </div>
                <div class="col-4 d-flex flex-row-reverse">
                    <a href="#" class="btn btn-sm btn-primary" wire:click.prevent="openModal()">
                        New message
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
                        <th scope="col" wire:click="sortByColumn('title')">
                            Title
                            @if ($sortColumn === 'title')
                                <i class="fa fa-fw fa-sort-{{ $sortDirection === 'asc' ?  'up' : 'down'  }}"></i>
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
                            <input type="text" class="form-control form-control-sm" wire:model="searchColumns.title"/>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr>
                            <td>{{ $message->title }}</td>
                            <td>{{ $message->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="#" wire:click.prevent="openModal({{ $message->id }})"
                                   data-tippy-content="Edit"
                                   title="Edit">
                                    <i class="fas fa-pencil-alt fa-fw"></i>
                                </a>
                                <a href="#" wire:click.prevent="confirmMessageDeletion({{ $message->id }})"
                                   class="text-danger"
                                   data-tippy-content="Delete"
                                   title="Delete">
                                    <i class="fas fa-trash fa-fw"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                There are no messages here!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-4">
            <nav class="d-flex justify-content-end">
                {{ $messages->links() }}
            </nav>
        </div>
    </div>
    <x-modal wire:model="displayingModal" scrollable="true" width="xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@if ($currentMessageId) Edit Message @else New Message @endif</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" wire:submit.prevent="saveMessage">
                    <div class="form-group @error('currentMessage.title') has-danger @enderror">
                        <label class="form-control-label" for="input-name">{{ __('Title') }}</label>
                        <input type="text" name="name" id="input-name"
                               class="form-control form-control-alternative @error('currentMessage.title') is-invalid @enderror"
                               wire:model.defer="currentMessage.title"
                               required autofocus>
                        @error('currentMessage.title')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group @error('currentMessage.message') has-danger @enderror">
                        <label class="form-control-label" for="input-message">{{ __('Message') }}</label>
                        <textarea id="input-message"
                                  class="form-control form-control-alternative @error('currentMessage.message') is-invalid @enderror"
                                  wire:model.defer="currentMessage.message"></textarea>
                        @error('currentMessage.message')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between align-items-center">
                <div class="text-left">
                    <button type="button" wire:click="saveMessage"
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
