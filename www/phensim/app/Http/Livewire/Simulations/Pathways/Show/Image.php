<?php

namespace App\Http\Livewire\Simulations\Pathways\Show;

use App\Models\Simulation;
use App\PHENSIM\Reader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Image extends Component
{
    public Simulation $simulation;
    public string $pathway;
    public bool $displayingImage = false;
    public ?string $image = null;

    /**
     * Show the modal with the pathway image
     *
     * @throws \App\Exceptions\FileSystemException
     * @throws \JsonException
     * @throws \Throwable
     */
    public function showImage(): void
    {
        if ($this->image === null) {
            $imageFile = (new Reader($this->simulation->output_file))->makePathwayImage(
                $this->pathway,
                $this->simulation->organism->accession
            );
            $this->image = 'data:image/png;base64,' . base64_encode(file_get_contents($imageFile));
        }
        $this->displayingImage = true;
    }

    public function render(): Factory|View|Application
    {
        return view('livewire.simulations.pathways.show.image');
    }
}
