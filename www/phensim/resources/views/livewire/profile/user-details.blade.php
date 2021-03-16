<div class="card card-profile shadow">
    <div class="row justify-content-center">
        <div class="col-lg-3 order-lg-2">
            <div class="card-profile-image">
                <a href="#">
                    <img src="{{ Gravatar::src(auth()->user()->email, 800) }}" class="rounded-circle">
                </a>
            </div>
        </div>
    </div>
    <div class="card-header text-center border-0 pt-8 pt-md-4 pb-0 pb-md-4">
    </div>
    <div class="card-body pt-0 pt-md-4">
        <div class="row">
            <div class="col">
                <div class="card-profile-stats d-flex justify-content-center mt-md-5">
                    <div>
                                        <span
                                            class="heading">{{ auth()->user()->countSimulationsByState(\App\Models\Simulation::QUEUED) }}</span>
                        <span class="description">Queued<br>Simulations</span>
                    </div>
                    <div>
                                        <span
                                            class="heading">{{ auth()->user()->countSimulationsByState(\App\Models\Simulation::COMPLETED) }}</span>
                        <span class="description">Completed<br>Simulations</span>
                    </div>
                    <div>
                                        <span
                                            class="heading">{{ auth()->user()->countSimulationsByState(\App\Models\Simulation::FAILED) }}</span>
                        <span class="description">Failed<br>Simulations</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center">
            <h3>
                {{ auth()->user()->name }}
            </h3>
            <div>
                <i class="ni ni-building mr-2"></i>{{ auth()->user()->affiliation }}
            </div>
        </div>
    </div>
</div>
