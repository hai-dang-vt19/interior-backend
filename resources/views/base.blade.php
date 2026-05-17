<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite([
        'resources/scss/app.scss',
        'resources/scss/admin.scss',
        'resources/js/app.js',
    ])
</head>

<body class="admin-body">
    <header class="admin-header">
        @include('component.navbar')
    </header>
    @auth
        @include('component.admin-order-pending-notify-modal')
    @endauth

    <main class="admin-main">
        @hasSection('breadcrumb')
            <div class="admin-breadcrumb-wrap">
                @yield('breadcrumb')
            </div>
        @endif
        @yield('content')
    </main>

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
