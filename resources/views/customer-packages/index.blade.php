@extends('layouts.main')
@section('title', 'Customer Packages')

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
  <h3 class="text-lg font-medium">Customer Packages Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('services') }}">Services</a></li>
      <li>Customer Packages</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="card bg-base-100 shadow mt-3">
    <div class="card-body p-0">
      <div class="flex items-center justify-between px-5 pt-5">
        <form method="GET" action="{{ route('customer-packages') }}" class="inline-flex items-center gap-3 flex-1">
          <label class="input input-sm">
            <span class="iconify lucide--search text-base-content/80 size-3.5"></span>
            <input class="w-24 sm:w-36" placeholder="Search customer" aria-label="Search customer" type="search" name="search" value="{{ $search }}"/>
          </label>
          <select class="select select-sm w-40" name="package_id" onchange="this.form.submit()">
            <option value="">All Packages</option>
            @foreach($packages as $package)
              <option value="{{ $package->id }}" {{ ($packageId ?? '') == $package->id ? 'selected' : '' }}>{{ $package->name }}</option>
            @endforeach
          </select>
        </form>
        <a aria-label="Create customer package link" class="btn btn-primary btn-sm max-sm:btn-square" href="{{ route('add-customer-package') }}">
          <span class="iconify lucide--plus size-4"></span>
          <span class="hidden sm:inline">New</span>
        </a>
      </div>
      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Customer</th>
              <th>Package</th>
              <th style="text-align:center">Original Days</th>
              <th style="text-align:center">Remaining Days</th>
              <th style="text-align:center">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($customerPackages as $customerPackage)
            <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
              <td>{{ $loop->iteration }}</td>
              <td>
                @if($customerPackage->customer && $customerPackage->customer->profile)
                  <div class="flex items-center space-x-3 truncate">
                    @if (empty($customerPackage->customer->profile->avatar_img))
                    <img src="{{ asset('images/default-user-avatar.png') }}" alt="Customer Avatar" class="mask mask-squircle bg-base-200 size-10">
                    @else
                    <img src="{{ asset('storage/profiles/'. $customerPackage->customer->profile->avatar_img) }}" alt="Customer Avatar" class="mask mask-squircle bg-base-200 size-10">
                    @endif
                    <div>
                      <p class="font-medium">{{ $customerPackage->customer->profile->first_name }} {{ $customerPackage->customer->profile->last_name }}</p>
                      <p class="text-xs text-base-content/60">{{ $customerPackage->customer->email }}</p>
                    </div>
                  </div>
                @else
                  N/A
                @endif
              </td>
              <td>{{ $customerPackage->package ? $customerPackage->package->name : 'N/A' }}</td>
              <td style="text-align:center">{{ $customerPackage->original_days }}</td>
              <td style="text-align:center">
                <span class="badge badge-soft {{ $customerPackage->remaining_days > 0 ? 'badge-success' : 'badge-error' }} badge-sm">
                  {{ $customerPackage->remaining_days }}
                </span>
              </td>
              <td style="text-align:center">
                <div class="inline-flex w-fit">
                  <a class="btn btn-square btn-ghost btn-sm" href="{{ route('edit-customer-package', ['id' => $customerPackage->id]) }}">
                    <span class="iconify lucide--pencil text-base-content/80 size-4"></span>
                  </a>
                  <button onclick="confirmDelete({{ $customerPackage }})" class="btn btn-square btn-error btn-outline btn-sm border-transparent">
                    <span class="iconify lucide--trash size-4"></span>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center py-8 text-base-content/60">
                No customer packages found. <a href="{{ route('add-customer-package') }}" class="link link-primary">Create one</a> to get started.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $customerPackages->links('layouts.pagination', ['items' => $customerPackages]) }}
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
      <form id="delete_form" method="POST" action="{{ route('delete-customer-package') }}">
        @csrf
        <input type="hidden" name="customer_package_id" value="" />
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
  function confirmDelete(customerPackage) {
    const customerName = customerPackage.customer && customerPackage.customer.profile
      ? `${customerPackage.customer.profile.first_name} ${customerPackage.customer.profile.last_name}`
      : 'this customer';
    const packageName = customerPackage.package ? customerPackage.package.name : 'this package';
    const message = `You are about to delete the customer package for "${customerName}" - "${packageName}". Would you like to proceed?`;
    $('#delete_modal_message').text(message);
    $('#delete_form input[name=customer_package_id]').val(customerPackage.id);
    delete_modal.showModal();
  }
</script>
@endsection

