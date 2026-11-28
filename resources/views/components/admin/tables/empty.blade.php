@props([
    'colspan' => 1,
    'title' => 'No records found',
    'description' => null,
])

<tr>

    <td colspan="{{ $colspan }}">

        <div class="admin-table-empty">

            <svg
                width="40"
                height="40"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.4"
                aria-hidden="true"
            >
                <rect x="3" y="4" width="18" height="16" rx="2"/>
                <path d="M3 10h18M9 10v10"/>
            </svg>

            <h3>{{ $title }}</h3>

            @if($description)
                <p>{{ $description }}</p>
            @endif

        </div>

    </td>

</tr>