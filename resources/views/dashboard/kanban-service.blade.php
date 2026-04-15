@extends('layouts.main')
@section('title', 'Service Dashboard')

@section('page-css')
<style>
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <div class="inline-flex items-center gap-3">
    <h3 class="text-lg font-medium">{{ $service->name }} Dashboard</h3>
    @if($service->name === 'Groom')
      <a class="btn btn-primary btn-sm max-sm:btn-square" href="{{ route('groomer-calendar', $id) }}">
        <span class="iconify lucide--calendar-days size-4"></span>
        <span class="hidden sm:inline">View Calendar</span>
      </a>
    @endif
  </div>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Service Dashboard</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @if($infoMessage)
    <div class="alert alert-soft alert-warning" role="alert">
      <span class="iconify lucide--info size-4"></span>
      <span>{{ $infoMessage }}</span>
      <button class="btn btn-ghost" style="height: 1px; padding: 0px"><span class="iconify lucide--x size-4"></span></button>
  </div>
  @endif
  <div class="card bg-base-100 shadow mt-3">
    <div class="card-body p-4">
      <form id="search_form" class="w-full mt-3" method="GET" action="{{ route('service-dashboard', $id) }}">
        <div class="grow grid grid-cols-1 gap-2 xl:grid-cols-5">
          <input type="text" class="input input-sm w-full" placeholder="Customer/Pet" name="customer" value="{{ $customerPet }}"/>
          <select class="select select-sm w-full" name="staff" value="{{ $staffId }}">
            <option value="" hidden selected>Choose Staff</option>
            <option value="0">All Staffs</option>
            @foreach($staffs as $staff)
            <option value="{{ $staff->id }}" {{ $staffId == $staff->id ? 'selected' : '' }}>{{ $staff->profile ? $staff->profile->first_name . " " . $staff->profile->last_name : '' }}</option>
            @endforeach
          </select>
          <div class="grid grid-cols-1 gap-2 xl:grid-cols-2">
            <button type="submit" class="btn btn-soft btn-primary btn-sm max-sm:btn-square">
              <span class="iconify lucide--search size-4"></span>
              <span class="hidden sm:inline">Search</span>
            </button>
          </div>
          <div class="flex justify-end xl:col-span-2 gap-2">
            <a href="{{ route('list-incident-reports', ['serviceId' => $id]) }}" class="btn btn-outline btn-warning btn-sm max-sm:btn-square">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-scroll-text-icon lucide-scroll-text"><path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/></svg>
              <span class="hidden sm:inline">Incident Reports</span>
            </a>
            <a class="btn btn-primary btn-sm max-sm:btn-square" href="{{ route('list-dashboard', $id) }}">
              <span class="iconify lucide--list size-4"></span>
              <span class="hidden sm:inline">View List</span>
            </a>
          </div>
        </div>
      </form>
      <div class="mt-8 overflow-auto">
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-5">
          @php
            $isBoardingOrDaycare = $service->category && (str_contains(strtolower($service->category->name), 'boarding') || str_contains(strtolower($service->category->name), 'daycare'));
            $statuses = [
              'checked_in' => 'Scheduled',
              'in_progress' => $isBoardingOrDaycare ? 'On Property' : 'In Progress',
              'completed' => 'Completed',
              'issue' => 'Issue',
            ];

            $statusColors = [
              'checked_in' => '#e0e7ff',   // light indigo (or pick your preferred color)
              'in_progress' => '#ede9fe',   // light purple
              'completed' => '#bbf7d0',   // light green
              'issue' => '#fecaca',        // light red
            ];
          @endphp
          @foreach($statuses as $statusKey => $statusLabel)
            <div class="card shadow" style="background-color: {{ $statusColors[$statusKey] ?? '#f3f4f6' }};">
              <div class="card-body p-2">
                <div class="flex items-center justify-between">
                  <span></span>
                  <h4 class="font-semibold text-center mb-2" style="color: black">{{ $statusLabel }}</h4>
                  @if ($statusKey == 'checked_in')
                  <a class="btn btn-square btn-ghost btn-xs mb-2" href="{{ route('add-appointment', ['service_id' => $id]) }}" title="Add Appointment">
                    <span class="iconify lucide--plus size-3 font-medium"></span>
                  </a>
                  @else
                  <span></span>
                  @endif
                </div>
                <div class="space-y-2">
                  @forelse($appointments->where('status', $statusKey) as $appointment)
                    <div class="card bg-base-100 shadow-sm p-2 relative" style="cursor: pointer;" onclick="window.location='{{ route('appointment-dashboard', $appointment->id) }}'">
                      <div class="flex items-center space-x-3">
                        <img src="{{ empty($appointment->pet->pet_img) ? asset('images/no_image.jpg') : asset('storage/pets/'. $appointment->pet->pet_img) }}" alt="Pet Image" class="mask mask-squircle bg-base-200 size-14" style="object-fit: cover;">
                        <div>
                          <p class="font-medium">
                            <span>{{ $appointment->pet->name }}</span>
                            @if ($appointment->pet->rating === 'green')
                              <i class="fa-solid fa-star" style="color: lightseagreen; font-size: 14px"></i>
                            @elseif ($appointment->pet->rating === 'yellow')
                              <i class="fa-solid fa-star" style="color: gold; font-size: 14px"></i>
                            @elseif ($appointment->pet->rating === 'red')
                              <i class="fa-solid fa-star" style="color: tomato; font-size: 14px"></i>
                            @endif
                          </p>
                          <p class="text-xs text-base-content/60">
                            <span class="text-base-content/80">Customer: </span>
                            {{ $appointment->customer->profile ? $appointment->customer->profile->first_name . " " . $appointment->customer->profile->last_name : $appointment->customer->name }}
                          </p>
                          <p class="text-xs text-base-content/60">
                            <span class="text-base-content/80">Staff: </span>
                            {{ $appointment->staff_id ? ($appointment->staff->profile ? $appointment->staff->profile->first_name . " " . $appointment->staff->profile->last_name : $appointment->staff->name) : 'Unassigned' }}
                          </p>
                          @if($appointment->end_time)
                          <p class="text-xs text-base-content/60">
                            <span class="text-base-content/80">Pickup: </span>
                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $appointment->end_time)->format('h:i A') }}
                          </p>
                          @endif
                          @if($statusKey === 'issue')
                            <div class="absolute top-2 right-2">
                              <span class="iconify lucide--triangle-alert size-4 text-red-600"></span>
                            </div>
                          @endif
                        </div>
                      </div>
                    </div>
                  @empty
                    <p class="text-xs text-center text-base-content/40">No records</p>
                  @endforelse
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-js')
@endsection