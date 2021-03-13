@if($node !== null)
    <a class="link-effect" href="{{ $node->getUrl() }}" target="_blank" data-toggle="tooltip" data-placement="top"
       title="{{ str_limit($node->name, 50) }}">{{ $node->accession }}</a>
@else
    <a class="link-effect" href="Javascript: void(0);">{{ $id }}</a>
@endif