<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/scss/app.scss', 'resources/scss/admin.scss', 'resources/js/app.js'])
    </head>
    <body class="auth-admin-wrap">
        <div class="container">
            @php
                $flashSuccess = session('dataSuccess');
                $flashSuccessMessage = is_array($flashSuccess)
                    ? ($flashSuccess['msg'] ?? ($flashSuccess['message'] ?? null))
                    : $flashSuccess;
                $flashError = session('dataError');
                $flashErrorMessage = is_array($flashError)
                    ? ($flashError['error']['msg'] ?? ($flashError['msg'] ?? null))
                    : $flashError;
            @endphp
            @if ($flashSuccessMessage)
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
                    <div class="alert alert-success mb-0">{{ $flashSuccessMessage }}</div>
                </div>
            @endif
            @if ($flashErrorMessage)
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
                    <div class="alert alert-danger mb-0">{{ $flashErrorMessage }}</div>
                </div>
            @endif
            @yield('content')
        </div>
        @yield('scripts')
    </body>
</html>
