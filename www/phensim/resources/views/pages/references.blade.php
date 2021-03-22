@extends('layouts.app', ['title' => __('Users')])

@section('content')
    <x-page-header class="col-lg-12" gradient="bg-gradient-red">
        <x-slot name="description">
            If you are using this service in your own work, please cite us by:
        </x-slot>
        References
    </x-page-header>

    <div class="container-fluid mt--6">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-body">
                        Salvatore Alaimo, Rosaria Valentina Rapicavoli, Gioacchino P. Marceca, Alessandro La Ferlita,
                        Oksana B. Serebrennikova, Philip N. Tsichlis, Bud Mishra, Alfredo Pulvirenti, Alfredo Ferro.
                        "PHENSIM: Phenotype Simulator." bioRxiv 2020.01.20.912279; doi:
                        https://doi.org/10.1101/2020.01.20.912279.
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection
