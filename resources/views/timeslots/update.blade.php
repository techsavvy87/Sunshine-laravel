@extends('layouts.main')
@section('title', 'Update Time Slot')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('src/libs/select2/select2.min.css') }}" />
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Update Time Slot</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('services') }}">Services</a></li>
      <li><a href="{{ route('timeslots') }}">Time Slots</a></li>
      <li class="opacity-80">Update</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('update-timeslot') }}" method="POST" id="update_form">
    @csrf
    <input type="hidden" name="timeslot_id" id="timeslot_id" value="{{ $timeSlot->id }}" />
    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <div class="fieldset mt-2 grid grid-cols-1 gap-6 xl:grid-cols-3">
          <div class="space-y-2">
            <label class="fieldset-label" for="service">Service*</label>
            <select class="select w-full" name="service" id="service" value="{{ $timeSlot->service_id }}" disabled>
              <option value="" hidden selected>Choose a service</option>
              @foreach($services as $service)
                <option value="{{ $service->id }}" {{ $service->id == $timeSlot->service_id ? 'selected' : '' }}>{{ $service->name }}</option>
              @endforeach
            </select>
            <input type="hidden" name="service" value="{{ $timeSlot->service_id }}" />
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="staff">Staff</label>
            <select class="select w-full" name="staff" id="staff" value="{{ $timeSlot->staff_id }}">
              <option value="" hidden selected>Choose a staff</option>
              @foreach($staffs as $staff)
                <option value="{{ $staff->id }}" {{ $staff->id == $timeSlot->staff_id ? 'selected' : '' }}>{{ $staff->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="fieldset mt-3 grid grid-cols-1 gap-6 xl:grid-cols-3">
          <div class="space-y-2">
            <label class="fieldset-label" for="date">Date*</label>
            <div class="w-full">
              <div class="btn btn-outline border-base-300 flex items-center gap-2 pointer-events-none opacity-60">
                <span class="iconify lucide--calendar text-base-content/60 size-4"></span>
                <p class="text-start" id="button_cally_target">{{ $timeSlot->date ? Carbon\Carbon::parse($timeSlot->date)->format('Y-m-d') : '-' }}</p>
              </div>
            </div>
            <input type="hidden" name="date" id="date" value="{{ $timeSlot->date ? Carbon\Carbon::parse($timeSlot->date)->format('Y-m-d') : '' }}" />
          </div>
          @php
            $svc = $timeSlot->service ?? null;
            $catName = $svc && $svc->category ? strtolower($svc->category->name) : '';
            $svcName = $svc ? strtolower($svc->name) : '';
            $isDaycareSvc = str_contains($catName, 'daycare') || str_contains($svcName, 'daycare');
          @endphp
          <div class="space-y-2" id="daycare_type_group" style="{{ $isDaycareSvc ? '' : 'display: none;' }}">
            <label class="fieldset-label" for="daycare_type">Daycare Type</label>
            <select class="select w-full" name="daycare_type" id="daycare_type">
              <option value="" hidden selected>Choose daycare type</option>
              <option value="half" {{ $timeSlot->daycare_type == 'half' ? 'selected' : '' }}>Half Day</option>
              <option value="full" {{ $timeSlot->daycare_type == 'full' ? 'selected' : '' }}>Full Day</option>
            </select>
          </div>
          <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
            <div class="space-y-2">
              <label class="fieldset-label" for="start_time">Start Time*</label>
              <label class="input w-40">
                <span class="iconify lucide--clock text-base-content/60 size-4"></span>
                <input
                  placeholder="e.g. 09:00 AM"
                  id="start_time"
                  name="start_time"
                  type="time"
                  min="09:00"
                  max="18:00"
                  value="{{ \Carbon\Carbon::createFromFormat('H:i:s', $timeSlot->start_time)->format('H:i') }}"
                />
              </label>
            </div>
            <div class="space-y-2">
              <label class="fieldset-label" for="end_time">End Time*</label>
              <label class="input w-40">
                <span class="iconify lucide--clock text-base-content/60 size-4"></span>
                <input
                  placeholder="e.g. 06:00 PM"
                  id="end_time"
                  name="end_time"
                  type="time"
                  min="09:00"
                  max="18:00"
                  value="{{ \Carbon\Carbon::createFromFormat('H:i:s', $timeSlot->end_time)->format('H:i') }}"
                />
              </label>
            </div>
          </div>
          <div id="overlap_warning" class="xl:col-span-3 hidden">
            <div class="alert alert-info">
              <span class="iconify lucide--info size-5"></span>
              <div>
                <h3 class="font-bold">Overlapping Time Slots Detected</h3>
                <div class="text-xs" id="overlap_details"></div>
                <div class="text-xs mt-1 font-semibold">These slots will be automatically removed when you save.</div>
              </div>
            </div>
          </div>
        </div>
        <div class="fieldset mt-3 grid grid-cols-1 gap-6 xl:grid-cols-3">
          <div class="space-y-2">
            <label class="fieldset-label" for="capacity">Capacity</label>
            <input class="input w-full" placeholder="e.g. 2" id="capacity" name="capacity" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" value="{{ $timeSlot->capacity }}"/>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="booked_count">Booked Count</label>
            <input class="input w-full" placeholder="e.g. 0" id="booked_count" name="booked_count" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" value="{{ $timeSlot->booked_count }}"/>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="status">Status*</label>
            <select class="select" name="status" id="status" value="{{ $timeSlot->status }}">
              <option value="" hidden selected>Choose a status</option>
              <option value="available" {{ $timeSlot->status == 'available' ? 'selected' : '' }}>Available</option>
              <option value="blocked" {{ $timeSlot->status == 'blocked' ? 'selected' : '' }}>Blocked</option>
              <option value="full" {{ $timeSlot->status == 'full' ? 'selected' : '' }}>Full</option>
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

  <script>
    let overlappingCount = 0;

    $(document).ready(function() {
      $('#staff').select2({
        placeholder: "Choose a staff",
      });

      // Check for overlap when start_time or end_time changes
      $('#start_time, #end_time').on('change', function() {
        checkTimeSlotOverlap();
      });
    });

    function checkTimeSlotOverlap() {
      const timeslotId = $('#timeslot_id').val();
      const startTime = $('#start_time').val();
      const endTime = $('#end_time').val();

      if (!startTime || !endTime) {
        return;
      }

      // Check if end time is after start time
      if (endTime <= startTime) {
        $('#alert_message').text('End time must be after start time.');
        alert_modal.showModal();
        return;
      }

      $.ajax({
        url: '{{ route("check-timeslot-overlap") }}',
        method: 'POST',
        data: {
          timeslot_id: timeslotId,
          start_time: startTime,
          end_time: endTime
        },
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
          if (response.overlap) {
            overlappingCount = response.overlapping_slots.length;
            
            // Show visual warning on page
            let overlappingSlotsHtml = '<ul class="mt-2 ml-4 list-disc">';
            response.overlapping_slots.forEach(function(slot) {
              overlappingSlotsHtml += `<li>${slot.start_time} - ${slot.end_time} (${slot.staff})</li>`;
            });
            overlappingSlotsHtml += '</ul>';
            
            $('#overlap_details').html(`Found ${overlappingCount} overlapping slot(s):` + overlappingSlotsHtml);
            $('#overlap_warning').removeClass('hidden');
          } else {
            overlappingCount = 0;
            $('#overlap_warning').addClass('hidden');
          }
        },
        error: function(xhr) {
          console.error('Error checking overlap:', xhr);
        }
      });
    }

    function saveTimeSlot() {
      const startTime = $('#start_time').val();
      const endTime = $('#end_time').val();
      const status = $('#status').val();

      if (!startTime || !endTime || !status) {
        $('#alert_message').text('Please fill in all required fields.');
        alert_modal.showModal();
        return;
      }

      if (endTime <= startTime) {
        $('#alert_message').text('End time must be after start time.');
        alert_modal.showModal();
        return;
      }

      $('#update_form').submit();
    }
  </script>
@endsection