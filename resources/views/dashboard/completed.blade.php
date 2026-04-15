@extends('layouts.main')
@section('title', 'Completed Appointments')

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Completed Appointments</h3>
  <a href="{{ route('list-dashboard', ['id' => 1]) }}" class="btn btn-ghost btn-sm">
    <span class="iconify lucide--arrow-left size-4"></span>
    Back to Dashboard
  </a>
</div>

<div class="mt-6">
  <div class="card bg-base-100 shadow">
    <div class="card-body p-0">
      <div class="flex items-center gap-3 px-5 pt-5">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-check-big-icon lucide-square-check-big"><path d="M21 10.656V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12.344"/><path d="m9 11 3 3L22 4"/></svg>
        <span class="font-medium">All Completed Appointments ({{ $completedAppointments->total() }})</span>
      </div>
      <div class="mt-2 overflow-auto">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Date</th>
              <th>Completed</th>
              <th>Pet</th>
              <th>Owner</th>
              <th>Service</th>
              <th>Staff</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($completedAppointments as $appointment)
            <tr>
              <td class="font-medium">{{ \Carbon\Carbon::parse($appointment->date)->format('M j, Y') }}</td>
              <td class="font-medium">{{ \Carbon\Carbon::parse($appointment->end_time)->format('g:i A') }}</td>
              <td>{{ $appointment->pet->name }}</td>
              <td>
                @if($appointment->customer->profile)
                  {{ $appointment->customer->profile->first_name }} {{ $appointment->customer->profile->last_name }}
                @else
                  {{ $appointment->customer->name }}
                @endif
              </td>
              <td>
                {{ $appointment->service->name }}
                @if($appointment->additional_service_ids)
                  @php
                    $additionalIds = explode(',', $appointment->additional_service_ids);
                    $additionalServices = \App\Models\Service::whereIn('id', $additionalIds)->get();
                  @endphp
                  @if($additionalServices->count() > 0)
                    <div class="text-xs text-base-content/60">
                      + {{ $additionalServices->pluck('name')->join(', ') }}
                    </div>
                  @endif
                @endif
              </td>
              <td>
                @if($appointment->staff)
                  @if($appointment->staff->profile)
                    {{ $appointment->staff->profile->first_name }} {{ $appointment->staff->profile->last_name }}
                  @else
                    {{ $appointment->staff->name }}
                  @endif
                @else
                  <span class="text-base-content/60">Unassigned</span>
                @endif
              </td>
              <td>
                <div class="flex items-center gap-1">
                  <button class="btn btn-square btn-ghost btn-xs" onclick="viewAppointment({{ $appointment->id }})">
                    <span class="iconify lucide--eye size-4"></span>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center text-base-content/60 py-4">No completed appointments</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      
      @if($completedAppointments->hasPages())
      <div class="px-5 py-4 border-t border-base-300">
        <div class="flex items-center justify-between">
          <div class="text-sm text-base-content/60">
            Showing {{ $completedAppointments->firstItem() }} to {{ $completedAppointments->lastItem() }} of {{ $completedAppointments->total() }} appointments
          </div>
          <div class="join">
            @if ($completedAppointments->onFirstPage())
              <button class="join-item btn btn-sm" disabled>«</button>
            @else
              <a href="{{ $completedAppointments->previousPageUrl() }}" class="join-item btn btn-sm">«</a>
            @endif

            @foreach ($completedAppointments->getUrlRange(1, $completedAppointments->lastPage()) as $page => $url)
              @if ($page == $completedAppointments->currentPage())
                <button class="join-item btn btn-sm btn-active">{{ $page }}</button>
              @else
                <a href="{{ $url }}" class="join-item btn btn-sm">{{ $page }}</a>
              @endif
            @endforeach

            @if ($completedAppointments->hasMorePages())
              <a href="{{ $completedAppointments->nextPageUrl() }}" class="join-item btn btn-sm">»</a>
            @else
              <button class="join-item btn btn-sm" disabled>»</button>
            @endif
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection

@section('page-js')
<script>
function viewAppointment(appointmentId) {
    window.location.href = `/dashboard/appointment/${appointmentId}`;
}
</script>
@endsection

