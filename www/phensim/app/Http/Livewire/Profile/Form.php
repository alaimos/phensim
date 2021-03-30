<?php

namespace App\Http\Livewire\Profile;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{

    public array $state = [];

    public function mount(): void
    {
        $this->state = auth()->user()->withoutRelations()->toArray();
    }

    /**
     * Validation rules
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'state.name'        => ['required', 'string', 'max:255'],
            'state.affiliation' => ['required', 'string', 'max:255'],
            'state.email'       => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore(auth()->id())],
        ];
    }

    /**
     * Perform the profile update
     *
     * @return void
     */
    public function update(): void
    {
        $validated = $this->validate();

        $user = auth()->user();
        abort_if(!$user, 500, 'Invalid user');
        $user->update($validated['state']);

        //TODO: if the user changes the email, I will have to verify it!

        $this->emit('profileUpdated');

        session()->flash('status', 'Profile successfully updated.');
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
     */
    public function render(): Factory|View|Application
    {
        return view('livewire.profile.form');
    }
}
