@extends('layouts.main')
@section('title', 'Packages')

@section('page-css')
<style>
  .table th,
  .table td {
    padding-block: 0.6rem;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Packages Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('services') }}">Services</a></li>
      <li>Packages</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="card bg-base-100 shadow mt-3">
    <div class="card-body p-0">
      <div class="flex items-center justify-between px-5 pt-5">
        <div class="inline-flex items-center gap-3">
          <label class="input input-sm">
            <span class="iconify lucide--search text-base-content/80 size-3.5"></span>
            <input class="w-24 sm:w-36" placeholder="Search packages" aria-label="Search packages" type="search" onkeydown="handleSearch(event)" value="{{ $search }}"/>
          </label>
        </div>
        <a aria-label="Create package link" class="btn btn-primary btn-sm max-sm:btn-square" href="{{ route('add-package') }}">
          <span class="iconify lucide--plus size-4"></span>
          <span class="hidden sm:inline">New Package</span>
        </a>
      </div>
      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Image</th>
              <th>Name</th>
              <th>Price</th>
              <th style="text-align:center">Days</th>
              <th>Services</th>
              <th style="text-align:center">Status</th>
              <th style="text-align:center">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($packages as $package)
            <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
              <td>{{ $loop->iteration }}</td>
              <td>
                @if ($package->image)
                  <img src="{{ asset('storage/services/' . $package->image) }}" alt="{{ $package->name }}" class="rounded-box bg-base-200 size-10 w-12" />
                @else
                  <img src="{{ asset('images/no_image.jpg') }}" alt="Package Image" class="rounded-box bg-base-200 size-10 w-12">
                @endif
              </td>
              <td>{{ $package->name }}</td>
              <td>${{ number_format($package->price, 2) }}</td>
              <td style="text-align:center">{{ $package->days ?? 'N/A' }}</td>
              <td class="truncate max-w-xs">
                @php
                  $serviceIds = $package->service_ids ? explode(',', $package->service_ids) : [];
                  $services = \App\Models\Service::whereIn('id', $serviceIds)->get();
                @endphp
                @if ($services->count() > 0)
                  {{ $services->pluck('name')->implode(', ') }}
                @else
                  No services
                @endif
              </td>
              <td style="text-align:center">
                @if ($package->status === 'active')
                  <span class="badge badge-soft badge-success badge-sm">Active</span>
                @else
                  <span class="badge badge-soft badge-error badge-sm">Inactive</span>
                @endif
              </td>
              <td style="text-align:center">
                <div class="inline-flex w-fit">
                  <a class="btn btn-square btn-ghost btn-sm" href="{{ route('edit-package', ['id' => $package->id]) }}">
                    <span class="iconify lucide--pencil text-base-content/80 size-4"></span>
                  </a>
                  <button onclick="confirmDelete({{ $package }})" class="btn btn-square btn-error btn-outline btn-sm border-transparent">
                    <span class="iconify lucide--trash size-4"></span>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-8 text-base-content/60">
                No packages found. <a href="{{ route('add-package') }}" class="link link-primary">Create one</a> to get started.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $packages->links('layouts.pagination', ['items' => $packages]) }}
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
      <form id="delete_form" method="POST" action="{{ route('delete-package') }}">
        @csrf
        <input type="hidden" name="package_id" value="" />
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
<script>
  function handleSearch(event) {
    if (event.key === 'Enter') {
      const searchValue = event.target.value;
      const url = `/packages?search=${encodeURIComponent(searchValue)}`;
      window.location.href = url;
    }
  }
  function confirmDelete(package) {
    const message = `You are about to delete the package "${package.name}". Would you like to proceed?`;
    $('#delete_modal_message').text(message);
    $('#delete_form input[name=package_id]').val(package.id);
    delete_modal.showModal();
  }
</script>
@endsection

