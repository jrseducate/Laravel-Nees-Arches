<x-base-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <h1 class="na-title">Jeremy Stephens</h1>
    <div class="na-sub-title">{{ __('A full stack developer') }}</div>

    <div class="na-code" style="background-image: url({!! asset('images/code.png') !!});"></div>

    <div class="na-content">{{ __('I\'ve been programming for over 8 years now, it\'s a passion of mine to develop responsive and efficient code. Computers are something I personally enjoy working with and have spent most of my life getting to know them intimately.') }}</div>

</x-base-layout>
