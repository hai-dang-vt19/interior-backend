<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite([
            'resources/css/app.css',
            'resources/js/app.js',
            'resources/scss/custom.scss',
        ])
    </head>
    <body>
        <div class="header">
            @include('component.navbar')
        </div>
        
        <div class="container-fluid px-5 pt-3">
            <div>
                @yield('breadcrumb')
            </div>
            @yield('content')
        </div>

        @yield('scripts')
        @if (session('dataSuccess'))
            <script type="module">
                Alert.success('{{ session('dataSuccess') }}');
            </script>
        @endif
        @if (session('dataError'))
            <script type="module">
                Alert.error('{{ session('dataError') }}');
            </script>
        @endif
    </body>
</html>
