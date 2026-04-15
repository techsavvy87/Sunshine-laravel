@extends('layouts.main')
@section('title', 'Update Customer Package')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('src/libs/select2/select2.min.css') }}" />
  <style>
    .select2-container {
      width: 100% !important;
    }
  </style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Update Customer Package</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('customer-packages') }}">Customer Packages</a></li>
      <li class="opacity-80">Update</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('update-customer-package') }}" method="POST" id="update_form">
    @csrf
    <input type="hidden" name="customer_package_id" value="{{ $customerPackage->id }}">
    <div class="card bg-base-100 shadow mt-3">
      <div class="card-body">
        <div class="card-title">Package Information</div>
        <div class="fieldset mt-2 grid grid-cols-1 gap-4 xl:grid-cols-2">
          <div class="space-y-2">
            <label class="fieldset-label" for="customer">Customer*</label>
            <select class="select w-full focus:outline-0" id="customer" name="customer_id" required>
              <option value="" hidden>Choose a customer</option>
            </select>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="package">Package*</label>
            <select class="select w-full focus:outline-0" id="package" name="package_id" required disabled>
              <option value="" hidden>Choose a package</option>
              @foreach($packages as $package)
                <option value="{{ $package->id }}" data-package='@json($package)' {{ $customerPackage->package_id == $package->id ? 'selected' : '' }}>
                  {{ $package->name }} - ${{ number_format($package->price, 2) }}
                </option>
              @endforeach
            </select>
            <input type="hidden" name="package_id" value="{{ $customerPackage->package_id }}" />
          </div>
        </div>
        <div class="fieldset mt-2 grid grid-cols-1 gap-4 xl:grid-cols-2">
          <div class="space-y-2">
            <label class="fieldset-label" for="original_days">Original Days*</label>
            <input type="number" id="original_days" name="original_days" class="input w-full" value="{{ $customerPackage->original_days }}" min="0" required disabled />
            <input type="hidden" name="original_days" value="{{ $customerPackage->original_days }}" />
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="remaining_days">Remaining Days*</label>
            <input type="number" id="remaining_days" name="remaining_days" class="input w-full" value="{{ $customerPackage->remaining_days }}" min="0" required />
          </div>
        </div>
        <div id="package_details" class="mt-4 space-y-2"></div>
      </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
      <a class="btn btn-sm btn-ghost" href="{{ route('customer-packages') }}">
        <span class="iconify lucide--x size-4"></span>
        Cancel
      </a>
      <button class="btn btn-sm btn-primary" type="submit">
        <span class="iconify lucide--check size-4"></span>
        Save
      </button>
    </div>
  </form>
</div>
@endsection

@section('page-js')
<script src="{{ asset('src/libs/select2/select2.min.js') }}"></script>
<script>
  $(document).ready(function() {
    // Initialize customer select2
    @if($customerPackage->customer && $customerPackage->customer->profile)
    const initialCustomer = {
      id: {{ $customerPackage->customer_id }},
      first_name: '{{ addslashes($customerPackage->customer->profile->first_name) }}',
      last_name: '{{ addslashes($customerPackage->customer->profile->last_name) }}',
      email: '{{ addslashes($customerPackage->customer->email) }}',
      phone_number: '{{ addslashes($customerPackage->customer->profile->phone_number_1 ?? '') }}'
    };
    @else
    const initialCustomer = null;
    @endif

    $('#customer').select2({
      placeholder: "Choose a customer",
      data: initialCustomer ? [initialCustomer] : [],
      ajax: {
        url: '{{ route("get-appointment-customers") }}',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term
          };
        },
        processResults: function (data) {
          return {
            results: data.map(function (customer) {
              return {
                id: customer.id,
                first_name: customer.profile.first_name,
                last_name: customer.profile.last_name,
                email: customer.email,
                phone_number: customer.profile.phone_number_1
              };
            })
          };
        }
      },
      templateResult: function (customer) {
        if (!customer.id) {
          return customer.text;
        }
        var $container = $(`
          <div class="flex items-center gap-2">
            <span class="font-medium">${customer.first_name} ${customer.last_name}</span>
            <span class="text-sm text-base-content/70">(${customer.email} | ${customer.phone_number})</span>
          </div>
        `);
        return $container;
      },
      templateSelection: function (customer) {
        if (!customer.id) {
          return customer.text;
        }
        var $container = $(`
          <div class="flex items-center gap-2">
            <span class="font-medium">${customer.first_name} ${customer.last_name}</span>
            <span class="text-sm text-base-content/70">(${customer.email} | ${customer.phone_number})</span>
          </div>
        `);
        return $container;
      }
    });

    // Set initial customer value
    if (initialCustomer) {
      $('#customer').val(initialCustomer.id).trigger('change');
    }

    $('#package').on('change', function() {
      renderPackageDetails();
    });

    renderPackageDetails();
  });

  function renderPackageDetails() {
    const packageId = $('#package').val();
    const packageDetails = $('#package_details');
    
    if (!packageId) {
      packageDetails.addClass('hidden');
      return;
    }

    const selectedOption = $('#package option:selected');
    const packageData = selectedOption.data('package');
    
    if (packageData) {
      let html = `
        <div class="card bg-base-200">
          <div class="card-body p-4">
            <h4 class="font-medium mb-2">Package Details</h4>
            <div class="space-y-2 text-sm">
              <div><strong>Name:</strong> ${packageData.name}</div>
              <div><strong>Price:</strong> $${parseFloat(packageData.price).toFixed(2)}</div>
              <div><strong>Days:</strong> ${packageData.days || 'N/A'}</div>
              ${packageData.description ? `<div><strong>Description:</strong> ${packageData.description}</div>` : ''}
            </div>
          </div>
        </div>
      `;
      packageDetails.html(html).removeClass('hidden');
    } else {
      packageDetails.addClass('hidden');
    }
  }
</script>
@endsection

