@extends('layouts.main')
@section('title', 'Appointment Audit Log')

@section('page-css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('content')
<div class="flex items-center justify-between">
  <div class="inline-flex items-center gap-3">
    <h3 class="text-lg font-medium">Appointment Audit Log</h3>
  </div>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Appointment Audit Log</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="card bg-base-100 shadow mt-3">
    <div class="card-body p-4">
      <div class="flex items-center justify-between mt-3">
        <form class="w-full" method="GET" action="{{ route('appointment-audit-log') }}">
          <div class="grow grid grid-cols-1 gap-2 xl:grid-cols-4">
            <div class="grid grid-cols-1 gap-2 xl:grid-cols-3 col-span-2">
              <input type="text" class="input input-sm w-full" placeholder="Pet / Owner" name="pet_owner" value="{{ $petOwner ?? '' }}"/>
              <select class="select select-sm w-full" name="service">
                <option value="" hidden selected>Choose Type</option>
                @foreach($services as $service)
                <option value="{{ $service->id }}" {{ ($serviceId ?? '') == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                @endforeach
              </select>
              <select class="select select-sm w-full" name="staff">
                <option value="" hidden selected>Choose Employee</option>
                @foreach($staffs as $staff)
                <option value="{{ $staff->id }}" {{ ($staffId ?? '') == $staff->id ? 'selected' : '' }}>{{ $staff->profile ? $staff->profile->first_name . ' ' . $staff->profile->last_name : '' }}</option>
                @endforeach
              </select>
            </div>
            <input type="text" name="datetimes" class="input input-sm w-full" value="{{ $datetimes ?? '' }}" placeholder="Date range" autocomplete="off"/>
            <div class="flex justify-start gap-2">
              <button class="btn btn-soft btn-primary btn-sm max-sm:btn-square">
                <span class="iconify lucide--search size-4"></span>
                <span class="hidden sm:inline">Search</span>
              </button>
              @if(!empty($petOwner) || !empty($datetimes) || !empty($serviceId) || !empty($staffId))
              <a href="{{ route('appointment-audit-log') }}" class="btn btn-ghost btn-sm">Clear</a>
              @endif
            </div>
          </div>
        </form>
      </div>
      <div class="mt-4 overflow-x-auto">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Pet / Owner</th>
              <th>Date / Time</th>
              <th>Type</th>
              <th>Description</th>
              <th>Employee</th>
              @if(hasPermission(29, 'can_delete'))
              <th class="w-12">Action</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @forelse($logs as $log)
            <tr>
              <td class="text-sm">
                <div class="flex items-center gap-2">
                  <div class="avatar">
                    <div class="mask mask-squircle w-8 h-8 bg-base-200">
                      @if(empty($log->pet_avatar))
                        <img src="{{ asset('images/no_image.jpg') }}" alt="Pet">
                      @else
                        <img src="{{ asset('storage/pets/' . $log->pet_avatar) }}" alt="Pet">
                      @endif
                    </div>
                  </div>
                  <div class="flex flex-col">
                    <span class="font-medium">{{ $log->pet_name ?? '—' }}</span>
                    <span class="text-xs text-base-content/70">{{ $log->owner_name ?? '—' }}</span>
                  </div>
                </div>
              </td>
              <td class="text-sm whitespace-nowrap">
                {{ $log->created_at ? $log->created_at->format('d/m/Y h:i A') : '—' }}
              </td>
              <td class="text-sm whitespace-nowrap">{{ $log->type ?? '—' }}</td>
              <td class="text-sm max-w-md">{{ $log->description }}</td>
              <td class="text-sm whitespace-nowrap">{{ $log->employee ?? '—' }}</td>
              @if(hasPermission(29, 'can_delete'))
              <td>
                <button type="button" class="btn btn-square btn-ghost btn-sm text-error btn-delete-audit" data-id="{{ $log->id }}" aria-label="Delete">
                  <span class="iconify lucide--trash-2 size-4"></span>
                </button>
              </td>
              @endif
            </tr>
            @empty
            <tr>
              <td colspan="{{ hasPermission(29, 'can_delete') ? 6 : 5 }}" class="text-center text-base-content/60 py-8">No audit log entries found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($logs->hasPages())
      <div class="mt-4">
        {{ $logs->links('layouts.pagination', ['items' => $logs]) }}
      </div>
      @endif
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
    <p class="py-4" id="delete_modal_message">You are about to delete this audit record. Would you like to proceed?</p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost btn-sm">No</button>
      </form>
      <form id="delete_form" method="POST" action="{{ route('appointment-audit-log-delete') }}">
        @csrf
        <input type="hidden" name="id" id="delete_audit_id" value="" />
        <button class="btn btn-error btn-sm">Delete</button>
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
    $(document).on('click', '.btn-delete-audit', function() {
      var id = $(this).data('id');
      $('#delete_audit_id').val(id);
      document.getElementById('delete_modal').showModal();
    });

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
