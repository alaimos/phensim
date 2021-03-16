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
     * Indicates if the application is confirming if an API token should be deleted.
     *
     * @var bool
     */
    public bool $confirmingApiTokenDeletion = false;

    /**
     * The ID of the API token being deleted.
     *
     * @var int
     */
    public int $apiTokenIdBeingDeleted;

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
        $this->confirmingApiTokenDeletion = true;
        $this->apiTokenIdBeingDeleted = $tokenId;
    }

    /**
     * Delete the API token.
     *
     * @return void
     */
    public function deleteApiToken(): void
    {
        $user = auth()->user();
        $user->tokens()->where('id', $this->apiTokenIdBeingDeleted)->delete();
        $user->load('tokens');
        $this->confirmingApiTokenDeletion = false;
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
