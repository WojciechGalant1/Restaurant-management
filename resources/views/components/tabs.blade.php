@props([
    'tabs' => [],
    'default' => null,
    'icons' => [], // optional: ['calendar' => 'heroicon-o-calendar-days', 'table' => 'heroicon-o-table-cells']
])

@php
    $default = $default ?? array_key_first($tabs);
@endphp

<div class="mb-4 border-b border-gray-200">
    <nav class="flex space-x-4" aria-label="Tabs">
        @foreach ($tabs as $key => $label)
            <button @click="tab = {{ json_encode($key) }}"
                :class="tab === {{ json_encode($key) }} ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center transition {{ $key === $default ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500' }}">
                @if (isset($icons) && isset($icons[$key]))
                    <x-dynamic-component :component="$icons[$key]" class="w-4 h-4 mr-2" />
                @endif
                {{ $label }}
            </button>
        @endforeach
    </nav>
</div>
