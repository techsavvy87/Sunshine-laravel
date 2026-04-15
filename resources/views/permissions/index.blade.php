@extends('layouts.main')
@section('title', 'Permissions')

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Permissions Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="./dashboards-ecommerce.html">PawPrints</a></li>
      <li>Permissions</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="card card-border bg-base-100 mt-3">
    <div class="card-body">
      <div class="mt-4 flex items-center justify-between">
        <label class="input input-sm">
          <span class="iconify lucide--search text-base-content/60 size-5"></span>
          <input class="grow" placeholder="Search" type="search" onkeydown="handleSearch(event)" value="{{ $search }}"/>
        </label>
        @if (hasPermission(6, 'can_create'))
        <button class="btn btn-primary btn-sm" onclick="openAddModal()">
          <span class="iconify lucide--plus size-4"></span>
          Add
        </button>
        @endif
      </div>
      <div class="mt-6 flex flex-wrap gap-3">
        @foreach ($permissions as $permission)
          <div class="badge badge-soft badge-primary badge-md cursor-pointer">
            @if (hasPermission(6, 'can_update'))
            <span onclick="openEditModal({{ $permission }})">{{ $permission->title }}</span>
            @else
            <span>{{ $permission->title }}</span>
            @endif
            @if (hasPermission(6, 'can_delete'))
            <span class="iconify lucide--x" onclick="openDeleteModal({{ $permission->id }})"></span>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
<dialog id="permission_modal" class="modal">
  <div class="modal-box">
    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute top-2 right-2">
        <span class="iconify lucide--x size-4"></span>
      </button>
    </form>
    <form id="save_form" method="POST" >
      @csrf
      <h3 class="text-lg font-medium" id="title">Add Permission</h3>
      <input type="hidden" name="id" value="" />
      <fieldset class="fieldset mt-4">
        <p class="fieldset-label">Title *</p>
        <input class="input w-full" aria-label="Input" placeholder="Type title here" type="text" name="title" required/>
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
<dialog id="delete_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium">
      Delete Permission
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4">
      You are about to delete this permission. Would you like to proceed further?
    </p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <form id="delete_form" method="POST" action="{{ route('delete-permission') }}">
        @csrf
        <input type="hidden" name="id" value="" />
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
  function openAddModal() {
    $('#save_form #title').text('Add Permission');
    $('#save_form input[name=id]').val('');
    $('#save_form input[name="title"]').val('');
    $('#save_form').attr('action', "{{ route('create-permission') }}")
    permission_modal.showModal();
  }
  function openEditModal(permission) {
    $('#save_form #title').text('Edit Permission');
    $('#save_form input[name=id]').val(permission.id);
    $('#save_form input[name=title]').val(permission.title);
    $('#save_form').attr('action', "{{ route('update-permission') }}")
    permission_modal.showModal();
  }
  function openDeleteModal(id) {
    $('#delete_form input[name=id]').val(id);
    delete_modal.showModal();
  }
  function handleSearch(event) {
    if (event.key === 'Enter') {
      const searchValue = event.target.value;
      const url = `/permissions?search=${encodeURIComponent(searchValue)}`;
      window.location.href = url;
    }
}
</script>
@endsection