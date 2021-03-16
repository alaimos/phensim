<?php

namespace App\Http\Livewire\Layout;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UserMenu extends Component
{

    /**
     * A set of event listeners for this component
     *
     * @var array|string[]
     */
    protected $listeners = ['profileUpdated' => 'render'];


    /**
     * Render this component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
     */
    public function render(): Factory|View|Application
    {
        return view('livewire.layout.user-menu');
    }
}
