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
            <div class="col-xl-8 mb-5 mb-xl-0">
                <div class="card bg-gradient-default shadow">
                    <div class="card-header bg-transparent">
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="text-white mb-0">Authors</h2>
                            </div>
                        </div>
                    </div>
                    <div class="card-body text-white">
                        <p>University of Catania</p>
                        <ul>
                            <li>Salvatore Alaimo - Department of Clinical and Experimental Medicine</li>
                            <li>Rosaria Valentina Rapicavoli - Department of Physics and Astronomy</li>
                            <li>Gioacchino Paolo Marceca - Department of Clinical and Experimental Medicine</li>
                            <li>Alessandro La Ferlita - Department of Physics and Astronomy</li>
                            <li>Alfredo Pulvirenti - Department of Clinical and Experimental Medicine</li>
                            <li>Alfredo Ferro - Department of Clinical and Experimental Medicine</li>
                        </ul>
                        <p>Ohio State University</p>
                        <ul>
                            <li>Philip N. Tsichlis - Department of Cancer Biology and Genetics and the James Comprehensive Cancer Center</li>
                        </ul>
                        <p>New York University</p>
                        <ul>
                            <li>Philip N. Tsichlis - Department of Computer Science, Courant Institute of Mathematical Sciences</li>
                        </ul>
                        <p>Tufts Medical Center</p>
                        <ul>
                            <li>Oksana B. Serebrennikova - Molecular Oncology Research Institute</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card shadow">
                    <div class="card-header bg-transparent">
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="mb-0">Bug Reporting and Suggestions</h2>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p>
                            For bugs reporting or suggestions on new features, please open a new issue in our GitHub
                            repository:
                            <a href="https://github.com/alaimos/phensim/issues">
                                https://github.com/alaimos/phensim/issues
                            </a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection
