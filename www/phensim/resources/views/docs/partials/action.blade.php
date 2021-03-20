<div class="mx-4">
    <h5>{{ $title }}</h5>
    <p class="mx-4">
        {{ $description }}<br>
        <strong>Request Method:</strong> <em>{{ $method }}</em><br>
        <strong>Request URL:</strong> <em>{{ url($url) }}</em><br>
        @if ($queryParameters)<strong>Query Parameters:</strong>@endif
    </p>
    @if ($queryParameters)
        <div class="mx-8">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($queryParameters as $param)
                        <tr>
                            <td>{{$param['name']}}</td>
                            <td>{{$param['type']}}</td>
                            <td>{{$param['desc']}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @if ($postParameters)
        <p class="mx-4">
            <strong>Supported body formats:</strong> <em>application/json</em>,<em>multipart/form-data</em>,<em>application/x-www-form-urlencoded</em><br>
            <strong>Body Parameters:</strong>
        </p>
        <div class="mx-8">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($postParameters as $param)
                        <tr>
                            <td>{{$param['name']}}</td>
                            <td>{{$param['type']}}</td>
                            <td>{{$param['desc']}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <p class="mx-4">
        <strong>Response:</strong><br>
        {{$responseDescription}}
    </p>
    @if ($responseParams)
        <div class="mx-8">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($responseParams as $param)
                        <tr>
                            <td>{{$param['name']}}</td>
                            <td>{{$param['type']}}</td>
                            <td>{{$param['desc']}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @if ($example)
        <p class="mx-4">
            <strong>Example Response:</strong>
        </p>
        <pre class="mx-8">{{$example}}</pre>
    @endif
</div>
