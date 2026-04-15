@extends('layouts.main')
@section('title', 'Capacities')

@section('page-css')
<style>
  th, td {
    text-align: center;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Capacities Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Capacities</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="mt-3 grid grid-cols-1 gap-4 xl:grid-cols-5 2xl:grid-cols-10">
    <div class="xl:col-span-2 2xl:col-span-3">
      <div class="card bg-base-100 card-border">
        <div class="card-body">
          <div class="card-title" id="capacity_form_title">@if (hasPermission(9, 'can_create')) Add Capacity @else View Capacity @endif</div>
          <input type="hidden" id="capacity_id" value="">
          <div class="fieldset">
            <div class="space-y-1 mt-2">
              <label class="fieldset-label" for="service">Service*</label>
              <select class="select w-full" id="service">
                <option value="" hidden>Select Service</option>
                @foreach ($services as $service)
                  <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="space-y-1 mt-2">
              <label class="fieldset-label" for="capacity">Capacity*</label>
              <input class="input w-full focus:outline-0" placeholder="e.g. 10" id="capacity" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
            </div>
            <div class="space-y-1 mt-2">
              <label class="fieldset-label" for="notes">Notes</label>
              <textarea class="textarea w-full focus:outline-0" placeholder="Additional notes..." id="notes" rows="3"></textarea>
            </div>
          </div>
          <div class="mt-5 flex justify-end gap-3">
            @if (hasPermission(9, 'can_create') || hasPermission(9, 'can_update'))
            <button class="btn btn-ghost btn-sm" id="cancel_btn" onclick="cancelCapacity()">Cancel</button>
            <button class="btn btn-sm btn-primary gap-1" onclick="saveCapacity()" id="save_btn">
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
                <input class="w-24 sm:w-36" placeholder="Search capacities" aria-label="Search capacities" type="search" onkeydown="handleSearch(event)"/>
              </label>
            </div>
          </div>
          <div class="mt-4">
            <table class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th style="text-align: left">Service</th>
                  <th style="text-align: left">Notes</th>
                  <th>Capacity</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="capacity_list">
                @foreach ($capacities as $capacity)
                <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
                  <td>{{ $loop->iteration }}</td>
                  <td style="text-align: left">{{ $capacity->service->name }}</td>
                  <td class="truncate-one-line" style="text-align: left">{{ $capacity->notes }}</td>
                  <td>{{ $capacity->capacity }}</td>
                  <td>
                    <div class="inline-flex w-fit">
                      @if (hasPermission(9, 'can_update'))
                      <button class="btn btn-square btn-primary btn-outline btn-xs border-transparent" onclick="editCapacity({{ $capacity }})">
                        <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
                      </button>
                      @endif
                      @if (hasPermission(9, 'can_delete'))
                      <button class="btn btn-square btn-error btn-outline btn-xs border-transparent" onclick="openDeleteModal({{ $capacity->id }})">
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
      Delete Capacity
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4">
      You are about to delete this capacity. Would you like to proceed further?
    </p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <input type="hidden" id="delete_id" value="" />
      <button class="btn btn-error" onclick="deleteCapacity()">Delete</button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>
@endsection

@section('page-js')
<script>
  function saveCapacity() {
    const capacityId = $('#capacity_id').val();
    const service = $('#service').val();
    const capacity = $('#capacity').val();
    const notes = $('#notes').val();

    if (!service || !capacity) {
      $('#alert_message').text('Please fill in all required fields.');
      alert_modal.showModal();
      return;
    }

    const data = {
      capacity_id: capacityId,
      service_id: service,
      capacity: capacity,
      notes: notes
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
      url: capacityId ? '{{ route("update-capacity") }}' : '{{ route("create-capacity") }}',
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

        // update the capacity list
        updateCapacities(response.result);
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
    $('#capacity_id').val('');
    $('#service').val('').trigger('change');
    $('#capacity').val('');
    $('#notes').val('');

    $('#capacity_form_title').text('Add Capacity');
  }

  function updateCapacities(result) {
    const capacityList = $('#capacity_list');
    capacityList.empty();

    $.each(result, function(index, capacity) {
      const row = `
        <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
          <td>${index + 1}</td>
          <td style="text-align: left">${capacity.service.name}</td>
          <td class="truncate-one-line" style="text-align: left">${capacity.notes}</td>
          <td>${capacity.capacity}</td>
          <td>
            <div class="inline-flex w-fit">
              <button class="btn btn-square btn-primary btn-outline btn-xs border-transparent" onclick='editCapacity(${JSON.stringify(capacity)})'>
                <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
              </button>
              <button class="btn btn-square btn-error btn-outline btn-xs border-transparent" onclick='openDeleteModal(${capacity.id})'>
                <span class="iconify lucide--trash" style="font-size: 0.875rem;"></span>
              </button>
            </div>
          </td>
        </tr>`;
      capacityList.append(row);
    });
  }

  function cancelCapacity() {
    resetForm();
    $('#capacity_form_title').text('Add Capacity');
  }

  function editCapacity(capacity) {
    $('#capacity_id').val(capacity.id);
    $('#service').val(capacity.service.id).trigger('change');
    $('#capacity').val(capacity.capacity);
    $('#notes').val(capacity.notes);
  }

  function openDeleteModal(id) {
    $('#delete_id').val(id);
    delete_modal.showModal();
  }

  function deleteCapacity() {
    delete_modal.close();

    const id = $('#delete_id').val();
    $.ajax({
      url: '{{ route("delete-capacity") }}',
      method: 'POST',
      data: { id: id },
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      success: function(response) {
        $('#success_message').text(response.message);
        success_modal.showModal();
        // update the capacity list
        updateCapacities(response.result);
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
      $('#capacity_list tr').each(function() {
        const row = $(this);
        const text = row.text().toLowerCase();
        row.toggle(text.includes(query));
      });
    }
  }
</script>
@endsection