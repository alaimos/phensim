<?php

namespace App\Http\Livewire\Profile;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Laravel\Sanctum\NewAccessToken;
use Livewire\Component;

class ApiTokenManager extends Component
{

    /**
     * The create API token form state.
     *
     * @var array
     */
    public array $createApiTokenForm = [
        'name' => '',
    ];

    /**
     * Indicates if the plain text token is being displayed to the user.
     *
     * @var bool
     */
    public bool $displayingToken = false;

    /**
     * The plain text token value.
     *
     * @var string|null
     */
    public ?string $plainTextToken;

    /**
     * Listeners for this component
     *
     * @var string[]
     */
    protected $listeners = ['delete'];

    /**
     * Validation Rules
     *
     * @var array|\string[][]
     */
    protected array $rules = [
        'createApiTokenForm.name' => ['required', 'string', 'max:255'],
    ];

    /**
     * Create a new API token.
     *
     * @return void
     */
    public function createApiToken(): void
    {
        $validData = $this->validate();

        $this->displayTokenValue(auth()->user()->createToken($validData['createApiTokenForm']['name']));

        $this->createApiTokenForm['name'] = '';

        $this->emit('tokenCreated');
    }

    /**
     * Display the token value to the user.
     *
     * @param  \Laravel\Sanctum\NewAccessToken  $token
     *
     * @return void
     */
    protected function displayTokenValue(NewAccessToken $token): void
    {
        $this->displayingToken = true;
        $this->plainTextToken = explode('|', $token->plainTextToken, 2)[1];
        $this->dispatchBrowserEvent('showing-token-modal');
    }

    /**
     * Confirm that the given API token should be deleted.
     *
     * @param  int  $tokenId
     *
     * @return void
     */
    public function confirmApiTokenDeletion(int $tokenId): void
    {
        $this->dispatchBrowserEvent(
            'swal:confirm:delete',
            [
                'type'  => 'warning',
                'title' => __('Delete API Token'),
                'text'  => __('Are you sure you would like to delete this API token?'),
                'id'    => $tokenId,
            ]
        );
    }

    /**
     * Delete the API token.
     *
     * @param  mixed  $id
     *
     * @return void
     */
    public function delete(mixed $id): void
    {
        $user = auth()->user();
        $user->tokens()->where('id', $id)->delete();
        $user->load('tokens');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.api-token-manager');
    }
}
