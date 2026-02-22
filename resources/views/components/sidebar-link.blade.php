@props(['active' => false, 'icon' => null])

@php
$classes = ($active ?? false)
    ? 'flex items-center gap-x-3 px-3 py-2 rounded-lg text-sm font-semibold bg-indigo-50 text-indigo-700 border-l-[3px] border-indigo-600 -ml-px'
    : 'flex items-center gap-x-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <x-dynamic-component :component="$icon" class="w-5 h-5 shrink-0" />
    @endif
    <span x-show="!collapsed" x-transition.opacity.duration.200ms class="truncate">{{ $slot }}</span>
</a>
