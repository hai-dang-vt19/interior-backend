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
            @if (session('dataSuccess'))
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
                    <div class="alert alert-success mb-0">{{ session('dataSuccess') }}</div>
                </div>
            @endif
            @if (session('dataError'))
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
                    <div class="alert alert-danger mb-0">{{ session('dataError') }}</div>
                </div>
            @endif
            @yield('content')
        </div>
        @yield('scripts')
    </body>
</html>
