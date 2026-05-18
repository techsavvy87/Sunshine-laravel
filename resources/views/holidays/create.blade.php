@extends('layouts.main')
@section('title', 'Create Holiday')

@section('page-css')
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Create Holiday</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('holidays') }}">Holidays</a></li>
      <li class="opacity-80">Create</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('create-holiday') }}" method="POST" id="create_form">
    @csrf
    <div class="card bg-base-100 shadow mt-3">
      <div class="card-body">
        <div class="card-title">Holiday Information</div>
        <div class="fieldset mt-2 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="fieldset-label" for="holiday_name">Holiday Name*</label>
              <input class="input w-full" id="holiday_name" name="holiday_name" type="text" placeholder="e.g. Christmas" value="{{ old('holiday_name') }}" required />
            </div>
            <div class="space-y-1">
              <label class="fieldset-label" for="fixed_price">Holiday Price (USD)*</label>
              <label class="input w-full focus:outline-0">
                <input class="grow focus:outline-0" id="fixed_price" name="fixed_price" type="text" placeholder="e.g. 20" value="{{ old('fixed_price') }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" required />
                <span class="badge badge-ghost badge-sm">USD</span>
              </label>
            </div>
          </div>

          <div class="divider my-2">Application Type</div>

          <div class="space-y-3">
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2">
                <input
                  class="radio"
                  type="radio"
                  id="application_one_day"
                  name="application_type"
                  value="one_day"
                  {{ old('application_type', 'one_day') === 'one_day' ? 'checked' : '' }}
                  onchange="toggleDateFields()"
                  required
                />
                <label class="fieldset-label cursor-pointer" for="application_one_day">One Day</label>
              </div>
              <div class="flex items-center gap-2">
                <input
                  class="radio"
                  type="radio"
                  id="application_period_days"
                  name="application_type"
                  value="period_days"
                  {{ old('application_type') === 'period_days' ? 'checked' : '' }}
                  onchange="toggleDateFields()"
                  required
                />
                <label class="fieldset-label cursor-pointer" for="application_period_days">Period Days</label>
              </div>
            </div>
            <p class="text-xs text-base-content/60">One Day: Price applies to a single date. Period Days: Price applies across a date range.</p>
          </div>

          <div id="date_fields_container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div id="one_day_field" class="space-y-1" style="display: {{ old('application_type', 'one_day') === 'one_day' ? 'block' : 'none' }};">
              <label class="fieldset-label" for="holiday_date">Holiday Date*</label>
              <input class="input w-full" id="holiday_date" name="holiday_date" type="date" value="{{ old('holiday_date', '') }}" />
            </div>

            <div id="period_days_fields" style="display: {{ old('application_type') === 'period_days' ? 'grid' : 'none' }};" class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="fieldset-label" for="period_start_date">Start Date*</label>
                <input class="input w-full" id="period_start_date" name="holiday_date" type="date" value="{{ old('holiday_date', '') }}" />
              </div>
              <div class="space-y-1">
                <label class="fieldset-label" for="end_date">End Date*</label>
                <input class="input w-full" id="end_date" name="end_date" type="date" value="{{ old('end_date', '') }}" />
              </div>
            </div>
          </div>

          <div class="mt-4">
            <div class="flex items-center gap-2">
              <input type="hidden" name="restrict_bookings" value="no" />
              <input
                class="checkbox"
                type="checkbox"
                id="restrict_bookings"
                name="restrict_bookings"
                value="yes"
                {{ old('restrict_bookings') === 'yes' ? 'checked' : '' }}
              />
              <label class="fieldset-label cursor-pointer" for="restrict_bookings">Restrict Bookings</label>
            </div>
            <p class="text-sm text-base-content/70 mt-1">When enabled, all services will have restricted bookings on this holiday.</p>
          </div>
        </div>
        <div class="mt-5 flex justify-end gap-3">
          <a class="btn btn-ghost btn-sm" href="{{ route('holidays') }}">Cancel</a>
          <button class="btn btn-sm btn-primary gap-1" type="submit" id="save_btn">
            <span class="loading loading-spinner size-3.5" style="display:none;"></span>
            Save
          </button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection

@section('page-js')
<script>
  function toggleDateFields() {
    const applicationType = $('input[name="application_type"]:checked').val();
    const oneDayField = $('#one_day_field');
    const periodDaysFields = $('#period_days_fields');
    const holidayDateInput = $('#holiday_date');
    const periodStartInput = $('#period_start_date');
    const endDateInput = $('#end_date');

    if (applicationType === 'one_day') {
      // Show one day field
      oneDayField.show();
      periodDaysFields.hide();
      
      // Enable one day input and disable period inputs
      holidayDateInput.prop('disabled', false).prop('required', true);
      periodStartInput.prop('disabled', true).prop('required', false);
      endDateInput.prop('disabled', true).prop('required', false);
      
      // Clear period inputs
      periodStartInput.val('');
      endDateInput.val('');
    } else {
      // Show period days fields
      oneDayField.hide();
      periodDaysFields.show();
      
      // Disable one day input and enable period inputs
      holidayDateInput.prop('disabled', true).prop('required', false);
      periodStartInput.prop('disabled', false).prop('required', true);
      endDateInput.prop('disabled', false).prop('required', true);
      
      // Clear one day input
      holidayDateInput.val('');
    }
  }

  // Initialize on page load
  $(document).ready(function() {
    toggleDateFields();
  });

  $('#create_form').on('submit', function(e) {
    const applicationType = $('input[name="application_type"]:checked').val();
    
    if (applicationType === 'one_day') {
      const holidayDate = $('#holiday_date').val();
      console.log('One Day - Holiday Date:', holidayDate);
      if (!holidayDate) {
        e.preventDefault();
        alert('Please select a holiday date.');
        return false;
      }
    } else {
      const startDate = $('#period_start_date').val();
      const endDate = $('#end_date').val();
      console.log('Period Days - Start:', startDate, 'End:', endDate);
      if (!startDate || !endDate) {
        e.preventDefault();
        alert('Please select both start and end dates for the holiday period.');
        return false;
      }
    }
  });
</script>
@endsection
