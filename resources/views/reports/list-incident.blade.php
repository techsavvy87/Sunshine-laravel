@extends('layouts.main')
@section('title', 'Incident Reports')

@section('page-css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
  .table th,
  .table td {
    padding-block: 0.6rem;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <div class="inline-flex items-center gap-3">
    <h3 class="text-lg font-medium">Incident Reports</h3>
  </div>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Incident Reports</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="card bg-base-100 shadow mt-3">
    <div class="card-body p-4">
      <div class="flex items-center justify-between mt-3">
        <form id="search_form" class="w-full" method="GET" action="{{ route('list-incident-reports', ['serviceId' => $serviceId]) }}">
          <div class="grow grid grid-cols-1 gap-2 xl:grid-cols-5">
            <label class="input input-sm w-full">
              <span class="iconify lucide--search text-base-content/80 size-3.5"></span>
              <input class="nput input-sm w-full" name="pet" placeholder="Search Pet" aria-label="Search Pet" value="{{ $pet }}"/>
            </label>
            <select class="select select-sm w-full" name="staff" value="{{ $staffId }}">
              <option value="" hidden>Choose Staff</option>
              <option value="-1" {{ $staffId == -1 ? 'selected' : '' }}>All Staff</option>
              @foreach($staffs as $staff)
              <option value="{{ $staff->id }}" {{ $staffId == $staff->id ? 'selected' : '' }}>{{ $staff->profile ? $staff->profile->first_name . " " . $staff->profile->last_name : '' }}</option>
              @endforeach
            </select>
            <div class="xl:col-span-3">
              <div class="grid grid-cols-1 gap-2 xl:grid-cols-2">
                <div class="flex justify-start">
                  <button class="btn btn-soft btn-primary btn-sm max-sm:btn-square">
                    <span class="iconify lucide--search size-4"></span>
                    <span class="hidden sm:inline">Search</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
        <div class="inline-flex items-center gap-2">
          <a aria-label="Create seller link" class="btn btn-primary btn-sm max-sm:btn-square" href="{{ route('add-incident-report', ['serviceId' => $serviceId]) }}">
            <span class="iconify lucide--plus size-4"></span>
            <span class="hidden sm:inline">New</span>
          </a>
        </div>
      </div>
      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Pets</th>
              <th>Employees</th>
              <th style="text-align:center">Injury Type</th>
              <th style="text-align:center">Emegency</th>
              <th style="text-align:center">Contact Owner</th>
              <th style="text-align:center">Treatment</th>
              <th style="text-align:center">Resolution</th>
              <th style="padding-left: 30px">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($incidentReports as $report)
            <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
              <td>{{ $loop->iteration }}</td>
              <td>{{ $report->pets->pluck('name')->implode(', ') }}</td>
              <td>{{ $report->staffs->pluck('name')->implode(', ') }}</td>
              <td style="text-align:center">{{ $report->injury_type }}</td>
              <td style="text-align:center">{{ $report->is_emergency }}</td>
              <td style="text-align:center">{{ $report->contact_owner }}</td>
              <td style="text-align:center">{{ $report->treatment_type }}</td>
              <td style="text-align:center">{{ $report->vet_results }}</td>
              <td>
                <div class="inline-flex w-fit">
                  <a class="btn btn-square btn-ghost btn-sm" href="{{ route('edit-incident-report', ['id' => $report->id]) }}">
                    <span class="iconify lucide--pencil text-base-content/80 size-4"></span>
                  </a>
                  <button onclick="confirmDelete({{ $report }})" class="btn btn-square btn-error btn-outline btn-sm border-transparent">
                    <span class="iconify lucide--trash size-4"></span>
                  </button>
                </div>
              </td>
            </tr>
            @endforeach
          </thead>
        </table>
      </div>
      {{ $incidentReports->links('layouts.pagination', ['items' => $incidentReports]) }}
    </div>
  </div>
</div>
<dialog id="delete_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium">
      Confirm Delete
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4" id="delete_modal_message"></p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost btn-sm">No</button>
      </form>
      <form id="delete_form" method="POST" action="{{ route('delete-incident-report') }}">
        @csrf
        <input type="hidden" name="incident_report_id" value="" />
        <button class="btn btn-error">Delete</button>
      </form>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>
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

  function confirmDelete(report) {
    const message = `You are about to delete the incident report ${report.id}. Would you like to proceed?`;
    $('#delete_modal_message').text(message);
    $('#delete_form input[name=incident_report_id]').val(report.id);
    delete_modal.showModal();
  }
</script>
@endsection