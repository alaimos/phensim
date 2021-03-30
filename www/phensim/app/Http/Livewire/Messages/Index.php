<?php

namespace App\Http\Livewire\Messages;

use App\Models\Message;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['receivedConfirmation'];

    public $sortColumn = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $searchColumns = [
        'title' => '',
    ];
    public $displayingModal = false;
    public $currentMessageId = null;
    public $currentMessage = null;

    /**
     * Validation rules for the form
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'currentMessage.title'   => ['required', 'string', 'max:255'],
            'currentMessage.message' => ['required', 'string'],
        ];
    }

    /**
     * Build the query to show the messages
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQuery(): Builder
    {
        $messages = Message::select(['id', 'title', 'created_at']);
        foreach ($this->searchColumns as $column => $value) {
            $messages->where($column, 'LIKE', '%' . $value . '%');
        }
        $messages->orderBy($this->sortColumn, $this->sortDirection);

        return $messages;
    }

    /**
     * Confirm that the given message should be deleted.
     *
     * @param  int  $messageId
     *
     * @return void
     */
    public function confirmMessageDeletion(int $messageId): void
    {
        $this->dispatchBrowserEvent(
            'swal:confirm',
            [
                'type'    => 'delete',
                'icon'    => 'warning',
                'title'   => __('Delete Message'),
                'text'    => __('Are you sure you would like to delete this message?'),
                'id'      => $messageId,
                'buttons' => true,
            ]
        );
    }

    /**
     * Open the modal with the message form
     *
     * @param  int|null  $messageId
     *
     * @return void
     */
    public function openModal(?int $messageId = null): void
    {
        $this->resetErrorBag();
        $this->displayingModal = true;
        $this->currentMessageId = $messageId;
        $this->currentMessage = ($messageId) ? Message::where('id', $messageId)->firstOrFail()->toArray() : [];
    }

    /**
     * Delete the message.
     *
     * @param  \App\Models\Message  $message
     *
     * @return void
     * @throws \Exception
     */
    public function receivedConfirmation(Message $message): void
    {
        $message->delete();
    }

    /**
     * Save the current message
     *
     * @return void
     */
    public function saveMessage(): void
    {
        $validData = $this->validate();
        $currentMessage = $validData['currentMessage'];
        $message = is_null($this->currentMessageId) ? new Message() : Message::where('id', $this->currentMessageId)->firstOrFail();
        $message->fill($currentMessage)->save();
        $this->displayingModal = false;
        $this->dispatchBrowserEvent(
            'swal:confirm',
            [
                'type'    => 'success',
                'icon'    => 'success',
                'title'   => __('Message Saved'),
                'text'    => __('The message has been successfully saved'),
                'buttons' => false,
                'danger'  => false,
                'timer'   => 3000,
            ]
        );
    }

    /**
     * Set column sorting
     *
     * @param  string  $column
     *
     * @retun void
     */
    public function sortByColumn(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->reset('sortDirection');
            $this->sortColumn = $column;
        }
    }

    /**
     * Handle the updating event
     *
     * @param $value
     * @param $name
     *
     * @return void
     */
    public function updating($value, $name): void
    {
        if (in_array($value, ['sortColumn', 'sortDirection', 'searchColumns.title'], true)) {
            $this->resetPage();
        }
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
     */
    public function render(): Factory|View|Application
    {
        return view(
            'livewire.messages.index',
            [
                'messages' => $this->buildQuery()->paginate($this->perPage),
            ]
        );
    }
}

