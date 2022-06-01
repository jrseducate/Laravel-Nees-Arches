<x-app-layout>

    <x-slot name="header">
        <h2 class="na-font-bold text-xl leading-tight">
            {{ $header }}
        </h2>
    </x-slot>

    <div class="na-panel overflow-hidden">
        {{ $slot }}
    </div>

</x-app-layout>
