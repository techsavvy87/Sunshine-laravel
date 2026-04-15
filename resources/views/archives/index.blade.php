@extends('layouts.main')
@section('title', 'Archived Appointments')

@section('page-css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
  .table th,
  .table td {
    padding-block: 0.5rem;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <div class="inline-flex items-center gap-3">
    <h3 class="text-lg font-medium">Archived Appointments</h3>
  </div>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Archives</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="card bg-base-100 shadow mt-3">
    <div class="card-body p-4">
      <div class="flex items-center justify-between mt-3">
        <form id="search_form" class="w-full" method="GET" action="{{ route('archives') }}">
          <div class="grow grid grid-cols-1 gap-2 xl:grid-cols-4">
            <div class="grid grid-cols-1 gap-2 xl:grid-cols-3 col-span-2">
              <input type="text" class="input input-sm w-full" placeholder="Customer/Pet" name="customer" value="{{ $customerPet ?? '' }}"/>
              <select class="select select-sm w-full" name="service">
                <option value="">Choose Service</option>
                @foreach($services as $service)
                <option value="{{ $service->id }}" {{ ($serviceId ?? '') == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                @endforeach
              </select>
              <select class="select select-sm w-full" name="staff">
                <option value="">Choose Staff</option>
                @foreach($staffs as $staff)
                <option value="{{ $staff->id }}" {{ ($staffId ?? '') == $staff->id ? 'selected' : '' }}>{{ $staff->profile ? $staff->profile->first_name . " " . $staff->profile->last_name : '' }}</option>
                @endforeach
              </select>
            </div>
            <input type="text" name="datetimes" class="input input-sm w-full" value="{{ $datetimes ?? '' }}" autocomplete="off"/>
            <div class="flex justify-start">
              <button class="btn btn-soft btn-primary btn-sm max-sm:btn-square">
                <span class="iconify lucide--search size-4"></span>
                <span class="hidden sm:inline">Search</span>
              </button>
            </div>
          </div>
        </form>
      </div>
      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Customer</th>
              <th>Pet</th>
              <th>Service</th>
              <th>Staff</th>
              <th style="text-align:center">Date</th>
              <th style="text-align:center">Start Time</th>
              <th style="text-align:center">End Time</th>
              <th style="text-align:center">Status</th>
              <th style="padding-left: 30px">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($finishedAppointments as $appointment)
            <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
              <td>{{ $loop->iteration }}</td>
              <td>{{ $appointment->customer->profile->first_name }} {{ $appointment->customer->profile->last_name }}</td>
              <td>
                <span>{{ $appointment->pet->name }}</span>
                @if ($appointment->pet->rating === 'green')
                  <i class="fa-solid fa-star" style="color: lightseagreen"></i>
                @elseif ($appointment->pet->rating === 'yellow')
                  <i class="fa-solid fa-star" style="color: gold"></i>
                @elseif ($appointment->pet->rating === 'red')
                  <i class="fa-solid fa-star" style="color: tomato"></i>
                @endif
              </td>
              <td>{{ $appointment->service->name }}</td>
              <td>{{ $appointment->staff_id ? ($appointment->staff->profile ? $appointment->staff->profile->first_name : $appointment->staff->name) : 'Unassigned' }}</td>
              <td style="text-align:center">{{ \Carbon\Carbon::parse($appointment->date)->format('m/d/Y') }}</td>
              <td style="text-align:center">
                {{ $appointment->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $appointment->start_time)->format('h:i A') : 'N/A' }}
              </td>
              <td style="text-align:center">
                {{ $appointment->end_time ? \Carbon\Carbon::createFromFormat('H:i:s', $appointment->end_time)->format('h:i A') : 'N/A' }}
              </td>
              <td style="text-align:center">
                @if ($appointment->status === 'finished')
                  <div class="badge badge-soft badge-success badge-sm">Finished</div>
                @elseif ($appointment->status === 'cancelled')
                  <div class="badge badge-soft badge-error badge-sm">Cancelled</div>
                @elseif ($appointment->status === 'no_show')
                  <div class="badge badge-soft badge-error badge-sm">No Show</div>
                @endif
              </td>
              <td>
                <div class="inline-flex w-fit">
                  <a class="btn btn-square btn-ghost btn-sm" href="{{ route('archive-detail', ['id' => $appointment->id]) }}">
                    <span class="iconify lucide--eye text-base-content/80 size-4"></span>
                  </a>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      {{ $finishedAppointments->links('layouts.pagination', ['items' => $finishedAppointments]) }}
    </div>
  </div>
</div>
@endsection

@section('page-js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
  $(function() {
    $('input[name="datetimes"]').daterangepicker({
      autoUpdateInput: false,
      timePicker: true,
      startDate: moment().startOf('hour'),
      endDate: moment().startOf('hour').add(32, 'hour'),
      locale: {
        cancelLabel: 'Clear'
      }
    });
    $('input[name="datetimes"]').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('MM/DD/YY hh:mm A') + ' - ' + picker.endDate.format('MM/DD/YY hh:mm A'));
    });
  });
</script>
@endsection
