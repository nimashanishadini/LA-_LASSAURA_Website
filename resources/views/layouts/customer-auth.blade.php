
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <meta name="referrer" content="no-referrer">

    <title>
        @yield('title', 'Customer Account') | La Lassaura
    </title>

    <link rel="icon"
          type="image/jpeg"
          href="{{ asset('images/brand/logo.jpeg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet"
          href="{{ asset('css/customer-auth.css') }}">

    @stack('styles')

</head>

<body class="customer-centered-body font-body">

    <div class="customer-centered-background"
         aria-hidden="true"></div>

    <main class="customer-centered-layout">

        <section class="customer-centered-card">

            <header class="customer-centered-heading">

                <h1>
                    @yield('heading', 'My Account')
                </h1>

                <a href="{{ route('home') }}"
                   class="customer-centered-close"
                   aria-label="Return to website">
                    &times;
                </a>

            </header>

            <div class="customer-centered-content">

                @yield('content')

            </div>

            <footer class="customer-centered-footer">

                LA LASSAURA · UNAWATUNA

            </footer>

        </section>

    </main>

    @stack('scripts')

</body>

</html>
