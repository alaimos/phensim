<h4 class="font-w400 text-black push-20">{{ $title }}</h4>
<p class="push-20-l">{{ $description }}<br>
    <strong>Request Method:</strong> <em>{{ $method }}</em><br>
    <strong>Request URL:</strong> <em>{{ url($url) }}</em><br>
    @if ($queryParameters)<strong>Query Parameters:</strong>@endif
</p>
@if ($queryParameters)
    <table class="table push-20-l">
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
@endif
@if ($postParameters)
    <p>
        @if ($method == 'POST')
            <strong>Supported POST formats:</strong> <em>application/json</em>,<em>multipart/form-data</em>,<em>application/x-www-form-urlencoded</em>
            <br>
        @endif
        <strong>POST Parameters:</strong>
    </p>
    <table class="table push-20-l">
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
@endif
<p class="push-20-l">
    <strong>Response:</strong><br>
    {{$responseDescription}}
</p>
@if ($responseParams)
    <table class="table push-20-l">
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
@endif
@if ($example)
    <p class="push-20-l">
        <strong>Example Response:</strong>
    </p>
    <pre class="push-30-l">{{$example}}</pre>
@endif