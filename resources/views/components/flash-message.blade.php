@props(['type' => 'success'])

@php
    $key = match($type) {
        'error' => 'error',
        'info' => 'info',
        default => 'success',
    };
    $message = session($key);
    $classes = match($type) {
        'error' => 'bg-red-100 border-l-4 border-red-500 text-red-700',
        'info' => 'bg-blue-100 border-l-4 border-blue-500 text-blue-700',
        default => 'bg-green-100 border-l-4 border-green-500 text-green-700',
    };
@endphp

@if ($message)
    <div class="{{ $classes }} p-4 mb-6 shadow-sm sm:rounded-r-lg" role="alert">
        <p>{{ $message }}</p>
    </div>
@endif
