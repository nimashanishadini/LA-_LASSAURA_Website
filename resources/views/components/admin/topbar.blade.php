
<header class="admin-shell-topbar">

    <div class="admin-topbar-title">
        La Lassaura · Admin
    </div>

    <div class="admin-topbar-user">

        <div class="admin-user-avatar" aria-hidden="true">
            {{
                \Illuminate\Support\Str::upper(
                    \Illuminate\Support\Str::substr(
                        auth()->user()->name,
                        0,
                        1
                    )
                )
            }}
        </div>

        <div class="admin-user-details">

            <p class="admin-user-name">
                {{ auth()->user()->name }}
            </p>

            <p class="admin-user-role">
                {{ auth()->user()->role }}
            </p>

        </div>

    </div>

</header>
