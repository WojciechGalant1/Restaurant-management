@props(['type' => 'success'])

@php
    $key = $type === 'error' ? 'error' : 'success';
    $message = session($key);
    $classes = $type === 'error'
        ? 'bg-red-100 border-l-4 border-red-500 text-red-700'
        : 'bg-green-100 border-l-4 border-green-500 text-green-700';
@endphp

@if ($message)
    <div class="{{ $classes }} p-4 mb-6 shadow-sm sm:rounded-r-lg" role="alert">
        <p>{{ $message }}</p>
    </div>
@endif
