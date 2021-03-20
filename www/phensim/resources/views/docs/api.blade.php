@extends('layouts.app', ['title' => __('User Manual')])

@section('content')
    <x-page-header class="col-lg-12" gradient="bg-gradient-orange">
        {{--        <x-slot name="description">--}}
        {{--            From this page you can manage all your simulations.--}}
        {{--        </x-slot>--}}
        API User Manual
    </x-page-header>

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-body">
                        <h3>Introduction</h3>
                        <p>
                            This is a brief specification of PHENSIM REST APIs.
                            To access APIs, you will need to generate an access token from the <strong>My
                                Profile</strong>
                            panel that you can access from the menu at the top-right corner of this page.</p>
                        <p>
                            All Requests MUST contain the following headers for authentication:
                        </p>
                        <pre class="mx-4"><code>Accept: application/json
Authorization: Bearer YOUR_API_AUTHENTICATION_TOKEN</code></pre>
                        <h3>HTTP Return Codes</h3>
                        <p>HTTP return codes are used to identify the state of a request:</p>
                        <div class="mx-4">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 50px;">Code</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">200</td>
                                        <td>Your request has been completed correctly. The payload will contain all the
                                            data.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">401</td>
                                        <td>You are not authorized to use API. Did you forget your authentication token?
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">403</td>
                                        <td>You are not allowed to perform the specified action. The payload might
                                            contain more details.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">404</td>
                                        <td>The resource you are looking for was not found. Did you specify the correct
                                            URL?
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">422</td>
                                        <td>Some validation error were found when sending your request. The payload will
                                            contain more details.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">500</td>
                                        <td>An error occurred during processing. The payload will contain more
                                            details.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection
