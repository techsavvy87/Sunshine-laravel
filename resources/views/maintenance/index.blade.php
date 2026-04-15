@extends('layouts.main')
@section('title', 'Maintenance Report')

@section('page-css')
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Maintenance Report</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Maintenance</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="card bg-base-100 shadow mt-3">
    <div class="card-body p-0">
      <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-5">
        <div class="inline-flex items-center gap-2 flex-wrap">
          <form method="GET" action="{{ route('maintenance') }}" id="maintenance_filter_form" class="inline-flex items-center gap-2 flex-wrap">
            <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}" />
            <label class="input input-sm flex items-center gap-2 w-40">
              <span class="iconify lucide--search text-base-content/80 size-3.5"></span>
              <input type="search" name="search" id="maintenance_search" class="grow min-w-0" placeholder="Search" value="{{ $search ?? '' }}" aria-label="Search" />
            </label>
            <input type="date" name="date" id="maintenance_filter_date" class="input input-bordered input-sm w-36" value="{{ $date ?? '' }}" placeholder="Date" aria-label="Date" />
          </form>
        </div>
        @if (hasPermission(27, 'can_create'))
        <button type="button" id="btn_add_maintenance" aria-label="Add maintenance issue" class="btn btn-primary btn-sm max-sm:btn-square">
          <span class="iconify lucide--plus size-4"></span>
          <span class="hidden sm:inline">Add Issue</span>
        </button>
        @endif
      </div>
      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Type</th>
              <th>Description</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($issues as $issue)
            <tr class="maintenance-row hover:bg-base-200/40" data-id="{{ $issue->id }}" data-type="{{ e($issue->type) }}" data-description="{{ e($issue->description) }}" data-date="{{ $issue->date->format('Y-m-d') }}">
              <td class="font-medium">{{ $issues->firstItem() + $loop->index }}</td>
              <td><span class="badge badge-sm badge-ghost">{{ $issue->type }}</span></td>
              <td class="max-w-xs truncate" title="{{ $issue->description }}">{{ Str::limit($issue->description, 50) }}</td>
              <td>{{ $issue->date->format('Y-m-d') }}</td>
              <td>
                <div class="inline-flex w-fit gap-1">
                  @if (hasPermission(27, 'can_update'))
                  <button type="button" class="btn btn-square btn-primary btn-outline btn-xs border-transparent btn-edit-maintenance" aria-label="Edit">
                    <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
                  </button>
                  @endif
                  @if (hasPermission(27, 'can_delete'))
                  <button type="button" class="btn btn-square btn-error btn-outline btn-xs border-transparent btn-delete-maintenance" data-id="{{ $issue->id }}">
                    <span class="iconify lucide--trash" style="font-size: 0.875rem;"></span>
                  </button>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center text-base-content/60">No maintenance issues yet.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($issues->hasPages())
      @include('layouts.pagination', ['items' => $issues])
      @endif
    </div>
  </div>
</div>

{{-- Add / Edit dialog --}}
<dialog id="form_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium">
      <span id="form_modal_title">Add Maintenance Issue</span>
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <form id="maintenance_form" method="POST" action="{{ route('create-maintenance') }}">
      @csrf
      <input type="hidden" name="id" id="maintenance_id" value="" />
      <div class="form-control mt-4">
        <label class="label" for="maintenance_type"><span class="label-text">Type</span></label>
        <input type="text" name="type" id="maintenance_type" class="input input-bordered w-full" required placeholder="e.g. Facility, Equipment" maxlength="255" />
      </div>
      <div class="form-control mt-4">
        <label class="label" for="maintenance_description"><span class="label-text">Description</span></label>
        <textarea name="description" id="maintenance_description" class="textarea textarea-bordered w-full min-h-24" required placeholder="Description"></textarea>
      </div>
      <div class="form-control mt-4">
        <label class="label" for="maintenance_date"><span class="label-text">Date</span></label>
        <input type="date" name="date" id="maintenance_date" class="input input-bordered w-full" required value="{{ date('Y-m-d') }}" />
      </div>
      <div class="modal-action">
        <button type="button" class="btn btn-ghost btn-cancel-maintenance">Cancel</button>
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
      Delete Maintenance Issue
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4">You are about to delete this maintenance issue. Would you like to proceed?</p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <form id="delete_form" method="POST" action="{{ route('delete-maintenance') }}">
        @csrf
        <input type="hidden" name="id" id="delete_maintenance_id" value="" />
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
  var storeUrl = '{{ route("create-maintenance") }}';
  var updateUrl = '{{ route("update-maintenance") }}';

  $(function() {
    $('#maintenance_search').on('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        $('#maintenance_filter_form').submit();
      }
    });
    $('#maintenance_filter_date').on('change', function() {
      $('#maintenance_filter_form').submit();
    });

    $('#btn_add_maintenance').on('click', function() {
      openFormModal();
    });

    $(document).on('click', '.btn-edit-maintenance', function() {
      var row = $(this).closest('tr.maintenance-row');
      if (!row.length) return;
      openFormModal(
        row.data('id'),
        row.data('type') || '',
        row.data('description') || '',
        row.data('date') || ''
      );
    });

    $(document).on('click', '.btn-delete-maintenance', function() {
      var id = $(this).data('id');
      $('#delete_maintenance_id').val(id);
      $('#delete_modal')[0].showModal();
    });

    $(document).on('click', '.btn-cancel-maintenance', function() {
      $('#form_modal')[0].close();
    });
  });

  function openFormModal(id, type, description, date) {
    $('#form_modal_title').text(id ? 'Edit Maintenance Issue' : 'Add Maintenance Issue');
    $('#maintenance_id').val(id || '');
    $('#maintenance_type').val(type || '');
    $('#maintenance_description').val(description || '');
    $('#maintenance_date').val(date || '{{ date("Y-m-d") }}');
    $('#maintenance_form').attr('action', id ? updateUrl : storeUrl);
    $('#form_modal')[0].showModal();
  }
</script>
@endsection
