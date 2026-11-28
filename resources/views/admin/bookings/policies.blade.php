@extends('layouts.admin')

@section('title', 'Booking policies')

@section('content')

    <x-admin.forms.section
        title="Property check-in & cancellation terms"
        description="Manage the property's check-in, check-out and cancellation policies."
    >

        <div class="admin-feedback admin-feedback-warning">

            <strong>Important</strong>

            <p>
                Configure the property's actual rules before
                accepting requests. Existing confirmed bookings
                retain their accepted terms; pending requests
                require customer acceptance of changed terms.
            </p>

        </div>

        <form
            method="POST"
            action="{{ route('admin.booking-policies.save') }}"
            class="admin-management-form"
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
            @method('PUT')

            <div class="admin-management-heading">

                <h2>Arrival & departure</h2>

                <p>
                    All times are based on Sri Lanka local time.
                </p>

            </div>

            <div class="admin-form-grid">

                <x-admin.forms.input
                    type="time"
                    name="check_in_time"
                    id="policy-check-in"
                    label="Check-in time"
                    :value="substr($policy->check_in_time ?? '', 0, 5)"
                    :required="true"
                />

                <x-admin.forms.input
                    type="time"
                    name="check_out_time"
                    id="policy-check-out"
                    label="Check-out time"
                    :value="substr($policy->check_out_time ?? '', 0, 5)"
                    :required="true"
                />

            </div>

            <div class="admin-room-form-group">

                <h3 class="admin-room-group-title">
                    Cancellation policy
                </h3>

                <p class="admin-room-group-description">
                    Define the cancellation and refund terms
                    applicable to guest reservations.
                </p>

                <x-admin.forms.textarea
                    name="cancellation_policy"
                    id="policy-cancellation"
                    label="Cancellation & refund terms"
                    :value="$policy->cancellation_policy"
                    :rows="8"
                    minlength="10"
                    maxlength="5000"
                    help="Enter between 10 and 5,000 characters."
                    :required="true"
                />

            </div>

            <div class="admin-management-actions">

                <x-admin.forms.submit-button
                    label="Save property terms"
                    loading-label="Saving property terms..."
                />

            </div>

        </form>

    </x-admin.forms.section>

@endsection