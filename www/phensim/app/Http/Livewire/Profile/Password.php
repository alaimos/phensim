<?php

namespace App\Http\Livewire\Profile;

use App\Rules\CurrentPasswordCheckRule;
use App\Rules\Password as PasswordRule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Password extends Component
{

    public array $state = [
        'old_password'          => '',
        'password'              => '',
        'password_confirmation' => '',
    ];

    /**
     * Validation rules
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'state.old_password'          => ['required', new CurrentPasswordCheckRule()],
            'state.password'              => [
                'required',
                (new PasswordRule())->length(8)->requireNumeric()->requireUppercase(),
                'confirmed',
                'different:old_password',
            ],
            'state.password_confirmation' => ['required'],
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
        $user->update(['password' => Hash::make($validated['state']['password'])]);

        $this->state = [
            'old_password'          => '',
            'password'              => '',
            'password_confirmation' => '',
        ];

        session()->flash('status', 'Password successfully updated.');
    }


    public function render(): Factory|View|Application
    {
        return view('livewire.profile.password');
    }
}
