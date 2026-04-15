@extends('layouts.main')
@section('title', 'Weight Ranges')

@section('page-css')
<style>
  th, td {
    text-align: center;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Weight Ranges Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Weight Ranges</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="mt-3 grid grid-cols-1 gap-4 xl:grid-cols-5 2xl:grid-cols-10">
    <div class="xl:col-span-2 2xl:col-span-3">
      <div class="card bg-base-100 card-border">
        <div class="card-body">
          <div class="card-title" id="range_form_title">@if (hasPermission(8, 'can_create')) Add Weight Range @else View Weight Range @endif</div>
          <input type="hidden" id="range_id" value="">
          <div class="fieldset">
            <div class="space-y-1 mt-2">
              <label class="fieldset-label" for="range_name">Name*</label>
              <input class="input w-full" id="range_name" type="text" placeholder="" />
            </div>
            <div class="mt-2 flex items-center gap-6">
              <div class="space-y-1">
                <label class="fieldset-label" for="range_min_weight">Min Weight*</label>
                <label class="input w-full focus:outline-0">
                  <input class="grow focus:outline-0" placeholder="e.g. 10" id="range_min_weight" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
                  <span class="badge badge-ghost badge-sm">lbs</span>
                </label>
              </div>
              <div class="space-y-1">
                <label class="fieldset-label" for="range_max_weight">Max Weight*</label>
                <label class="input w-full focus:outline-0">
                  <input class="grow focus:outline-0" placeholder="e.g. 10" id="range_max_weight" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
                  <span class="badge badge-ghost badge-sm">lbs</span>
                </label>
              </div>
            </div>
          </div>
          <div class="mt-5 flex justify-end gap-3">
            @if (hasPermission(8, 'can_create') || hasPermission(8, 'can_update'))
            <button class="btn btn-ghost btn-sm" id="cancel_btn" onclick="cancelWeightRange()">Cancel</button>
            <button class="btn btn-sm btn-primary gap-1" onclick="saveWeightRange()" id="save_btn">
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
                <input class="w-24 sm:w-36" placeholder="Search weight ranges" aria-label="Search weight ranges" type="search" onkeydown="handleSearch(event)"/>
              </label>
            </div>
          </div>
          <div class="mt-4">
            <table class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th style="text-align: left">Name</th>
                  <th>Min Weight (lbs)</th>
                  <th>Max Weight (lbs)</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="weightrange_list">
                @foreach ($weightRanges as $weightRange)
                <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
                  <td>{{ $loop->iteration }}</td>
                  <td style="text-align: left">{{ $weightRange->name }}</td>
                  <td>{{ $weightRange->min_weight }}</td>
                  <td>{{ $weightRange->max_weight }}</td>
                  <td>
                    <div class="inline-flex w-fit">
                      @if (hasPermission(8, 'can_update'))
                      <button class="btn btn-square btn-primary btn-outline btn-xs border-transparent" onclick="editWeightRange({{ $weightRange }})">
                        <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
                      </button>
                      @endif
                      @if (hasPermission(8, 'can_delete'))
                      <button class="btn btn-square btn-error btn-outline btn-xs border-transparent" onclick="openDeleteModal({{ $weightRange->id }})">
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
      Delete Weight Range
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4">
      You are about to delete this weight range. Would you like to proceed further?
    </p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <input type="hidden" id="delete_id" value="" />
      <button class="btn btn-error" onclick="deleteWeightRange()">Delete</button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>
@endsection

@section('page-js')
<script>
  function saveWeightRange() {
    const weightRangeId = $('#range_id').val();
    const name = $('#range_name').val();
    const minWeight = $('#range_min_weight').val();
    const maxWeight = $('#range_max_weight').val();

    if (!name || !minWeight || !maxWeight) {
      $('#alert_message').text('Please fill in all required fields.');
      alert_modal.showModal();
      return;
    }

    const data = {
      weight_range_id: weightRangeId,
      name,
      min_weight: minWeight,
      max_weight: maxWeight
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
      url: weightRangeId ? '{{ route("update-weight-range") }}' : '{{ route("create-weight-range") }}',
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

        // update the weight ranges list
        updateWeightRanges(response.result);
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
    $('#range_id').val('');
    $('#range_name').val('');
    $('#range_min_weight').val('');
    $('#range_max_weight').val('');

    $('#range_form_title').text('Add Weight Range');
  }

  function updateWeightRanges(result) {
    const weightRangeList = $('#weightrange_list');
    weightRangeList.empty();

    $.each(result, function(index, weightRange) {
      const row = `
        <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
          <td>${index + 1}</td>
          <td style="text-align: left">${weightRange.name}</td>
          <td>${weightRange.min_weight}</td>
          <td>${weightRange.max_weight}</td>
          <td>
            <div class="inline-flex w-fit">
              <button class="btn btn-square btn-primary btn-outline btn-xs border-transparent" onclick='editWeightRange(${JSON.stringify(weightRange)})'>
                <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
              </button>
              <button class="btn btn-square btn-error btn-outline btn-xs border-transparent" onclick='openDeleteModal(${weightRange.id})'>
                <span class="iconify lucide--trash" style="font-size: 0.875rem;"></span>
              </button>
            </div>
          </td>
        </tr>`;
      weightRangeList.append(row);
    });
  }

  function cancelWeightRange() {
    resetForm();
    $('#range_form_title').text('Add Weight Range');
  }

  function editWeightRange(weightRange) {
    console.log("weight range:::", weightRange);
    $('#range_id').val(weightRange.id);
    $('#range_name').val(weightRange.name);
    $('#range_min_weight').val(weightRange.min_weight);
    $('#range_max_weight').val(weightRange.max_weight);
  }

  function openDeleteModal(id) {
    $('#delete_id').val(id);
    delete_modal.showModal();
  }

  function deleteWeightRange() {
    delete_modal.close();

    const id = $('#delete_id').val();
    $.ajax({
      url: '{{ route("delete-weight-range") }}',
      method: 'POST',
      data: { id: id },
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      success: function(response) {
        $('#success_message').text(response.message);
        success_modal.showModal();

        // update the weight ranges list
        updateWeightRanges(response.result);
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
      $('#weightrange_list tr').each(function() {
        const row = $(this);
        const text = row.text().toLowerCase();
        row.toggle(text.includes(query));
      });
    }
  }
</script>
@endsection