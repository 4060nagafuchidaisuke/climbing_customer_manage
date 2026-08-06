{{-- デフォルトはダッシュボード背景 --}}
@props([
    'background'=>'images/Admin.webp',
    'bgPosition'=>'center center', 
    'bgSize'=>'cover',
]

)

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HAZY BOULDER') }} | 管理システム</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    </head>

    <body class="font-sans antialiased bg-cover bg-center bg-fixed"
        style="
            background-image: url('{{ asset($background) }}');
            background-position: {{ $bgPosition }};
            background-size:     {{ $bgSize }};
        ">
        <div class="min-h-screen">
            @unless (request()->routeIs('checkin.index'))
                @include('layouts.navigation')
                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset
            @endunless

            <main>
                {{ $slot }}
            </main>
        </div>
    @stack('scripts')
    </body>
</html>