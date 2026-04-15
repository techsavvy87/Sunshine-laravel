@extends('layouts.main')
@section('title', 'Update Holiday')

@section('page-css')
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Update Holiday</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('holidays') }}">Holidays</a></li>
      <li class="opacity-80">Update</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('update-holiday') }}" method="POST" id="update_form">
    @csrf
    <input type="hidden" name="holiday_id" value="{{ $holiday->id }}">
    <div class="card bg-base-100 shadow mt-3">
      <div class="card-body">
        <div class="card-title">Holiday Information</div>
        <div class="fieldset mt-2 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-1">
              <label class="fieldset-label" for="holiday_name">Holiday Name*</label>
              <input class="input w-full" id="holiday_name" name="holiday_name" type="text" placeholder="e.g. New Year's Day" value="{{ old('holiday_name', $holiday->name) }}" required />
            </div>
            <div class="space-y-1">
              <label class="fieldset-label" for="holiday_date">Holiday Date*</label>
              <div class="dropdown w-full">
                <div role="button" class="btn btn-outline border-base-300 flex items-center gap-2" tabindex="0">
                  <span class="iconify lucide--calendar text-base-content/80 size-4.5"></span>
                  <p class="text-start" id="button_cally_target">{{ old('holiday_date', $holiday->date) }}</p>
                  <span class="iconify lucide--chevron-down text-base-content/70 size-4"></span>
                </div>
                <div class="dropdown-content mt-2" tabindex="0">
                  <calendar-date class="cally bg-base-100 rounded-box shadow-md transition-all hover:shadow-lg" id="button_cally_element" value="{{ old('holiday_date', $holiday->date) }}" >
                    <span class="iconify lucide--chevron-left" slot="previous"></span>
                    <span class="iconify lucide--chevron-right" slot="next"></span>
                    <calendar-month></calendar-month>
                  </calendar-date>
                </div>
              </div>
              <input type="hidden" id="holiday_date" name="holiday_date" value="{{ old('holiday_date', $holiday->date) }}" required />
            </div>
            <div class="space-y-1">
              <label class="fieldset-label" for="percent_increase">Percent Increase*</label>
              <input
                class="input w-full"
                id="percent_increase"
                name="percent_increase"
                type="text"
                placeholder="e.g. 10"
                value="{{ old('percent_increase', $holiday->percent_increase) }}"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                required
              />
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
                {{ old('restrict_bookings', $holiday->restrict_bookings) === 'yes' ? 'checked' : '' }}
              />
              <label class="fieldset-label cursor-pointer" for="restrict_bookings">Restrict Bookings</label>
            </div>
            <p class="text-sm text-base-content/70 mt-1">When enabled, all services will have restricted bookings on this holiday.</p>
          </div>
          <div class="mt-4">
            <label class="fieldset-label mb-2">Service Max Values</label>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              @foreach($services as $service)
              @php
                $holidayService = $holiday->holidayServices->firstWhere('service_id', $service->id);
                $maxValue = $holidayService ? $holidayService->max_value : null;
                $isDisabled = $maxValue === null;
              @endphp
              <div class="card bg-base-200 p-4">
                <div class="space-y-3">
                  <div>
                    <label class="font-medium text-sm">{{ $service->name }}</label>
                  </div>
                  <div class="space-y-2">
                    <input
                      type="hidden"
                      name="services[{{ $service->id }}][service_id]"
                      value="{{ $service->id }}"
                    />
                    <div class="flex items-center gap-2">
                      <input
                        class="input input-sm flex-1 service-max-value"
                        type="text"
                        name="services[{{ $service->id }}][max_value]"
                        placeholder="Max value"
                        value="{{ $maxValue ?? '' }}"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        {{ $isDisabled ? 'disabled' : '' }}
                      />
                      <div class="flex items-center gap-1">
                        <input
                          class="checkbox checkbox-sm service-no-restriction"
                          type="checkbox"
                          {{ $isDisabled ? 'checked' : '' }}
                          onchange="handleNoRestrictionService(this, {{ $service->id }})"
                        />
                        <label class="text-xs">No Restriction</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
        <div class="mt-5 flex justify-end gap-3">
          <a class="btn btn-ghost btn-sm" href="{{ route('holidays') }}">Cancel</a>
          <button class="btn btn-sm btn-primary gap-1" type="submit" id="save_btn">
            <span class="loading loading-spinner size-3.5" style="display:none;"></span>
            Update
          </button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection

@section('page-js')
<script src="{{ asset('src/assets/ui-components-calendar.js') }}"></script>
<script type="module" src="https://unpkg.com/cally"></script>
<script>
  document.getElementById("button_cally_element")?.addEventListener("change", (e) => {
    const dateValue = e.target.value;
    document.getElementById("button_cally_target").innerText = dateValue;
    document.getElementById("holiday_date").value = dateValue;
  })

  function handleNoRestrictionService(checkbox, serviceId) {
    const maxValueInput = $(`input[name="services[${serviceId}][max_value]"]`);
    maxValueInput.prop('disabled', checkbox.checked);
    if (checkbox.checked) {
      maxValueInput.val('');
    }
  }

  $('#update_form').on('submit', function(e) {
    const holidayDate = $('#holiday_date').val();
    if (!holidayDate || holidayDate === '-') {
      e.preventDefault();
      alert('Please select a holiday date.');
      return false;
    }

    // Remove disabled fields before submit
    $('.service-max-value[disabled]').each(function() {
      $(this).prop('disabled', false);
      $(this).val('');
    });
  });
</script>
@endsection
