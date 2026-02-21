@props([
    'route',
    'confirmMessage' => __('Are you sure?'),
    'class' => 'inline',
])

<form action="{{ $route }}" method="POST" class="{{ $class }}" onsubmit="return confirm({{ json_encode($confirmMessage) }})">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => 'text-red-600 hover:text-red-900 bg-red-50 p-1 rounded transition']) }}>
        @if (isset($slot) && trim($slot) !== '')
            {{ $slot }}
        @else
            <x-heroicon-o-trash class="w-5 h-5" />
        @endif
    </button>
</form>
