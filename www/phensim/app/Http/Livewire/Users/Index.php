<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use App\Rules\Password;
use Hash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
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
        'name'  => '',
        'email' => '',
    ];
    public $displayingModal = false;
    public $currentUserId = null;
    public $currentUser = null;

    /**
     * Validation rules for the form
     *
     * @return array
     */
    protected function rules(): array
    {
        $uniqueRule = Rule::unique('users', 'email');
        if ($this->currentUserId !== null) {
            $uniqueRule->ignore($this->currentUserId);
        }

        return [
            'currentUser.name'        => ['required', 'string', 'max:255'],
            'currentUser.email'       => ['required', 'string', 'email', 'max:255', $uniqueRule],
            'currentUser.password'    => [
                ($this->currentUserId !== null) ? 'filled' : 'required',
                'string',
                (new Password())->length(8)->requireNumeric()->requireUppercase(),
            ],
            'currentUser.affiliation' => ['required', 'string', 'max:255'],
            'currentUser.is_admin'    => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Build the query to show the users
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQuery(): Builder
    {
        $users = User::select(['id', 'name', 'email', 'created_at']);
        foreach ($this->searchColumns as $column => $value) {
            $users->where($column, 'LIKE', '%' . $value . '%');
        }
        $users->orderBy($this->sortColumn, $this->sortDirection);

        return $users;
    }

    /**
     * Confirm that the given user should be deleted.
     *
     * @param  int  $userId
     *
     * @return void
     */
    public function confirmUserDeletion(int $userId): void
    {
        $this->dispatchBrowserEvent(
            'swal:confirm',
            [
                'type'    => 'delete',
                'icon'    => 'warning',
                'title'   => __('Delete User'),
                'text'    => __('Are you sure you would like to delete this user?'),
                'id'      => $userId,
                'buttons' => true,
            ]
        );
    }

    /**
     * Open the modal with the user form
     *
     * @param  int|null  $userId
     *
     * @return void
     */
    public function openModal(?int $userId = null): void
    {
        $this->resetErrorBag();
        $this->displayingModal = true;
        $this->currentUserId = $userId;
        $this->currentUser = ($userId) ? User::find($userId)->toArray() : [];
    }

    /**
     * Delete the user.
     *
     * @param  \App\Models\User  $user
     *
     * @return void
     * @throws \Exception
     */
    public function receivedConfirmation(User $user): void
    {
        $user->delete();
    }

    /**
     * Save the current user
     *
     * @return void
     */
    public function saveUser(): void
    {
        $validData = $this->validate();
        $currentUser = $validData['currentUser'];
        $isAdmin = $validData['is_admin'] ?? false;
        if (is_null($this->currentUserId)) {
            $currentUser['password'] = Hash::make($currentUser['password']);
            $user = (new User())->fill($currentUser);
        } else {
            if (isset($currentUser['password']) || !empty($currentUser['password'])) {
                $currentUser['password'] = Hash::make($currentUser['password']);
            }
            $user = User::find($this->currentUserId)->fill($currentUser);
        }
        $user->is_admin = $isAdmin;
        $user->save();
        $this->displayingModal = false;
        $this->dispatchBrowserEvent(
            'swal:confirm',
            [
                'type'    => 'success',
                'icon'    => 'success',
                'title'   => __('User Saved'),
                'text'    => __('The user has been successfully saved'),
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
        if (in_array($value, ['sortColumn', 'sortDirection', 'searchColumns.name', 'searchColumns.email'], true)) {
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
            'livewire.users.index',
            [
                'users' => $this->buildQuery()->paginate($this->perPage),
            ]
        );
    }
}
