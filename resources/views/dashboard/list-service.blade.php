@extends('layouts.main')
@section('title', 'Appointments Dashboard')

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">{{ $service->name }} Dashboard</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Service Dashboard</li>
    </ul>
  </div>
</div>

<div class="mt-4 space-y-6">
  @if($infoMessage)
  <div class="alert alert-soft alert-warning" role="alert">
    <span class="iconify lucide--info size-4"></span>
    <span>{{ $infoMessage }}</span>
    <button class="btn btn-ghost" style="height: 1px; padding: 0px"><span class="iconify lucide--x size-4"></span></button>
  </div>
  @endif
  @php
    $isBoardingOrDaycare = $service->category && (str_contains(strtolower($service->category->name), 'boarding') || str_contains(strtolower($service->category->name), 'daycare'));
    $inProgressLabel = $isBoardingOrDaycare ? 'On Property' : 'In Progress';
  @endphp
  <!-- Scheduled Appointments (formerly Checked In) -->
  <div class="card bg-base-100 shadow">
    <div class="card-body p-0">
      <div class="flex items-center justify-between px-5 pt-5">
        <div class="flex items-center gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big-icon lucide-circle-check-big"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
          <span class="font-medium">Scheduled ({{ $checkedInAppointments->count() }})</span>
        </div>
        <div class="flex gap-2">
          <a href="{{ route('list-incident-reports', ['serviceId' => $id]) }}" class="btn btn-outline btn-warning btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-scroll-text-icon lucide-scroll-text"><path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5 a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/></svg>
            <span class="hidden sm:inline">Incident Reports</span>
          </a>
          <a class="btn btn-primary btn-sm" href="{{ route('service-dashboard', $id) }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-kanban-icon lucide-square-kanban"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M8 7v7"/><path d="M12 7v4"/><path d="M16 7v9"/></svg>
            <span class="hidden sm:inline">View Board</span>
          </a>
          <a href="{{ route('add-appointment', ['service_id' => $id]) }}" class="btn btn-outline btn-primary btn-sm">
            <span class="iconify lucide--plus size-3"></span>
            <span class="hidden sm:inline">New</span>
          </a>
        </div>
      </div>
      <div class="mt-2 overflow-auto">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Date</th>
              <th>Time</th>
              <th>Pet</th>
              <th>Owner</th>
              <th>Additional Services</th>
              <th>Staff</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($checkedInAppointments as $appointment)
            <tr>
              <td class="font-medium">{{ \Carbon\Carbon::parse($appointment->date)->format('M j, Y') }}</td>
              <td class="font-medium">{{ \Carbon\Carbon::parse($appointment->start_time)->format('g:i A') }}</td>
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
              <td>
                @if($appointment->customer->profile)
                  {{ $appointment->customer->profile->first_name }} {{ $appointment->customer->profile->last_name }}
                @else
                  {{ $appointment->customer->name }}
                @endif
              </td>
              <td>
                @if($appointment->additional_service_ids)
                  @php
                    $additionalIds = explode(',', $appointment->additional_service_ids);
                    $additionalServices = \App\Models\Service::whereIn('id', $additionalIds)->get();
                  @endphp
                  @if($additionalServices->count() > 0)
                    {{ $additionalServices->pluck('name')->join(', ') }}
                  @else
                    <span class="text-base-content/60">-</span>
                  @endif
                @else
                  <span class="text-base-content/60">-</span>
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
              <td colspan="9" class="text-center text-base-content/60 py-4">No scheduled appointments</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- In Progress / On Property Appointments -->
  <div class="card bg-base-100 shadow">
    <div class="card-body p-0">
      <div class="flex items-center gap-3 px-5 pt-5">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-activity-icon lucide-activity"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/></svg>
        <span class="font-medium">{{ $inProgressLabel }} ({{ $inProgressAppointments->count() }})</span>
      </div>
      <div class="mt-2 overflow-auto">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Date</th>
              <th>Started</th>
              <th>Pet</th>
              <th>Owner</th>
              <th>Additional Services</th>
              <th>Staff</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($inProgressAppointments as $appointment)
            <tr>
              <td class="font-medium">{{ \Carbon\Carbon::parse($appointment->date)->format('M j, Y') }}</td>
              <td class="font-medium">{{ \Carbon\Carbon::parse($appointment->start_time)->format('g:i A') }}</td>
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
              <td>
                @if($appointment->customer->profile)
                  {{ $appointment->customer->profile->first_name }} {{ $appointment->customer->profile->last_name }}
                @else
                  {{ $appointment->customer->name }}
                @endif
              </td>
              <td>
                @if($appointment->additional_service_ids)
                  @php
                    $additionalIds = explode(',', $appointment->additional_service_ids);
                    $additionalServices = \App\Models\Service::whereIn('id', $additionalIds)->get();
                  @endphp
                  @if($additionalServices->count() > 0)
                    {{ $additionalServices->pluck('name')->join(', ') }}
                  @else
                    <span class="text-base-content/60">-</span>
                  @endif
                @else
                  <span class="text-base-content/60">-</span>
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
              <td colspan="9" class="text-center text-base-content/60 py-4">No {{ strtolower($inProgressLabel) }} appointments</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Completed Appointments -->
  <div class="card bg-base-100 shadow">
    <div class="card-body p-0">
      <div class="flex items-center justify-between px-5 pt-5">
        <div class="flex items-center gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-check-big-icon lucide-square-check-big"><path d="M21 10.656V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12.344"/><path d="m9 11 3 3L22 4"/></svg>
          <span class="font-medium">Completed ({{ $completedCount }})</span>
        </div>
        @if($completedCount > 5)
          <a href="{{ route('completed-appointments') }}" class="text-sm text-primary hover:underline">
            See more...
          </a>
        @endif
      </div>
      <div class="mt-2 overflow-auto">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Date</th>
              <th>Completed</th>
              <th>Pet</th>
              <th>Owner</th>
              <th>Additional Services</th>
              <th>Staff</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($completedAppointments as $appointment)
            <tr>
              <td class="font-medium">{{ \Carbon\Carbon::parse($appointment->date)->format('M j, Y') }}</td>
              <td class="font-medium">{{ \Carbon\Carbon::parse($appointment->end_time)->format('g:i A') }}</td>
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
              <td>
                @if($appointment->customer->profile)
                  {{ $appointment->customer->profile->first_name }} {{ $appointment->customer->profile->last_name }}
                @else
                  {{ $appointment->customer->name }}
                @endif
              </td>
              <td>
                @if($appointment->additional_service_ids)
                  @php
                    $additionalIds = explode(',', $appointment->additional_service_ids);
                    $additionalServices = \App\Models\Service::whereIn('id', $additionalIds)->get();
                  @endphp
                  @if($additionalServices->count() > 0)
                    {{ $additionalServices->pluck('name')->join(', ') }}
                  @else
                    <span class="text-base-content/60">-</span>
                  @endif
                @else
                  <span class="text-base-content/60">-</span>
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
              <td colspan="8" class="text-center text-base-content/60 py-4">No completed appointments</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card bg-base-100 shadow">
    <div class="card-body p-0">
      <div class="flex items-center gap-3 px-5 pt-5">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
        <span class="font-medium">Issues/Concerns ({{ $issuedAppointments->count() }})</span>
      </div>
      <div class="mt-2 overflow-auto">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Date</th>
              <th>Time</th>
              <th>Pet</th>
              <th>Owner</th>
              <th>Staff</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($issuedAppointments as $appointment)
            <tr>
              <td class="font-medium">{{ \Carbon\Carbon::parse($appointment->date)->format('M j, Y') }}</td>
              <td class="font-medium">{{ \Carbon\Carbon::parse($appointment->start_time)->format('g:i A') }}</td>
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
              <td>
                @if($appointment->customer->profile)
                  {{ $appointment->customer->profile->first_name }} {{ $appointment->customer->profile->last_name }}
                @else
                  {{ $appointment->customer->name }}
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
                <span class="badge badge-error badge-sm">Issue Reported</span>
              </td>
              <td>
                <div class="flex gap-1">
                  <button class="btn btn-square btn-ghost btn-xs" onclick="viewAppointment({{ $appointment->id }})">
                    <span class="iconify lucide--eye size-4"></span>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center text-base-content/60 py-4">No appointments with issues</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
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
