@props(['action', 'method' => 'DELETE', 'class' => 'btn btn-danger'])
<form method="POST" action="{{ $action }}" class="d-inline-block {{ str_contains($class, 'w-100') ? 'w-100' : '' }}">
    @csrf
    @method($method)
    <button type="submit" {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</button>
</form>
