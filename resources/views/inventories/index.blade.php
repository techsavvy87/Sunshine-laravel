@extends('layouts.main')
@section('title', 'Inventory Items')

@section('page-css')
<style>
  .table th,
  .table td {
    padding-block: 0.5rem;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Inventory Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Inventory</li>
      <li>Items</li>
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
            <input class="w-24 sm:w-36" placeholder="Search users" aria-label="Search users" type="search" onkeydown="handleSearch(event)" value="{{ $search }}"/>
          </label>
        </div>
        @if (hasPermission(4, 'can_create'))
        <a aria-label="Create seller link" class="btn btn-primary btn-sm max-sm:btn-square" href="{{ route('add-inventory-item') }}">
          <span class="iconify lucide--plus size-4"></span>
          <span class="hidden sm:inline">New Item</span>
        </a>
        @endif
      </div>
      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Vendor</th>
              <th>Brand</th>
              <th>Cost</th>
              <th>Wholesale</th>
              <th>Category</th>
              <th style="text-align:center">Service</th>
              <th style="text-align:center">Quantity</th>
              <th style="text-align:center">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($inventoryItems as $item)
            <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
              <td>{{ $loop->iteration }}</td>
              <td>{{ $item->vendor }}</td>
              <td>{{ $item->brand }}</td>
              <td>${{ $item->cost }}</td>
              <td>${{ $item->wholesale_cost }}</td>
              <td>{{ $item->category->name }}</td>
              <td style="text-align:center">{{ $item->is_service ? 'Yes' : 'No' }}</td>
              <td style="text-align:center">{{ $item->quantity }}</td>
              <td style="text-align:center">
                <div class="inline-flex w-fit">
                  @if (hasPermission(4, 'can_read'))
                  <a href="{{ route('detail-inventory-item', ['id' => $item->id]) }}" class="btn btn-square btn-info btn-outline btn-sm border-transparent">
                    <span class="iconify lucide--eye size-4"></span>
                  </a>
                  @endif
                  @if (hasPermission(4, 'can_update'))
                  <a class="btn btn-square btn-ghost btn-sm" href="{{ route('edit-inventory-item', ['id' => $item->id]) }}">
                    <span class="iconify lucide--pencil text-base-content/80 size-4"></span>
                  </a>
                  @endif
                  @if (hasPermission(4, 'can_delete'))
                  <button onclick="confirmDelete({{ $item }})" class="btn btn-square btn-error btn-outline btn-sm border-transparent">
                    <span class="iconify lucide--trash size-4"></span>
                  </button>
                  @endif
                </div>
              </td>
            </tr>
            @endforeach
          </thead>
        </table>
      </div>
      {{ $inventoryItems->links('layouts.pagination', ['items' => $inventoryItems]) }}
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
      <form id="delete_form" method="POST" action="{{ route('delete-inventory-item') }}">
        @csrf
        <input type="hidden" name="item_id" value="" />
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
      const url = `/inventory/items?search=${encodeURIComponent(searchValue)}`;
      window.location.href = url;
    }
  }
  function confirmDelete(item) {
    const message = `You are about to delete the item ${item.name}. Would you like to proceed?`;
    $('#delete_modal_message').text(message);
    $('#delete_form input[name=item_id]').val(item.id);
    delete_modal.showModal();
  }
</script>
@endsection