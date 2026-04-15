@extends('layouts.main')
@section('title', 'End of Day Report')

@section('page-css')
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">End of Day Report</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('boarding-process-log') }}">Boarding Daily Workflow</a></li>
      <li>End of Day</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="flex flex-wrap items-center gap-3 mb-4">
    <form method="GET" action="{{ route('end-of-day') }}" class="inline-flex items-center gap-2">
      <input type="date" name="date" class="input input-bordered input-sm w-40" value="{{ $date->format('Y-m-d') }}" aria-label="Select date" onchange="this.form.submit()" />
    </form>
    <span class="text-base-content/70 text-sm">{{ $date->format('l, F j, Y') }}</span>
  </div>

  @include('reports.partials.end-of-day-content')

  <div class="card bg-base-100 shadow mt-3">
    <div class="card-body">
      <h4 class="font-semibold text-base border-b border-base-300 pb-2">Add maintenance issue</h4>
      @if (hasPermission(27, 'can_create'))
      <form method="POST" action="{{ route('create-end-of-day-maintenance') }}" class="mt-4 p-3 rounded-lg bg-base-200/50 flex flex-wrap items-end gap-3">
        @csrf
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}" />
        <select name="type" class="select select-bordered select-sm w-32" required>
          <option value="facility">Facility</option>
          <option value="equipment">Equipment</option>
        </select>
        <input type="text" name="category" class="input input-bordered input-sm w-36" placeholder="e.g. HVAC, plumbing, phones" />
        <input type="text" name="description" class="input input-bordered input-sm min-w-[200px]" placeholder="Description" required />
        <button type="submit" class="btn btn-primary btn-sm">Add issue</button>
      </form>
      @endif
    </div>
  </div>
</div>
@endsection

@section('page-js')
@endsection
