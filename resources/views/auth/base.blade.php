<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/scss/app.scss', 'resources/js/app.js', 'resources/scss/custom.scss'])
    </head>
    <body>
        <div class="container">
            @yield('content')
        </div>
        @yield('scripts')
    </body>
</html>
