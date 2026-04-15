@extends('layouts.main')
@section('title', 'Credit Types')

@section('page-css')
<style>
  th, td {
    text-align: center;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Credit Types Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Credit Types</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="mt-3 grid grid-cols-1 gap-4 xl:grid-cols-5 2xl:grid-cols-10">
    <div class="xl:col-span-2 2xl:col-span-3">
      <div class="card bg-base-100 card-border">
        <div class="card-body">
          <div class="card-title" id="credit_form_title">@if (hasPermission(10, 'can_create')) Add Credit Type @else View Credit Type @endif</div>
          <input type="hidden" id="credit_id" value="">
          <div class="fieldset">
            <div class="space-y-1 mt-2">
              <label class="fieldset-label" for="credit_name">Name*</label>
              <input class="input w-full" id="credit_name" type="text" placeholder="" />
            </div>
            <div class="space-y-1 mt-2">
              <label class="fieldset-label" for="credit_num">Num*</label>
              <input class="input w-full" id="credit_num" type="text" placeholder="" oninput="this.value = this.value.replace(/[^0-9]/g, '')"/>
            </div>
            <div class="mt-2 flex items-center gap-6">
              <div class="space-y-1">
                <label class="fieldset-label" for="credit_card_cost">Credit Card Cost($)*</label>
                <input
                  class="input w-full"
                  id="credit_card_cost"
                  type="text"
                  placeholder="e.g. 100"
                  oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                />
              </div>
              <div class="space-y-1">
                <label class="fieldset-label" for="cash_cost">Cash Cost($)*</label>
                <input
                  class="input w-full"
                  id="cash_cost"
                  type="text"
                  placeholder="e.g. 100"
                  oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                />
              </div>
            </div>
            <div class="mt-2 flex items-center gap-6">
              <div class="space-y-1">
                <label class="fieldset-label" for="expiration_days">Expiration Days*</label>
                <input
                  class="input w-full"
                  id="expiration_days"
                  type="text"
                  placeholder="e.g. 5"
                  oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                />
              </div>
              <div class="mt-4">
                <input
                  class="checkbox checkbox-sm"
                  id="no_expiration"
                  type="checkbox"
                  name="no_expiration"
                  onchange="handleNoExpiration(this)"
                />
                <label for="no_expiration" class="text-sm">
                  No Expiration
                </label>
              </div>
            </div>
            <div class="space-y-1 mt-2">
              <label class="fieldset-label" for="multiple_discount">Multiple Discount %*</label>
              <input class="input w-full" id="multiple_discount" type="text" placeholder="" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
            </div>
          </div>
          <div class="mt-5 flex justify-end gap-3">
            @if (hasPermission(10, 'can_create') || hasPermission(10, 'can_update'))
            <button class="btn btn-ghost btn-sm" id="cancel_btn" onclick="cancelCreditType()">Cancel</button>
            <button class="btn btn-sm btn-primary gap-1" onclick="saveCreditType()" id="save_btn">
              <span class="loading loading-spinner size-3.5" style="display:none;"></span>
              Save
            </button>
            @endif
          </div>
        </div>
      </div>
    </div>
    <div class="xl:col-span-3 2xl:col-span-7">
      <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
          <div class="flex items-center justify-between px-5 pt-5">
            <div class="inline-flex items-center gap-3">
              <label class="input input-sm">
                <span class="iconify lucide--search text-base-content/80 size-3.5"></span>
                <input class="w-24 sm:w-36" placeholder="Search credit types" aria-label="Search credit types" type="search" onkeydown="handleSearch(event)"/>
              </label>
            </div>
          </div>
          <div class="mt-4">
            <table class="table">
              <thead>
                <tr>
                  <th style="text-align: left">Name</th>
                  <th>Num <br>Credits</th>
                  <th>Credit Card <br>Cost</th>
                  <th>Cash <br>Cost</th>
                  <th>Expiration Days</th>
                  <th>Multiple <br>Discount %</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="credittype_list">
                @foreach ($creditTypes as $creditType)
                <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
                  <td style="text-align: left">{{ $creditType->name }}</td>
                  <td>{{ $creditType->num_credits }}</td>
                  <td>${{ $creditType->credit_card_cost }}</td>
                  <td>${{ $creditType->cash_cost }}</td>
                  <td>{{ $creditType->expiration_days ?? 'No Expiration' }}</td>
                  <td>{{ $creditType->multiple_discount }}</td>
                  <td>
                    <div class="inline-flex w-fit">
                      @if (hasPermission(10, 'can_update'))
                      <button class="btn btn-square btn-primary btn-outline btn-xs border-transparent" onclick="editCreditType({{ $creditType }})">
                        <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
                      </button>
                      @endif
                      @if (hasPermission(10, 'can_delete'))
                      <button class="btn btn-square btn-error btn-outline btn-xs border-transparent" onclick="openDeleteModal({{ $creditType->id }})">
                        <span class="iconify lucide--trash" style="font-size: 0.875rem;"></span>
                      </button>
                      @endif
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<dialog id="delete_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium">
      Delete Credit Type
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4">
      You are about to delete this credit type. Would you like to proceed further?
    </p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <input type="hidden" id="delete_id" value="" />
      <button class="btn btn-error" onclick="deleteCreditType()">Delete</button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>
@endsection

@section('page-js')
<script>
  function handleNoExpiration(ele) {
    $('#expiration_days').prop('disabled', ele.checked);
    if (ele.checked) $('#expiration_days').val('');
  }

  function saveCreditType() {
    const creditId = $('#credit_id').val();
    const name = $('#credit_name').val();
    const num = $('#credit_num').val();
    const cardCost = $('#credit_card_cost').val();
    const cashCost = $('#cash_cost').val();
    const expirationDays = $('#expiration_days').val();
    const multipleDiscount = $('#multiple_discount').val();
    const noExpiration = $('#no_expiration').is(':checked');

    if (!name || !num || !cardCost || !cashCost || !multipleDiscount || (!noExpiration && !expirationDays)) {
      $('#alert_message').text('Please fill in all required fields.');
      alert_modal.showModal();
      return;
    }

    const data = {
      credit_id: creditId,
      name,
      num,
      credit_card_cost: cardCost,
      cash_cost: cashCost,
      expiration_days: noExpiration ? null : expirationDays,
      multiple_discount: multipleDiscount,
    };

    // show loading spinner in the 'save' button and diasable buttons
    $('#save_btn .loading').css('display', 'inline-block');
    $('#save_btn').prop('disabled', true);
    // Remove the original 'Save' text from the save button
    $('#save_btn').contents().filter(function() {
      return this.nodeType === 3 && this.nodeValue.trim() === 'Save';
    }).remove();
    // Add 'Loading' text to the save button
    $('#save_btn').append('Loading');
    $('#cancel_btn').prop('disabled', true);

    $.ajax({
      url: creditId ? '{{ route("update-credit-type") }}' : '{{ route("create-credit-type") }}',
      method: 'POST',
      data: data,
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      success: function(response) {
        $('#success_message').text(response.message);
        success_modal.showModal();

        $('#save_btn .loading').css('display', 'none');
        $('#save_btn').prop('disabled', false);
        // Remove 'Loading' text if present
        $('#save_btn').contents().filter(function() {
          return this.nodeType === 3 && this.nodeValue.trim() === 'Loading';
        }).remove();
        // Add 'Save' text if not present
        if ($('#save_btn').text().trim() === '') {
          $('#save_btn').append('Save');
        }
        $('#cancel_btn').prop('disabled', false);

        // Reset the form fields
        resetForm();

        // update the credit types list
        updateCreditTypes(response.result);

      },
      error: function(xhr) {
        let msg = 'An error occurred. Please try again.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        $('#alert_message').text(msg);
        alert_modal.showModal();

        $('#save_btn .loading').css('display', 'none');
        $('#save_btn').prop('disabled', false);
        // Remove 'Loading' text if present
        $('#save_btn').contents().filter(function() {
          return this.nodeType === 3 && this.nodeValue.trim() === 'Loading';
        }).remove();
        // Add 'Save' text if not present
        if ($('#save_btn').text().trim() === '') {
          $('#save_btn').append('Save');
        }
        $('#cancel_btn').prop('disabled', false);
      }
    });
  }

  function resetForm() {
    $('#credit_id').val('');
    $('#credit_name').val('');
    $('#credit_num').val('');
    $('#credit_card_cost').val('');
    $('#cash_cost').val('');
    $('#expiration_days').val('').prop('disabled', false);
    $('#no_expiration').prop('checked', false);
    $('#multiple_discount').val('');

    $('#credit_form_title').text('Add Credit Type');
  }

  function updateCreditTypes(result) {
    const creditTypeList = $('#credittype_list');
    creditTypeList.empty();

    $.each(result, function(index, creditType) {
      const expirationDays = creditType.expiration_days ? creditType.expiration_days : 'No Expiration';
      const row = `
        <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
          <td style="text-align: left">${creditType.name}</td>
          <td>${creditType.num_credits}</td>
          <td>$${creditType.credit_card_cost}</td>
          <td>$${creditType.cash_cost}</td>
          <td>${expirationDays}</td>
          <td>${(parseFloat(creditType.multiple_discount) || 0).toFixed(2)}</td>
          <td>
            <div class="inline-flex w-fit">
              <button class="btn btn-square btn-primary btn-outline btn-xs border-transparent" onclick='editCreditType(${JSON.stringify(creditType)})'>
                <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
              </button>
              <button class="btn btn-square btn-error btn-outline btn-xs border-transparent" onclick='openDeleteModal(${creditType.id})'>
                <span class="iconify lucide--trash" style="font-size: 0.875rem;"></span>
              </button>
            </div>
          </td>
        </tr>`;
      creditTypeList.append(row);
    });
  }

  function cancelCreditType() {
    resetForm();
    $('#credit_form_title').text('Add Credit Type');
  }

  function editCreditType(creditType) {
    console.log("credit type:::", creditType);
    $('#credit_id').val(creditType.id);
    $('#credit_name').val(creditType.name);
    $('#credit_num').val(creditType.num_credits);
    $('#credit_card_cost').val(creditType.credit_card_cost);
    $('#cash_cost').val(creditType.cash_cost);
    $('#expiration_days').val(creditType.expiration_days);
    $('#no_expiration').prop('checked', creditType.expiration_days === null);
    $('#multiple_discount').val(creditType.multiple_discount);
    if (creditType.expiration_days === null) {
      $('#no_expiration').prop('checked', true);
      $('#expiration_days').prop('disabled', true);
    } else {
      $('#no_expiration').prop('checked', false);
      $('#expiration_days').prop('disabled', false);
    }

    $('#credit_form_title').text('Edit Credit Type');
  }

  function openDeleteModal(id) {
    $('#delete_id').val(id);
    delete_modal.showModal();
  }

  function deleteCreditType() {
    delete_modal.close();

    const id = $('#delete_id').val();
    $.ajax({
      url: '{{ route("delete-credit-type") }}',
      method: 'POST',
      data: { id: id },
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      success: function(response) {
        $('#success_message').text(response.message);
        success_modal.showModal();

        // update the credit types list
        updateCreditTypes(response.result);
      },
      error: function(xhr) {
        let msg = 'An error occurred. Please try again.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        $('#alert_message').text(msg);
        alert_modal.showModal();
      }
    });
  }

  function handleSearch(event) {
    if (event.key === 'Enter') {
      const query = event.target.value.toLowerCase();
      $('#credittype_list tr').each(function() {
        const row = $(this);
        const text = row.text().toLowerCase();
        row.toggle(text.includes(query));
      });
    }
  }
</script>
@endsection