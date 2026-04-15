@extends('layouts.main')
@section('title', 'Group Classes')

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
  <h3 class="text-lg font-medium">Group Classes Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('services') }}">Services</a></li>
      <li>Group Classes</li>
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
            <input class="w-24 sm:w-36" placeholder="Search classes" aria-label="Search classes" type="search" onkeydown="handleSearch(event)" value="{{ $search }}"/>
          </label>
        </div>
        <a aria-label="Create group class link" class="btn btn-primary btn-sm max-sm:btn-square" href="{{ route('add-group-class') }}">
          <span class="iconify lucide--plus size-4"></span>
          <span class="hidden sm:inline">New Group Class</span>
        </a>
      </div>
      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Name</th>
              <th>Price</th>
              <th>Duration</th>
              <th>Schedule</th>
              <th>Start Date</th>
              <th style="text-align:center">Status</th>
              <th style="text-align:center">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($groupClasses as $class)
            <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
              <td>{{ $loop->iteration }}</td>
              <td>{{ $class->name }}</td>
              <td>${{ number_format($class->price, 2) }}</td>
              <td>{{ $class->duration_amount }} {{ ucfirst($class->duration_unit) }}</td>
              <td class="truncate max-w-xs">{{ $class->schedule }}</td>
              <td>{{ \Carbon\Carbon::parse($class->started_at)->format('M j, Y, H:i') }}</td>
              <td style="text-align:center">
                @if ($class->status === 'active')
                  <span class="badge badge-soft badge-success badge-sm">Active</span>
                @else
                  <span class="badge badge-soft badge-error badge-sm">Inactive</span>
                @endif
              </td>
              <td style="text-align:center">
                <div class="inline-flex w-fit">
                  <a class="btn btn-square btn-ghost btn-sm" href="{{ route('edit-group-class', ['id' => $class->id]) }}">
                    <span class="iconify lucide--pencil text-base-content/80 size-4"></span>
                  </a>
                  <button onclick="confirmDelete({{ $class }})" class="btn btn-square btn-error btn-outline btn-sm border-transparent">
                    <span class="iconify lucide--trash size-4"></span>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-8 text-base-content/60">
                No group classes found. <a href="{{ route('add-group-class') }}" class="link link-primary">Create one</a> to get started.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $groupClasses->links('layouts.pagination', ['items' => $groupClasses]) }}
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
      <form id="delete_form" method="POST" action="{{ route('delete-group-class') }}">
        @csrf
        <input type="hidden" name="class_id" value="" />
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
      const url = `/classes?search=${encodeURIComponent(searchValue)}`;
      window.location.href = url;
    }
  }
  function confirmDelete(groupClass) {
    const message = `You are about to delete the group class "${groupClass.name}". Would you like to proceed?`;
    $('#delete_modal_message').text(message);
    $('#delete_form input[name=class_id]').val(groupClass.id);
    delete_modal.showModal();
  }
</script>
@endsection

