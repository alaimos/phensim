@extends('layouts.app')

@section('content')
    <section class="content content-boxed">
        <div class="push-10-t push-10">
            <div class="row">
                <div class="col-md-6"><h3 class="h4 font-w600 text-uppercase push-10">Authors</h3>
                    <div class="block">
                        <div class="block-content">
                            <div class="row items-push">
                                <div class="col-md-12"><h4 class="text-uppercase push-10">
                                        Salvatore Alaimo
                                    </h4>
                                    <p class="push-20">
                                        Bioinformatics Unit<br>
                                        Department of Clinical and Experimental Medicine<br>
                                        University of Catania, Italy
                                    </p></div>
                            </div>
                        </div>
                    </div>
                    <div class="block">
                        <div class="block-content">
                            <div class="row items-push">
                                <div class="col-md-12"><h4 class="text-uppercase push-10">
                                        Ferro Alfredo
                                    </h4>
                                    <p class="push-20">
                                        Bioinformatics Unit<br>
                                        Department of Clinical and Experimental Medicine<br>
                                        University of Catania, Italy
                                    </p></div>
                            </div>
                        </div>
                    </div>
                    <div class="block">
                        <div class="block-content">
                            <div class="row items-push">
                                <div class="col-md-12"><h4 class="text-uppercase push-10">
                                        Pulvirenti Alfredo
                                    </h4>
                                    <p class="push-20">
                                        Bioinformatics Unit<br>
                                        Department of Clinical and Experimental Medicine<br>
                                        University of Catania, Italy
                                    </p></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6"><h3 class="h4 font-w600 text-uppercase push-10">Bugs Reporting</h3>
                    <div class="block">
                        <div class="block-content">
                            <div class="row items-push">
                                <div class="col-md-12"><p>
                                        For bugs reporting, please open a new issue in the GitHub repository of this
                                        project: <a href="https://github.com/alaimos/phensim/issues">https://github.com/alaimos/phensim/issues</a>.
                                    </p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('inline-scripts')
@endpush