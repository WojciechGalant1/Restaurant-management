@props(['title'])

<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $title }}
    </h2>
    @if (isset($action))
        {{ $action }}
    @elseif (isset($actionUrl) && isset($actionLabel))
        <a href="{{ $actionUrl }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition flex items-center">
            <x-heroicon-o-plus class="w-4 h-4 mr-2" />
            {{ $actionLabel }}
        </a>
    @endif
</div>
