@extends('layouts.main')
@section('title', 'Item Detail')

@section('page-css')
<style>
  .table th,
  .table td {
    text-align: center;
    padding-block: 0.4rem;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Inventory Detail</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('inventory-items') }}">Items</a></li>
      <li class="opacity-80">Detail</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="mt-3 grid grid-cols-1 gap-6 lg:grid-cols-12">
    <div class="lg:col-span-8 2xl:col-span-9 space-y-4">
      <div class="card card-border bg-base-100">
        <div class="card-body p-0">
          <div class="px-5 pt-5">
            <div class="flex justify-between">
              <div class="space-x-2">
                <label class="input input-sm">
                  <span class="iconify lucide--search text-base-content/80 size-3.5"></span>
                  <input class="w-24 sm:w-36" placeholder="Search logs" type="search" onkeydown="handleSearch(event)" value=""/>
                </label>
              </div>
              <button class="btn btn-sm btn-outline border-primary-300 btn-primary" onclick="save_modal.showModal();">
                <span class="iconify lucide--scroll-text size-3.5"></span>Update Inventory
              </button>
            </div>
          </div>
          <div class="mt-3 overflow-auto">
            <table class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Quantity</th>
                  <th>Increase/Decrease</th>
                  <th>Reason</th>
                  <th>Changed By</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($transactions as $transaction)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $transaction->quantity_change }}</td>
                  <td>
                    @if ($transaction->change_type === 'increase')
                      <span class="iconify lucide--arrow-up text-success size-4"></span>
                    @else
                      <span class="iconify lucide--arrow-down text-error size-4"></span>
                    @endif
                  </td>
                  <td>{{ $transaction->reason }}</td>
                  <td>{{ $transaction->user->name ?? '' }}</td>
                  <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('m/d/Y') }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="card card-border bg-base-100">
        <div class="card-body p-0">
          <div class="px-5 pt-5">
            <div class="flex justify-between">
              <div class="space-x-2">
                <label class="input input-sm">
                  <span class="iconify lucide--search text-base-content/80 size-3.5"></span>
                  <input class="w-24 sm:w-36" placeholder="Search invoices" type="search" onkeydown="handleSearch(event)" value=""/>
                </label>
              </div>
            </div>
          </div>
          <div class="mt-3 overflow-auto">
            <table class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Quantity Sold</th>
                  <th>Invoice</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="lg:col-span-4 2xl:col-span-3">
      <div class="card card-border bg-base-100">
        <div class="card-body gap-0">
          <p class="bg-base-200 rounded-box px-3 py-1 font-medium">Inventory Info</p>
          <div class="mt-2">
            <div class="mt-2 flex gap-2">
              <label class="text-sm font-semibold">Vendor:</label>
              <p class="text-base-content/80 text-sm">{{ $inventoryItem->vendor }}</p>
            </div>
            <div class="mt-2 flex gap-2">
              <label class="text-sm font-semibold">Brand:</label>
              <p class="text-base-content/80 text-sm">{{ $inventoryItem->brand }}</p>
            </div>
            @if ($inventoryItem->description)
            <div class="mt-2 flex gap-2">
              <label class="text-sm font-semibold">Description:</label>
              <p class="text-base-content/80 text-sm">{{ $inventoryItem->description }}</p>
            </div>
            @endif
            @foreach($inventoryItem->attributes as $attribute)
            <div class="mt-2 flex gap-2">
              <label class="text-sm font-semibold">{{ $attribute->attribute_name }}:</label>
              <p class="text-base-content/80 text-sm">{{ $attribute->attribute_value }}</p>
            </div>
            @endforeach
            @if ($inventoryItem->sku)
            <div class="mt-2 flex gap-2">
              <label class="text-sm font-semibold" for="sku">SKU:</label>
              <p class="text-base-content/80 text-sm">{{ $inventoryItem->sku }}</p>
            </div>
            @endif
            <div class="mt-2 flex gap-2">
              <label class="text-sm font-semibold">Cost:</label>
              <p class="text-base-content/80 text-sm">${{ $inventoryItem->cost }}</p>
            </div>
            <div class="mt-2 flex gap-2">
              <label class="text-sm font-semibold">Wholesale Cost:</label>
              <p class="text-base-content/80 text-sm">${{ $inventoryItem->wholesale_cost }}</p>
            </div>
            <div class="mt-2 flex gap-2">
              <label class="text-sm font-semibold">Category:</label>
              <p class="text-base-content/80 text-sm">{{ $inventoryItem->category->name ?? '' }}</p>
            </div>
            <div class="mt-2 flex gap-2">
              <label class="text-sm font-semibold">PAR:</label>
              <p class="text-base-content/80 text-sm">{{ $inventoryItem->par }}</p>
            </div>
            <div class="mt-3 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <label class="text-sm font-semibold">Hidden:</label>
                @if ($inventoryItem->is_hidden)
                <span class="iconify lucide--badge-check text-success size-4.5"></span>
                @else
                <span class="iconify lucide--badge-x text-error size-4.5"></span>
                @endif
              </div>
              <div class="flex items-center gap-3">
                <label class="text-sm font-semibold">Service:</label>
                @if ($inventoryItem->is_service)
                <span class="iconify lucide--badge-check text-success size-4.5"></span>
                @else
                <span class="iconify lucide--badge-x text-error size-4.5"></span>
                @endif
              </div>
            </div>
            <hr class="my-2" style="color: #bdbdbd"/>
            <div class="mt-2 flex gap-2">
              <label class="text-base-content/80 font-semibold">Total Quantity:</label>
              <p class="text-base-content/90">{{ $inventoryItem->quantity }}</p>
            </div>
            <div class="flex items-center mt-5 gap-2" style="float: right">
              <a class="btn btn-sm btn-primary btn-outline" href="{{ route('inventory-items') }}">
                <span class="iconify lucide--chevron-left size-4"></span>
                Back
              </a>
              <a class="btn btn-sm btn-primary" href="{{ route('edit-inventory-item', ['id' => $inventoryItem->id]) }}">
                <span class="iconify lucide--pencil size-3"></span>
                Edit
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<dialog id="save_modal" class="modal">
  <div class="modal-box">
    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute top-2 right-2">
        <span class="iconify lucide--x size-4"></span>
      </button>
    </form>
    <form id="save_form" method="POST" action="{{ route('create-inventory-transaction') }}">
      @csrf
      <h3 class="text-lg font-medium" id="title">Update Inventory</h3>
      <input type="hidden" name="item_id" value="{{ $inventoryItem->id }}" />
      <fieldset class="fieldset mt-3">
        <p class="fieldset-label">Quantity *</p>
        <input
          class="input w-full"
          placeholder="Type quantity here"
          type="text"
          name="quantity"
          oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
          required
        />
      </fieldset>
      <fieldset class="fieldset mt-2">
        <p class="fieldset-label">Reason</p>
        <input
          class="input w-full"
          placeholder="Type reason here"
          type="text"
          name="reason"
        />
      </fieldset>
      <fieldset class="fieldset mt-4">
        <div class="flex items-center gap-3">
          <input class="radio radio-sm" id="type_increase" type="radio" value="increase" checked name="change_type" />
          <label class="fieldset-label" for="type_increase">Increase</label>
          <input class="radio radio-sm" id="type_decrease" type="radio" value="decrease" name="change_type" />
          <label class="fieldset-label" for="type_decrease">Decrease</label>
        </div>
      </fieldset>
      <div class="modal-action">
        <button class="btn btn-primary" type="submit">Save</button>
      </div>
    </form>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>
@endsection