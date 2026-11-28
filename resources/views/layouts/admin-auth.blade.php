
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Admin Login') | La Lassaura
    </title>

    <link rel="icon"
          href="{{ asset('images/brand/logo.jpeg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('css/admin-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-forms.css') }}">

    @stack('styles')

</head>

<body class="admin-auth-body font-body antialiased">

    <main class="admin-auth-layout">

        {{-- Branding Section --}}
        <section class="admin-auth-brand"
                 aria-label="La Lassaura Property Management">

            <div class="admin-auth-brand-content">

                <a href="{{ route('home') }}"
                   class="admin-auth-logo-link">

                    <img
                        src="{{ asset('images/brand/logo.jpeg') }}"
                        alt="La Lassaura"
                        class="admin-auth-logo"
                    >

                </a>

                <div class="admin-auth-brand-name">
                    LA LASSAURA
                </div>

                <p class="admin-auth-brand-subtitle">
                    BEACH SIDE VILLA
                </p>

                <div class="admin-auth-brand-divider"></div>

                <h2>
                    Property Management
                </h2>

                <p class="admin-auth-brand-description">
                    Manage your property, reservations
                    and guest experiences in one place.
                </p>

            </div>

            <div class="admin-auth-brand-footer">
                UNAWATUNA · SRI LANKA
            </div>

        </section>


        {{-- Authentication Content --}}
        <section class="admin-auth-main">

            <div class="admin-auth-container">

                @yield('content')

            </div>

        </section>

    </main>

    @stack('scripts')

</body>

</html>
