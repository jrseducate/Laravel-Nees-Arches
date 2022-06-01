<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/jquery-ui-1.13.1.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/toastify.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/font-awesome-all-5.15.4.css') }}">

        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <link rel="stylesheet" href="{{ asset('css/na.css') }}">
        <link rel="stylesheet" href="{{ asset('css/na-themes.css') }}">
        <link rel="stylesheet" href="{{ asset('css/trello.css') }}">

        <!-- Scripts -->
        <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
        <script src="{{ asset('js/jquery-ui-1.13.1.min.js') }}"></script>
        <script src="{{ asset('js/sweet-alert-2.11.min.js') }}"></script>
        <script src="{{ asset('js/underscore-1.13.3.min.js') }}"></script>
        <script src="{{ asset('js/toastify.min.js') }}"></script>

        <script src="{{ asset('js/app.js') }}" defer></script>
        <script src="{{ asset('js/ajax.js') }}" defer></script>
        <script src="{{ asset('js/na.js') }}" defer></script>
        <script src="{{ asset('js/trello.js') }}" defer></script>

        <script>
            const Links = {
                Trello: {
                    post_board_create: '{{ route('trello.post.board.create') }}',
                    post_board_update: '{{ route('trello.post.board.update') }}',
                    post_board_destroy: '{{ route('trello.post.board.destroy') }}',

                    post_column_create: '{{ route('trello.post.column.create') }}',
                    post_column_update: '{{ route('trello.post.column.update') }}',
                    post_column_destroy: '{{ route('trello.post.column.destroy') }}',

                    post_item_create: '{{ route('trello.post.item.create') }}',
                    post_item_update: '{{ route('trello.post.item.update') }}',
                    post_item_destroy: '{{ route('trello.post.item.destroy') }}',

                    post_column_reorder: '{{ route('trello.post.column.reorder') }}',
                    post_item_reorder: '{{ route('trello.post.item.reorder') }}',
                    post_request_update: '{{ route('trello.post.request_update') }}',
                }
            };
        </script>
    </head>
    <body class="na-body na-theme {!! $theme !!} font-sans antialiased" data-theme-class="{!! $theme !!}">
        <div class="min-h-screen">
            @include('layouts.navigation')

            {{--
            <!-- Page Heading -->
            <header class="na-header shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
            --}}

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
