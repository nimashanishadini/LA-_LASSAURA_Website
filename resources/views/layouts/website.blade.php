
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'La Lassaura')</title>

    <meta name="description"
          content="@yield('description', 'Discover La Lassaura in Unawatuna, Sri Lanka.')">

          <link rel="icon" type="image/jpeg"
      href="{{ asset('images/brand/logo.jpeg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @stack('styles')
</head>

<body class="bg-ivory text-navy font-body antialiased">

    <x-website.announcement-bar />

    <x-website.navbar />

    <main id="main-content">
        @yield('content')
    </main>

    <x-website.footer />
    <x-website.booking.modal />
    <x-website.image-lightbox />

    @livewireScripts
    @stack('scripts')

</body>
</html>
