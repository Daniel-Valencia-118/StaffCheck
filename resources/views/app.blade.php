<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StaffCheck</title>
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#213668">

    @routes(auth()->check() ? null : 'publico', Vite::cspNonce())

    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    @inertia
</body>
</html>
