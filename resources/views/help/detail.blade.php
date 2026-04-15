@extends('layouts.main')
@section('title', 'Help & Support - ' . ucwords(str_replace('-', ' ', $section)))

@section('page-css')
<style>
  .help-subsection {
    border-left: 3px solid hsl(var(--p));
    padding-left: 1rem;
    margin-bottom: 1.5rem;
  }
  .help-subsection-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: hsl(var(--p));
    margin-bottom: 0.75rem;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Help & Support</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('help') }}">Help & Support</a></li>
      <li class="opacity-80">{{ ucwords(str_replace('-', ' ', $section)) }}</li>
    </ul>
  </div>
</div>

<div class="mt-3">
  @include('layouts.alerts')
  
  <div class="card bg-base-100 shadow">
    <div class="card-body">
      @if($section === 'getting-started')
        @include('help.sections.getting-started')
      @elseif($section === 'dashboard')
        @include('help.sections.dashboard')
      @elseif($section === 'system-settings')
        @include('help.sections.system-settings')
      @elseif($section === 'customers')
        @include('help.sections.customers')
      @elseif($section === 'pets')
        @include('help.sections.pets')
      @elseif($section === 'inventory')
        @include('help.sections.inventory')
      @elseif($section === 'services')
        @include('help.sections.services')
      @elseif($section === 'time-slots')
        @include('help.sections.time-slots')
      @elseif($section === 'appointments')
        @include('help.sections.appointments')
      @elseif($section === 'service-dashboard')
        @include('help.sections.service-dashboard')
      @elseif($section === 'archives')
        @include('help.sections.archives')
      @elseif($section === 'incident-reports')
        @include('help.sections.incident-reports')
      @elseif($section === 'notifications')
        @include('help.sections.notifications')
      @elseif($section === 'support')
        @include('help.sections.support')
      @endif
    </div>
  </div>
</div>
@endsection