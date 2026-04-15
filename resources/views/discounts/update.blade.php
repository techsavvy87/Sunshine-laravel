@extends('layouts.main')
@section('title', 'Update Discount')

@section('page-css')
<style>
.select2-container .select2-selection--multiple {
    height: 40px;
}
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
    <h3 class="text-lg font-medium">Update Discount</h3>
    <div class="breadcrumbs hidden p-0 text-sm sm:inline">
        <ul>
            <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
            <li><a href="{{ route('discounts') }}">Discounts</a></li>
            <li class="opacity-80">Update</li>
        </ul>
    </div>
</div>

<div class="mt-3">
    @include('layouts.alerts')

    @php
    $selectedServiceIds = old('service_ids', $discount->service_ids ?? []);
    $selectedCustomerIds = old('customer_ids', $discount->customer_ids ?? []);
    $applyServices = old('apply_services', empty($selectedServiceIds) ? 'all' : 'specific');
    $applyCustomers = old('apply_customers', empty($selectedCustomerIds) ? 'all' : 'specific');
    @endphp

    <form action="{{ route('update-discount') }}" method="POST">
        @csrf
        <input type="hidden" name="id" value="{{ $discount->id }}" />

        <div class="card bg-base-100 shadow mt-3">
            <div class="card-body">
                <div class="card-title">Discount Information</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                    <div class="space-y-2">
                        <label class="label" for="title"><span class="label-text">Title*</span></label>
                        <input type="text" name="title" id="title" class="input input-bordered w-full"
                            value="{{ old('title', $discount->title) }}" placeholder="e.g. Summer Grooming Promo"
                            maxlength="255" required />
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="label" for="type"><span class="label-text">Discount Type*</span></label>
                            <select name="type" id="type" class="select select-bordered w-full" required>
                                <option value="percent"
                                    {{ old('type', $discount->type) === 'percent' ? 'selected' : '' }}>
                                    Percent</option>
                                <option value="fixed" {{ old('type', $discount->type) === 'fixed' ? 'selected' : '' }}>
                                    Fixed</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="label" for="amount"><span class="label-text">Discount Amount*</span></label>
                            <label class="input input-bordered flex items-center gap-2 w-full">
                                <span id="amount_unit" class="text-base-content/70">%</span>
                                <input type="number" name="amount" id="amount" class="grow"
                                    value="{{ old('amount', $discount->amount) }}" min="0" step="0.01"
                                    placeholder="e.g. 20" required />
                            </label>
                            <label class="label"><span id="amount_hint" class="label-text-alt">Percent value from 0
                                    to 100.</span></label>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="label" for="apply_services"><span class="label-text">Services
                                Scope*</span></label>
                        <select name="apply_services" id="apply_services" class="select select-bordered w-full">
                            <option value="all" {{ $applyServices === 'all' ? 'selected' : '' }}>All Services</option>
                            <option value="specific" {{ $applyServices === 'specific' ? 'selected' : '' }}>Specific
                                Services</option>
                        </select>

                    </div>
                    <div id="service_ids_group" class="hidden space-y-2">
                        <label class="label" for="service_ids"><span class="label-text">Select
                                Services*</span></label>
                        <select name="service_ids[]" id="service_ids" class="w-full" multiple="multiple">
                            @foreach ($services as $service)
                            <option value="{{ $service->id }}"
                                {{ in_array($service->id, $selectedServiceIds) ? 'selected' : '' }}>
                                {{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
                <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="label" for="apply_customers"><span class="label-text">Customers
                                Scope*</span></label>
                        <select name="apply_customers" id="apply_customers" class="select select-bordered w-full">
                            <option value="all" {{ $applyCustomers === 'all' ? 'selected' : '' }}>All Customers</option>
                            <option value="specific" {{ $applyCustomers === 'specific' ? 'selected' : '' }}>Specific
                                Customers</option>
                        </select>

                    </div>

                    <div id="customer_ids_group" class="hidden space-y-2">
                        <label class="label" for="customer_ids"><span class="label-text">Select
                                Customers*</span></label>
                        <select name="customer_ids[]" id="customer_ids" class="w-full" multiple="multiple">
                            @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}"
                                {{ in_array($customer->id, $selectedCustomerIds) ? 'selected' : '' }}>
                                {{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="label" for="start_date"><span class="label-text">Start Date</span></label>
                        <input type="datetime-local" name="start_date" id="start_date"
                            class="input input-bordered w-full"
                            value="{{ old('start_date', optional($discount->start_date)->format('Y-m-d\\TH:i')) }}" />
                    </div>

                    <div class="space-y-2">
                        <label class="label" for="end_date"><span class="label-text">End Date</span></label>
                        <input type="datetime-local" name="end_date" id="end_date" class="input input-bordered w-full"
                            value="{{ old('end_date', optional($discount->end_date)->format('Y-m-d\\TH:i')) }}" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 mt-4">
                    <div class="space-y-2">
                        <label class="label" for="description"><span class="label-text">Description</span></label>
                        <textarea name="description" id="description" class="textarea textarea-bordered w-full min-h-24"
                            placeholder="Optional description">{{ old('description', $discount->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a class="btn btn-sm btn-ghost" href="{{ url()->previous() }}">
                <span class="iconify lucide--x size-4"></span>
                Cancel
            </a>
            <button class="btn btn-sm btn-primary" type="submit">
                <span class="iconify lucide--check size-4"></span>
                Update
            </button>
        </div>
    </form>
</div>
@endsection

@section('page-js')
<script>
$(function() {
    $('#service_ids').select2({
        width: '100%',
        placeholder: 'Select services'
    });

    $('#customer_ids').select2({
        width: '100%',
        placeholder: 'Select customers'
    });

    $('#apply_services').on('change', function() {
        updateScopeSelectState('#apply_services', '#service_ids', '#service_ids_group');
    });

    $('#apply_customers').on('change', function() {
        updateScopeSelectState('#apply_customers', '#customer_ids', '#customer_ids_group');
    });

    $('#type').on('change', function() {
        updateDiscountValueUi();
    });

    updateScopeSelectState('#apply_services', '#service_ids', '#service_ids_group');
    updateScopeSelectState('#apply_customers', '#customer_ids', '#customer_ids_group');
    updateDiscountValueUi();
});

function updateScopeSelectState(scopeSelector, valueSelector, groupSelector) {
    const scope = $(scopeSelector).val();
    const isSpecific = scope === 'specific';
    $(groupSelector).toggleClass('hidden', !isSpecific);

    if (!isSpecific) {
        $(valueSelector).val([]).trigger('change');
    }
}

function updateDiscountValueUi() {
    const type = $('#type').val();
    const isPercent = type === 'percent';
    $('#amount_unit').text(isPercent ? '%' : '$');
    $('#amount').attr('max', isPercent ? '100' : null);
    $('#amount').attr('placeholder', isPercent ? 'e.g. 20' : 'e.g. 10.00');
    $('#amount_hint').text(isPercent ? 'Percent value from 0 to 100.' : 'Fixed amount in dollars.');
}
</script>
@endsection