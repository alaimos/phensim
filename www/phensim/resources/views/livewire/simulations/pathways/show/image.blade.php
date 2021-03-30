<div>
    <a href="#" wire:click.prevent="showImage()" class="btn btn-primary" wire:loading.remove>
        View pathway image
    </a>
    <a href="#" class="btn btn-primary" wire:loading>
        <i class="fas fa-spinner fa-pulse"></i> Please wait...
    </a>
    <x-modal wire:model="displayingImage" scrollable="true" width="xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pathway Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img src="{{ $image }}" alt="{{ $pathway }}" style="width: 100%">
            </div>
            <div class="modal-footer justify-content-end align-items-center">
                <div class="text-right">
                    <button type="button" wire:click="$set('displayingImage', false)" wire:loading.attr="disabled"
                            class="btn btn-secondary">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </x-modal>
</div>
