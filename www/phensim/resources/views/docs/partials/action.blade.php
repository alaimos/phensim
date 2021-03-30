<div class="mx-4">
    <a id="endpoint-{{ \Illuminate\Support\Str::slug($title) }}"/>
    <h4>{{ $title }}</h4>
    <div class="mx-4">
        <div>{!! $description !!}</div>
        <h5>Request Method</h5>
        <div class="mt-0 mx-4"><code class="text-dark">{{ $method }}</code></div>
        <h5>Request URL</h5>
        <div class="mt-0 mx-4"><code class="text-dark">{{ url($url) }}</code></div>
        @if ($queryParameters)
            <h5>Query Parameters</h5>
            <x-docs.api.table :headers="['Name', 'Type', 'Description']" :rows="$queryParameters"></x-docs.api.table>
        @endif
        @if ($postParameters)
            <h5>Available Body Content-Type</h5>
            <div class="mt-0 mx-4">
                <code class="text-dark">application/json</code><br>
                <code class="text-dark">multipart/form-data</code><br>
                <code class="text-dark">application/x-www-form-urlencoded</code>
            </div>
            <h5>Body Fields</h5>
            <x-docs.api.table :headers="['Name (in dot notation)', 'Type', 'Description']" :rows="$postParameters"></x-docs.api.table>
        @endif
        <h5>Response</h5>
        <div class="mx-4">
            <p>{!! $responseDescription !!}</p>
            @if ($responseParams)
                <x-docs.api.table :headers="['Name (in dot notation)', 'Type', 'Description']" :rows="$responseParams"></x-docs.api.table>
            @endif
        </div>
        @if ($example)
            <h5>Examples</h5>
            <x-docs.api.code-block class="mx-4" title="Response">{{ $example }}</x-docs.api.code-block>
        @endif
    </div>
</div>
