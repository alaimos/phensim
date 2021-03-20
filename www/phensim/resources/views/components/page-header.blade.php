@props(['gradient' => 'bg-gradient-primary'])

<div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center">
    <span class="mask {{ $gradient }} opacity-8"></span>
    <div class="container-fluid d-flex align-items-center">
        <div class="row">
            <div {{ $attributes->merge(['class' => 'col-md-12']) }}>
                <h1 class="display-2 text-white">{{ $slot }}</h1>
                @if ($description ?? null)
                    <p class="text-white mt-0 mb-5">{{ $description }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
