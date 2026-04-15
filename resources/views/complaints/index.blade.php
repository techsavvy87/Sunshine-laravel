@extends('layouts.main')
@section('title', 'Complaints / Issues')

@section('page-css')
<link rel="stylesheet" href="{{ asset('src/libs/select2/select2.min.css') }}" />
<style>
  #form_modal .select2-container { width: 100% !important; }
  #form_modal .select2-container--default .select2-selection--single { min-height: 2.5rem; border-radius: 0.25rem; border-color: hsl(var(--bc) / 0.2); }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Complaints / Issues</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Complaints / Issues</li>
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
            <input id="complaint_search" class="w-24 sm:w-36" placeholder="Search" aria-label="Search" type="search"/>
          </label>
        </div>
        @if (hasPermission(24, 'can_create'))
        <button type="button" id="btn_add_complaint" aria-label="Add complaint" class="btn btn-primary btn-sm max-sm:btn-square">
          <span class="iconify lucide--plus size-4"></span>
          <span class="hidden sm:inline">Add Complaint / Issue</span>
        </button>
        @endif
      </div>
      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Customer</th>
              <th>Description</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($complaints as $complaint)
            <tr class="complaint-row hover:bg-base-200/40" data-id="{{ $complaint->id }}" data-customer-id="{{ $complaint->customer_id }}" data-description="{{ e($complaint->description) }}" data-date="{{ $complaint->date ? $complaint->date->format('Y-m-d') : '' }}">
              <td class="font-medium">{{ $complaints->firstItem() + $loop->index }}</td>
              <td class="customer-name">
                @if ($complaint->customer && $complaint->customer->profile)
                  {{ $complaint->customer->profile->first_name }} {{ $complaint->customer->profile->last_name }}
                @else
                  {{ $complaint->customer->name ?? '—' }}
                @endif
              </td>
              <td class="complaint-desc max-w-xs truncate" title="{{ $complaint->description }}">{{ Str::limit($complaint->description, 80) }}</td>
              <td>{{ $complaint->date ? $complaint->date->format('Y-m-d') : '—' }}</td>
              <td>
                <div class="inline-flex w-fit gap-1">
                  @if (hasPermission(24, 'can_update'))
                  <button type="button" class="btn btn-square btn-primary btn-outline btn-xs border-transparent btn-edit-complaint" aria-label="Edit">
                    <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
                  </button>
                  @endif
                  @if (hasPermission(24, 'can_delete'))
                  <button type="button" class="btn btn-square btn-error btn-outline btn-xs border-transparent btn-delete-complaint" data-id="{{ $complaint->id }}">
                    <span class="iconify lucide--trash" style="font-size: 0.875rem;"></span>
                  </button>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center text-base-content/60">No complaints / issues yet.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($complaints->hasPages())
      @include('layouts.pagination', ['items' => $complaints])
      @endif
    </div>
  </div>
</div>

{{-- Add / Edit dialog --}}
<dialog id="form_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium">
      <span id="form_modal_title">Add Complaint / Issue</span>
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <form id="complaint_form" method="POST" action="{{ route('create-complaint') }}">
      @csrf
      <input type="hidden" name="id" id="complaint_id" value="" />
      <div class="form-control mt-4">
        <label class="label" for="complaint_customer_id">
          <span class="label-text">Customer</span>
        </label>
        <select name="customer_id" id="complaint_customer_id" class="w-full" required>
          <option value="">Select customer</option>
          @foreach ($customers as $c)
          <option value="{{ $c->id }}">{{ $c->profile ? $c->profile->first_name . ' ' . $c->profile->last_name : $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-control mt-4">
        <label class="label" for="complaint_description">
          <span class="label-text">Description</span>
        </label>
        <textarea name="description" id="complaint_description" class="textarea textarea-bordered w-full min-h-24" required placeholder="Description"></textarea>
      </div>
      <div class="form-control mt-4">
        <label class="label" for="complaint_date"><span class="label-text">Date</span></label>
        <input type="date" name="date" id="complaint_date" class="input input-bordered w-full" required value="{{ date('Y-m-d') }}" />
      </div>
      <div class="modal-action">
        <button type="button" class="btn btn-ghost btn-cancel-complaint">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>

<dialog id="delete_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium">
      Delete Complaint / Issue
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4">
      You are about to delete this complaint/issue. Would you like to proceed?
    </p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <form id="delete_form" method="POST" action="{{ route('delete-complaint') }}">
        @csrf
        <input type="hidden" name="id" id="delete_complaint_id" value="" />
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
  var storeUrl = '{{ route("create-complaint") }}';
  var updateUrl = '{{ route("update-complaint") }}';

  $(function() {
    $('#complaint_customer_id').select2({
      placeholder: 'Select customer',
      allowClear: true,
      width: '100%',
      dropdownParent: $('#form_modal')
    });

    $('#complaint_search').on('keydown', function(e) {
      if (e.key === 'Enter') {
        var query = $(this).val().toLowerCase();
        $('.complaint-row').each(function() {
          var name = ($(this).find('.customer-name').text() || '').toLowerCase();
          var desc = ($(this).find('.complaint-desc').text() || '').toLowerCase();
          $(this).toggle(name.indexOf(query) !== -1 || desc.indexOf(query) !== -1);
        });
      }
    });

    $('#btn_add_complaint').on('click', function() {
      openFormModal();
    });

    $(document).on('click', '.btn-edit-complaint', function() {
      var row = $(this).closest('tr.complaint-row');
      if (!row.length) return;
      openFormModal(
        row.data('id'),
        row.data('customer-id'),
        row.data('description') || '',
        row.data('date') || ''
      );
    });

    $(document).on('click', '.btn-delete-complaint', function() {
      var id = $(this).data('id');
      $('#delete_complaint_id').val(id);
      $('#delete_modal')[0].showModal();
    });

    $(document).on('click', '.btn-cancel-complaint', function() {
      $('#form_modal')[0].close();
    });
  });

  function openFormModal(id, customerId, description, date) {
    $('#form_modal_title').text(id ? 'Edit Complaint / Issue' : 'Add Complaint / Issue');
    $('#complaint_id').val(id || '');
    $('#complaint_customer_id').val(customerId || '').trigger('change');
    $('#complaint_description').val(description || '');
    $('#complaint_date').val(date || '{{ date("Y-m-d") }}');
    $('#complaint_form').attr('action', id ? updateUrl : storeUrl);
    $('#form_modal')[0].showModal();
  }
</script>
@endsection
