@extends('layouts.main')
@section('title', 'Kennels')

@section('page-css')
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Kennels Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Kennels</li>
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
            <input class="w-24 sm:w-36" placeholder="Search kennels" aria-label="Search kennels" type="search" onkeydown="handleSearch(event)" value="{{ $search }}"/>
          </label>
        </div>
        @if (hasPermission(27, 'can_create'))
        <a aria-label="Create kennel link" class="btn btn-primary btn-sm max-sm:btn-square" href="{{ route('add-kennel') }}">
          <span class="iconify lucide--plus size-4"></span>
          <span class="hidden sm:inline">New Kennel</span>
        </a>
        @endif
      </div>

      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Image</th>
              <th>Name</th>
              <th>Assigned Pet</th>
              <th>Type</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($kennels as $kennel)
            <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
              <td>{{ $loop->iteration }}</td>
              <td>
                <div class="flex items-center space-x-3 truncate">
                  @if (empty($kennel->img))
                  <img src="{{ asset('images/no_image.jpg') }}" alt="Seller Image" class="rounded-box bg-base-200 size-10">
                  @else
                  <img src="{{ asset('storage/kennels/'. $kennel->img) }}" alt="Seller Image" class="rounded-box bg-base-200 size-10">
                  @endif
                </div>
              </td>
              <td>{{ $kennel->name }}</td>
              <td>
                @if (isset($kennel->current_pets) && $kennel->current_pets->isNotEmpty())
                  <div class="flex flex-col gap-2">
                    @foreach ($kennel->current_pets as $pet)
                      <div class="flex items-center gap-2">
                        <img src="{{ empty($pet->pet_img) ? asset('images/no_image.jpg') : asset('storage/pets/' . $pet->pet_img) }}" alt="Pet Image" class="mask mask-squircle bg-base-200 size-8">
                        <span>{{ $pet->name }}</span>
                      </div>
                    @endforeach
                  </div>
                @endif
              </td>
              <td>
                @php
                  $typeClass = $kennel->type === 'dog' ? 'badge-info' : 'badge-secondary';
                @endphp
                <span class="badge badge-soft badge-sm {{ $typeClass }}">{{ ucfirst($kennel->type) }}</span>
              </td>
              <td>
                @php
                  $statusClass = match($kennel->status) {
                    'In Service' => 'badge-success',
                    'Out of Service' => 'badge-error',
                    default => 'badge-warning',
                  };
                @endphp
                <span class="badge badge-soft badge-sm {{ $statusClass }}">{{ $kennel->status }}</span>
              </td>
              <td>
                <div class="inline-flex w-fit gap-1">
                  @if (hasPermission(27, 'can_update'))
                  <a aria-label="Edit kennel" class="btn btn-square btn-primary btn-outline btn-xs border-transparent" href="{{ route('edit-kennel', ['id' => $kennel->id]) }}">
                    <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
                  </a>
                  @endif
                  @if (hasPermission(27, 'can_delete'))
                  <button type="button" class="btn btn-square btn-error btn-outline btn-xs border-transparent btn-delete-kennel" data-id="{{ $kennel->id }}" data-name="{{ e($kennel->name) }}" aria-label="Delete kennel">
                    <span class="iconify lucide--trash" style="font-size: 0.875rem;"></span>
                  </button>
                  @endif
                </div>
              </td>
            </tr>
            @endforeach
            @if ($kennels->isEmpty())
            <tr>
              <td colspan="8" class="text-center text-base-content/60">No kennels found.</td>
            </tr>
            @endif
          </tbody>
        </table>
      </div>

      {{ $kennels->links('layouts.pagination', ['items' => $kennels]) }}
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
    <p class="py-4" id="delete_modal_message">You are about to delete this kennel. Would you like to proceed?</p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <form id="delete_form" method="POST" action="{{ route('delete-kennel') }}">
        @csrf
        <input type="hidden" name="id" id="delete_kennel_id" value="" />
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
      const url = `/kennels?search=${encodeURIComponent(searchValue)}`;
      window.location.href = url;
    }
  }

  $(function() {
    $(document).on('click', '.btn-delete-kennel', function() {
      var id = $(this).data('id');
      var name = $(this).data('name') || 'this kennel';
      $('#delete_kennel_id').val(id);
      $('#delete_modal_message').text('You are about to delete the kennel ' + name + '. Would you like to proceed?');
      $('#delete_modal')[0].showModal();
    });
  });
</script>
@endsection
