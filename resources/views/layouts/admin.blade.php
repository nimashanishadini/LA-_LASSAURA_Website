
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Dashboard') | La Lassaura Admin
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

<link rel="stylesheet" href="{{ asset('css/admin-shell.css') }}">

<link rel="stylesheet" href="{{ asset('css/admin-forms.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-tables.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-calendar.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-timeline.css') }}">

@livewireStyles
@stack('styles')
</head>

<body class="admin-shell-body font-body antialiased"
      x-data="{ menu: false }"
      @keydown.escape.window="
          if (menu) {
              menu = false;
              $nextTick(() => $refs.menuButton.focus());
          }
      ">

    <a href="#admin-content" class="admin-skip-link">
        Skip to content
    </a>

    <div class="admin-shell">

        <!-- Sidebar -->
        <x-admin.sidebar />

        <!-- Main Area -->
        <div class="admin-shell-main">

            <!-- Topbar -->
            <x-admin.topbar />

            <!-- Page Content -->
            <main id="admin-content"
                  tabindex="-1"
                  class="admin-shell-content">

                <div class="admin-page-heading">

                    <p>Property management</p>

                    <h1>
                        @yield('title', 'Dashboard')
                    </h1>

                </div>

                <!-- Success Message -->
                @if(session('status'))
                    <div class="admin-alert-success" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="admin-alert-error" role="alert">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')

            </main>

        </div>

    </div>

    @livewireScripts
    @stack('scripts')

</body>
</html>
