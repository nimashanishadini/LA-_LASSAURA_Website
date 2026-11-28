@extends('layouts.admin')

@section('title', 'Inquiries')

@section('content')

    <!-- Overview -->

    <div class="admin-table-overview">

        <div class="admin-table-overview-item">

            <span>Total inquiries</span>

            <strong>{{ $inquiries->total() }}</strong>

        </div>

        <div class="admin-table-overview-item">

            <span>New inquiries</span>

            <strong class="admin-table-gold">
                {{ $newCount }}
            </strong>

        </div>

    </div>

    <!-- Page heading -->

    <div class="admin-table-page-heading">

        <div>

            <h2>All guest inquiries</h2>

            <p>
                Manage messages submitted through the
                website contact form.
            </p>

        </div>

    </div>

    <!-- Inquiries table -->

    <x-admin.tables.table label="Guest inquiries">

        <x-slot:head>

            <x-admin.tables.heading>
                Guest
            </x-admin.tables.heading>

            <x-admin.tables.heading>
                Subject
            </x-admin.tables.heading>

            <x-admin.tables.heading>
                Message
            </x-admin.tables.heading>

            <x-admin.tables.heading>
                Date
            </x-admin.tables.heading>

            <x-admin.tables.heading>
                Status
            </x-admin.tables.heading>

            <x-admin.tables.heading align="right">
                Action
            </x-admin.tables.heading>

        </x-slot:head>

        @forelse($inquiries as $inquiry)

            <tr>

                <!-- Guest -->

                <x-admin.tables.cell>

                    <div class="admin-table-person">

                        <div
                            class="admin-table-avatar"
                            aria-hidden="true"
                        >
                            {{ mb_strtoupper(
                                mb_substr($inquiry->name, 0, 1)
                            ) }}
                        </div>

                        <div class="admin-table-person-info">

                            <strong>
                                {{ $inquiry->name }}
                            </strong>

                            <span>
                                {{ $inquiry->email }}
                            </span>

                        </div>

                    </div>

                </x-admin.tables.cell>

                <!-- Subject -->

                <x-admin.tables.cell>

                    <strong class="admin-inquiry-subject">

                        {{ \Illuminate\Support\Str::limit(
                            $inquiry->subject,
                            45
                        ) }}

                    </strong>

                </x-admin.tables.cell>

                <!-- Message preview -->

                <x-admin.tables.cell>

                    <div class="admin-table-comment">

                        {{ \Illuminate\Support\Str::limit(
                            $inquiry->message,
                            60
                        ) }}

                    </div>

                </x-admin.tables.cell>

                <!-- Date -->

                <x-admin.tables.cell>

                    <div class="admin-table-date">

                        <strong>
                            {{ $inquiry->created_at
                                ->timezone('Asia/Colombo')
                                ->format('d M Y') }}
                        </strong>

                        <span>
                            {{ $inquiry->created_at
                                ->timezone('Asia/Colombo')
                                ->format('H:i') }}
                        </span>

                    </div>

                </x-admin.tables.cell>

                <!-- Status -->

                <x-admin.tables.cell>

                    <span class="admin-message-status
                        status-{{ $inquiry->status }}">

                        {{ ucfirst($inquiry->status) }}

                    </span>

                </x-admin.tables.cell>

                <!-- Action -->

                <x-admin.tables.cell align="right">

                    <button
                        type="button"
                        class="admin-table-view-button"
                        aria-label="View inquiry from {{ $inquiry->name }}"
                        aria-haspopup="dialog"
                        aria-controls="inquiry-modal-{{ $inquiry->id }}"
                        @click="document.getElementById(
                            'inquiry-modal-{{ $inquiry->id }}'
                        ).showModal()"
                    >

                        <svg
                            width="17"
                            height="17"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <path d="M2 12s3.5-7 10-7
                                10 7 10 7-3.5 7-10 7
                                -10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>

                        View

                    </button>

                </x-admin.tables.cell>

            </tr>

        @empty

            <x-admin.tables.empty
                :colspan="6"
                title="No inquiries received yet"
                description="Guest messages submitted through the contact form will appear here."
            />

        @endforelse

        @if($inquiries->hasPages())

            <x-slot:footer>

                {{ $inquiries->links() }}

            </x-slot:footer>

        @endif

    </x-admin.tables.table>

    <!-- Inquiry detail modals -->

    @foreach($inquiries as $inquiry)

        @php
            $isCurrentInquiry =
                (string) old('_inquiry_id') ===
                (string) $inquiry->id;
        @endphp

        <x-admin.modal
            :id="'inquiry-modal-'.$inquiry->id"
            title="Inquiry details"
        >

            <!-- Guest information -->

            <div class="admin-modal-guest">

                <div
                    class="admin-review-avatar"
                    aria-hidden="true"
                >
                    {{ mb_strtoupper(
                        mb_substr($inquiry->name, 0, 1)
                    ) }}
                </div>

                <div class="admin-review-person">

                    <h2>{{ $inquiry->name }}</h2>

                    <p>{{ $inquiry->email }}</p>

                    <p>
                        {{ $inquiry->created_at
                            ->timezone('Asia/Colombo')
                            ->format('d M Y H:i') }}
                    </p>

                </div>

                <span class="admin-message-status
                    status-{{ $inquiry->status }}">

                    {{ ucfirst($inquiry->status) }}

                </span>

            </div>

            <!-- Contact information -->

            <dl class="admin-message-details">

                <dt>Email address</dt>

                <dd>{{ $inquiry->email }}</dd>

                <dt>Phone number</dt>

                <dd>
                    {{ $inquiry->phone ?: 'Not provided' }}
                </dd>

                <dt>Subject</dt>

                <dd>{{ $inquiry->subject }}</dd>

            </dl>

            <!-- Full message -->

            <div class="admin-message-body">

                <h3>Guest message</h3>

                <p>{{ $inquiry->message }}</p>

            </div>

            <!-- Status update form -->

            <form
                method="POST"
                action="{{ route('admin.inquiries.update', $inquiry) }}"
                x-data="{ submitting: false }"
                @submit="
                    if (submitting) {
                        $event.preventDefault();
                    } else {
                        submitting = true;
                    }
                "
                @pageshow.window="submitting = false"
                :aria-busy="submitting"
            >

                @csrf
                @method('PATCH')

                <input
                    type="hidden"
                    name="_inquiry_id"
                    value="{{ $inquiry->id }}"
                >

                <x-admin.forms.select
                    name="status"
                    :id="'inquiry-status-'.$inquiry->id"
                    label="Inquiry status"
                    :options="[
                        'new' => 'New',
                        'read' => 'Read',
                        'closed' => 'Closed'
                    ]"
                    :value="$isCurrentInquiry
                        ? old('status', $inquiry->status)
                        : $inquiry->status"
                    :use-old="false"
                    :show-errors="$isCurrentInquiry"
                    :required="true"
                />

                <div class="admin-modal-actions">

                    <x-admin.forms.button
                        type="button"
                        variant="secondary"
                        x-bind:disabled="submitting"
                        @click="$el.closest('dialog').close()"
                    >
                        Cancel
                    </x-admin.forms.button>

                    <x-admin.forms.submit-button
                        label="Update status"
                        loading-label="Updating..."
                    />

                </div>

            </form>

        </x-admin.modal>

        <!-- Reopen modal after validation error -->

        @if($isCurrentInquiry && $errors->any())

            <span
                hidden
                x-init="$nextTick(() => {
                    document.getElementById(
                        'inquiry-modal-{{ $inquiry->id }}'
                    ).showModal()
                })"
            ></span>

        @endif

    @endforeach

@endsection