@props(['title' => ''])

<div {{ $attributes->merge(['class' => 'card bg-gradient-default']) }}>
    <div class="card-body">
        @if ($title ?? null) <h3 class="card-title text-white">{{ $title }}</h3> @endif
        <blockquote class="blockquote text-white mb-0">
            <pre><code class="text-white">{{ $slot }}</code></pre>
        </blockquote>
    </div>
</div>
