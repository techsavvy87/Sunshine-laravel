@extends('layouts.main')
@section('title', 'Create Customer Package')

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
  <h3 class="text-lg font-medium">Create Customer Package</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('customer-packages') }}">Customer Packages</a></li>
      <li class="opacity-80">Create</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form id="create_form" method="POST">
    @csrf
    <div class="card bg-base-100 shadow mt-3">
      <div class="card-body">
        <div class="card-title">Package Information</div>
        <div class="fieldset mt-2 grid grid-cols-1 gap-4 xl:grid-cols-2">
          <div class="space-y-2">
            <label class="fieldset-label" for="customer">Customer*</label>
            <select class="select w-full focus:outline-0" id="customer" name="customer_id" required>
              <option value="" hidden selected>Choose a customer</option>
            </select>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="package">Package*</label>
            <select class="select w-full focus:outline-0" id="package" name="package_id" required>
              <option value="" hidden selected>Choose a package</option>
              @foreach($packages as $package)
                <option value="{{ $package->id }}" data-package='@json($package)'>{{ $package->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div id="package_details" class="mt-4 space-y-2 hidden"></div>
      </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
      <a class="btn btn-sm btn-ghost" href="{{ route('customer-packages') }}">
        <span class="iconify lucide--x size-4"></span>
        Cancel
      </a>
      <button class="btn btn-sm btn-primary" type="button" onclick="saveCustomerPackage()">
        <span class="iconify lucide--check size-4"></span>
        Save
      </button>
    </div>
  </form>
</div>

<dialog id="invoice_payment_modal" class="modal modal-lg">
  <div class="modal-box max-w-5xl">
    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute top-2 right-2">
        <span class="iconify lucide--x size-4"></span>
      </button>
    </form>
    <h3 class="text-lg font-medium mb-4">Invoice & Payment Information</h3>
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2 max-h-[80vh] overflow-y-auto">
      <!-- Invoice Form -->
      <div class="card bg-base-200">
        <div class="card-body p-4">
          <h4 class="font-medium mb-3">Invoice</h4>
          <div class="space-y-3 text-sm">
            <fieldset class="fieldset">
              <legend class="fieldset-legend">Invoice Number*</legend>
              <input type="text" id="modal_invoice_number" class="input input-bordered w-full input-sm" placeholder="Enter invoice number" />
            </fieldset>
            <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
              <fieldset class="fieldset">
                <legend class="fieldset-legend">First Name*</legend>
                <input type="text" id="modal_first_name" class="input input-bordered w-full input-sm" placeholder="Enter first name" />
              </fieldset>
              <fieldset class="fieldset">
                <legend class="fieldset-legend">Last Name*</legend>
                <input type="text" id="modal_last_name" class="input input-bordered w-full input-sm" placeholder="Enter last name" />
              </fieldset>
            </div>
            <fieldset class="fieldset">
              <legend class="fieldset-legend">Email*</legend>
              <input type="text" id="modal_email" class="input input-bordered w-full input-sm" placeholder="Enter email" />
            </fieldset>
            <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
              <fieldset class="fieldset">
                <legend class="fieldset-legend">Issued At*</legend>
                <input type="datetime-local" id="modal_issued_at" class="input w-full input-sm" />
              </fieldset>
              <fieldset class="fieldset">
                <legend class="fieldset-legend">Due Date</legend>
                <input type="date" id="modal_due_date" class="input w-full input-sm" />
              </fieldset>
            </div>
            <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
              <fieldset class="fieldset">
                <legend class="fieldset-legend">Paid At</legend>
                <input type="datetime-local" id="modal_paid_at" class="input w-full input-sm" />
              </fieldset>
              <fieldset class="fieldset">
                <legend class="fieldset-legend">Status</legend>
                <select class="select w-full input-sm" id="modal_status">
                  <option value="draft">Draft</option>
                  <option value="sent" selected>Sent</option>
                  <option value="paid">Paid</option>
                  <option value="void">Void</option>
                </select>
              </fieldset>
            </div>
            <fieldset class="fieldset">
              <legend class="fieldset-legend">Notes</legend>
              <textarea placeholder="Type here" id="modal_invoice_notes" class="textarea w-full textarea-sm"></textarea>
            </fieldset>
          </div>
        </div>
      </div>

      <!-- Payment Information -->
      <div class="card bg-base-200">
        <div class="card-body p-4">
          <h4 class="font-medium mb-3">Payment Information</h4>
          <div class="space-y-3 text-sm">
            <fieldset class="fieldset">
              <legend class="fieldset-legend">Amount*</legend>
              <input type="number" id="modal_payment_amount" class="input input-bordered w-full input-sm" step="0.01" min="0" placeholder="0.00" />
            </fieldset>
            <fieldset class="fieldset">
              <legend class="fieldset-legend">Payment Type*</legend>
              <select id="modal_payment_method" class="select w-full input-sm">
                <option value="">Select payment type</option>
                <option value="cash">Cash</option>
                <option value="check">Check</option>
                <option value="cc">Credit Card</option>
              </select>
            </fieldset>
            <fieldset class="fieldset">
              <legend class="fieldset-legend">Notes</legend>
              <textarea id="modal_payment_notes" class="textarea textarea-bordered w-full textarea-sm" rows="3" placeholder="Enter payment notes..."></textarea>
            </fieldset>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-action">
      <button type="button" class="btn btn-sm" onclick="invoice_payment_modal.close()">Cancel</button>
      <button type="button" id="save_customer_package_invoice_btn" class="btn btn-sm btn-primary" onclick="saveCustomerPackageWithInvoice()">
        <span class="loading loading-spinner loading-sm" style="display: none;"></span>
        Save
      </button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>
@endsection

@section('page-js')
<script src="{{ asset('src/libs/select2/select2.min.js') }}"></script>
<script>
  $(document).ready(function() {
    $('#customer').select2({
      placeholder: "Choose a customer",
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

    window.servicesData = [];
    @if(isset($services))
      @foreach($services as $s)
        window.servicesData.push({
          id: {{ $s->id }},
          name: '{{ addslashes($s->name) }}',
          category_name: '{{ $s->category ? addslashes($s->category->name) : '' }}',
          price_small: {{ $s->price_small !== null ? $s->price_small : 'null' }},
        });
      @endforeach
    @endif

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
      // Get services list
      let servicesList = 'No services';
      if (packageData.service_ids) {
        const serviceIds = packageData.service_ids.split(',').map(id => id.trim()).filter(id => id);
        const serviceNames = [];
        serviceIds.forEach(function(id) {
          const service = window.servicesData.find(function(s) { return String(s.id) === String(id); });
          if (service) {
            serviceNames.push(service.name);
          }
        });
        if (serviceNames.length > 0) {
          servicesList = serviceNames.join(', ');
        } else {
          servicesList = serviceIds.length + ' service(s)';
        }
      }

      let html = `
        <div class="card bg-base-200">
          <div class="card-body p-4">
            <h4 class="font-medium mb-2">Package Details</h4>
            <div class="space-y-2 text-sm">
              <div><strong>Name:</strong> ${packageData.name}</div>
              <div><strong>Price:</strong> $${parseFloat(packageData.price).toFixed(2)}</div>
              <div><strong>Days:</strong> ${packageData.days || 'N/A'}</div>
              <div><strong>Services:</strong> ${servicesList}</div>
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

  function saveCustomerPackage() {
    const customerId = $('#customer').val();
    const packageId = $('#package').val();

    if (!customerId || !packageId) {
      alert('Please select both customer and package.');
      return;
    }

    populateInvoiceModal();
    invoice_payment_modal.showModal();
  }

  function populateInvoiceModal() {
    const customerId = $('#customer').val();
    const packageId = $('#package').val();
    const selectedOption = $('#package option:selected');
    const packageData = selectedOption.data('package');

    // Get customer data
    if (customerId) {
      $.ajax({
        url: '{{ route("get-appointment-customers") }}',
        method: 'GET',
        data: { q: '' },
        success: function(customers) {
          const customer = customers.find(c => String(c.id) === String(customerId));
          if (customer && customer.profile) {
            $('#modal_first_name').val(customer.profile.first_name || '');
            $('#modal_last_name').val(customer.profile.last_name || '');
            $('#modal_email').val(customer.email || '');
          }
        }
      });
    }

    // Generate invoice number
    $.ajax({
      url: '{{ route("generate-invoice-number") }}',
      method: 'GET',
      success: function(response) {
        $('#modal_invoice_number').val(response.invoice_number);
      }
    });

    // Set issued date to now
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    $('#modal_issued_at').val(`${year}-${month}-${day}T${hours}:${minutes}`);

    // Set payment amount
    if (packageData) {
      $('#modal_payment_amount').val(parseFloat(packageData.price || 0).toFixed(2));
    }
  }

  function saveCustomerPackageWithInvoice() {
    const invoiceNumber = $('#modal_invoice_number').val();
    const firstName = $('#modal_first_name').val();
    const lastName = $('#modal_last_name').val();
    const email = $('#modal_email').val();
    const issuedAt = $('#modal_issued_at').val();
    const dueDate = $('#modal_due_date').val();
    const paidAt = $('#modal_paid_at').val();
    const status = $('#modal_status').val();
    const invoiceNotes = $('#modal_invoice_notes').val();
    const paymentAmount = $('#modal_payment_amount').val();
    const paymentMethod = $('#modal_payment_method').val();
    const paymentNotes = $('#modal_payment_notes').val();

    // Validation
    if (!invoiceNumber || !firstName || !lastName || !email || !issuedAt) {
      alert('Please fill in all required invoice fields.');
      return;
    }

    if (!paymentAmount || parseFloat(paymentAmount) <= 0) {
      alert('Please enter a valid payment amount (greater than 0).');
      return;
    }

    if (!paymentMethod || paymentMethod === '') {
      alert('Please select a payment type.');
      return;
    }

    const customerId = $('#customer').val();
    const packageId = $('#package').val();
    const selectedOption = $('#package option:selected');
    const packageData = selectedOption.data('package');

    if (!customerId || !packageId) {
      alert('Please select both customer and package.');
      return;
    }

    // Show loading
    const saveBtn = $('#save_customer_package_invoice_btn');
    const spinner = saveBtn.find('.loading');
    spinner.show();
    saveBtn.prop('disabled', true);

    // Prepare form data
    const formData = {
      _token: '{{ csrf_token() }}',
      customer_id: customerId,
      package_id: packageId,
      invoice_number: invoiceNumber,
      first_name: firstName,
      last_name: lastName,
      email: email,
      issued_at: issuedAt,
      due_date: dueDate || null,
      paid_at: paidAt || null,
      status: status,
      notes: invoiceNotes || null,
      payment_amount: paymentAmount,
      payment_method: paymentMethod,
      payment_notes: paymentNotes || null,
    };

    // Submit via AJAX
    $.ajax({
      url: '{{ route("create-customer-package") }}',
      method: 'POST',
      data: formData,
      success: function(response) {
        window.location.href = '{{ route("customer-packages") }}';
      },
      error: function(xhr) {
        spinner.hide();
        saveBtn.prop('disabled', false);
        let errorMessage = 'An error occurred while saving.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
          const errors = Object.values(xhr.responseJSON.errors).flat();
          errorMessage = errors.join('\n');
        }
        alert(errorMessage);
      }
    });
  }
</script>
@endsection

