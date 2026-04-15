@extends('layouts.main')
@section('title', 'Create Time Slot')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('src/libs/select2/select2.min.css') }}" />
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Create Time Slot</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('services') }}">Services</a></li>
      <li><a href="{{ route('timeslots') }}">Time Slots</a></li>
      <li class="opacity-80">Create</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('create-timeslot') }}" method="POST" id="create_form">
    @csrf
    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <div class="fieldset mt-2 grid grid-cols-1 gap-6 xl:grid-cols-3">
          <div class="space-y-2">
            <label class="fieldset-label" for="service">Service*</label>
            <select class="select w-full" name="service" id="service">
              <option value="" hidden selected>Choose a service</option>
              @foreach($services as $service)
                <option value="{{ $service->id }}">{{ $service->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="staff">Staff</label>
            <select class="select w-full" name="staff" id="staff">
              <option value="" hidden selected>Choose a staff</option>
              @foreach($staffs as $staff)
                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="fieldset mt-3 grid grid-cols-1 gap-6 xl:grid-cols-3">
          <input type="hidden" id="date" name="date" />
          <div class="space-y-2">
            <label class="fieldset-label" for="date">Date*</label>
            <div class="dropdown w-full">
              <div role="button" class="btn btn-outline border-base-300 flex items-center gap-2" tabindex="0">
                <span class="iconify lucide--calendar text-base-content/60 size-4"></span>
                <p class="text-start" id="button_cally_target">-</p>
                <span class="iconify lucide--chevron-down text-base-content/70 size-4"></span>
              </div>
              <div class="dropdown-content mt-2" tabindex="0">
                <calendar-date class="cally bg-base-100 rounded-box shadow-md transition-all hover:shadow-lg" id="button_cally_element" value="-" >
                  <span class="iconify lucide--chevron-left" slot="previous"></span>
                  <span class="iconify lucide--chevron-right" slot="next"></span>
                  <calendar-month></calendar-month>
                </calendar-date>
              </div>
            </div>
          </div>
          <div class="space-y-2" id="daycare_type_group" style="display: none;">
            <label class="fieldset-label" for="daycare_type">Daycare Type</label>
            <select class="select w-full" name="daycare_type" id="daycare_type">
              <option value="" hidden selected>Choose daycare type</option>
              <option value="half">Half Day</option>
              <option value="full">Full Day</option>
            </select>
          </div>
          <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
            <div class="space-y-2">
              <label class="fieldset-label" for="start_time">Start Time*</label>
              <label class="input w-40">
                <span class="iconify lucide--clock text-base-content/60 size-4"></span>
                <input placeholder="e.g. 09:00 AM" id="start_time" name="start_time" type="time" min="09:00" max="18:00"/>
              </label>
            </div>
            <div class="space-y-2">
              <label class="fieldset-label" for="end_time">End Time*</label>
              <label class="input w-40">
                <span class="iconify lucide--clock text-base-content/60 size-4"></span>
                <input placeholder="e.g. 06:00 PM" id="end_time" name="end_time" type="time" min="09:00" max="18:00"/>
              </label>
            </div>
          </div>
        </div>
        <div class="fieldset mt-3 grid grid-cols-1 gap-6 xl:grid-cols-3">
          <div class="space-y-2">
            <label class="fieldset-label" for="capacity">Capacity</label>
            <input class="input w-full" placeholder="e.g. 2" id="capacity" name="capacity" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="booked_count">Booked Count</label>
            <input class="input w-full" placeholder="e.g. 0" id="booked_count" name="booked_count" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="status">Status*</label>
            <select class="select" name="status" id="status">
              <option value="" hidden selected>Choose a status</option>
              <option value="available">Available</option>
              <option value="blocked">Blocked</option>
              <option value="full">Full</option>
            </select>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
      <a class="btn btn-sm btn-ghost" href="{{ url()->previous() }}">
        <span class="iconify lucide--x size-4"></span>
        Cancel
      </a>
      <button class="btn btn-sm btn-primary" type="button" onclick="saveTimeSlot()">
        <span class="iconify lucide--check size-4"></span>
        Save
      </button>
    </div>
  </form>
</div>
@endsection

@section('page-js')
  <script src="{{ asset('src/libs/select2/select2.min.js') }}"></script>
  <script src="{{ asset('src/assets/ui-components-calendar.js') }}"></script>
  <script type="module" src="https://unpkg.com/cally"></script>

  <script>
    document.getElementById("button_cally_element")?.addEventListener("change", (e) => {
      document.getElementById("button_cally_target").innerText = e.target.value
    })

    $(document).ready(function() {
      $('#staff').select2({
        placeholder: "Choose a staff",
      });

      // Toggle daycare type based on selected service text (contains 'daycare')
      $('#service').on('change', function() {
        const selectedText = $(this).find('option:selected').text().toLowerCase();
        const isDaycare = selectedText.includes('daycare');
        if (isDaycare) {
          $('#daycare_type_group').show();
        } else {
          $('#daycare_type_group').hide();
          $('#daycare_type').val('');
        }
      }).trigger('change');
    });

    function saveTimeSlot() {
      const service = $('#service').val();
      const date = $('#button_cally_target').text();
      const startTime = $('#start_time').val();
      const endTime = $('#end_time').val();
      const status = $('#status').val();

      if (!service || !date || !startTime || !endTime || !status) {
        $('#alert_message').text('Please fill in all required fields.');
        alert_modal.showModal();
        return;
      }

      if (date) {
        $('#date').val(date);
      }

      $('#create_form').submit();
    }
  </script>
@endsection