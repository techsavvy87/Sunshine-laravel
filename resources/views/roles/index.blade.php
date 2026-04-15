@extends('layouts.main')
@section('title', 'Roles')

@section('page-css')
<style>
  .table :where(th,td) {
    padding-block: 0.25rem;
  }
  .collapse-title-custom {
    padding-inline-end: 1rem;
    min-height: 1.2rem;
    padding: 12px 18px;
    position: relative;
  }
  .add-permission-btn {
    height: 2px;
    margin-bottom: 10px;
  }
  .btn-box {
    position: absolute;
    right: 1rem;
    top: 0.6rem;
    z-index: 10;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Roles Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Roles</li>
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
      <div class="mt-4 space-y-1">
        @foreach ($roles as $role)
        <div class="rounded-box collapse border border-base-300">
          <input aria-label="Collapse trigger" type="checkbox" checked="" name="accordion-multiple" style="min-height: 0px"/>
          <div class="collapse-title text-md font-medium collapse-title-custom">
            <span>{{ ucfirst($role->title) }}</span>
          </div>
          <div class="collapse-content">
            @if (hasPermission(6, 'can_create'))
            <button class="btn btn-link add-permission-btn" onclick="openAddRolePermissionModal({{ $role->id }})">+ Add Permission To This Role</button>
            @endif
            <table class="table" style="width: 80%; margin-top: 6px;">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Permission Resource</th>
                  <th>Create</th>
                  <th>Read</th>
                  <th>Update</th>
                  <th>Delete</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($role->permissions as $permission)
                <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
                  <td class="font-medium">{{ $loop->iteration }}</td>
                  <td>{{ $permission->title }}</td>
                  <td>
                    @if ($permission->pivot->can_create)
                    <span class="iconify lucide--badge-check text-success size-4.5"></span>
                    @else
                    <span class="iconify lucide--badge-x text-error size-4.5"></span>
                    @endif
                  </td>
                  <td>
                    @if ($permission->pivot->can_read)
                    <span class="iconify lucide--badge-check text-success size-4.5"></span>
                    @else
                    <span class="iconify lucide--badge-x text-error size-4.5"></span>
                    @endif
                  </td>
                  <td>
                    @if ($permission->pivot->can_update)
                    <span class="iconify lucide--badge-check text-success size-4.5"></span>
                    @else
                    <span class="iconify lucide--badge-x text-error size-4.5"></span>
                    @endif
                  </td>
                  <td>
                    @if ($permission->pivot->can_delete)
                    <span class="iconify lucide--badge-check text-success size-4.5"></span>
                    @else
                    <span class="iconify lucide--badge-x text-error size-4.5"></span>
                    @endif
                  </td>
                  <td>
                    <div class="inline-flex w-fit">
                      @if (hasPermission(6, 'can_update'))
                      <button class="btn btn-square btn-primary btn-outline btn-xs border-transparent" onclick="openEditRolePermissionModal({{ $role->id }}, {{ $permission }})">
                        <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
                      </button>
                      @endif
                      @if (hasPermission(6, 'can_delete'))
                      <button class="btn btn-square btn-error btn-outline btn-xs border-transparent" onclick="openRemoveRolePermissionModal({{ $role->id }}, {{ $permission->id }})">
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
          <div class="flex items-center gap-2 btn-box">
            @if (hasPermission(6, 'can_update'))
            <button class="btn btn-soft btn-primary" aria-label="Icon" style="height: 28px; padding: 6px;" onclick="openEditModal({{ $role }});">
              <span class="iconify lucide--pencil size-4"></span>
            </button>
            @endif
            @if (hasPermission(6, 'can_delete'))
            <button class="btn btn-soft btn-error" aria-label="Icon" style="height: 28px; padding: 6px;" onclick="openDeleteModal({{ $role->id }});">
              <span class="iconify lucide--trash size-4"></span>
            </button>
            @endif
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
<dialog id="role_modal" class="modal">
  <div class="modal-box">
    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute top-2 right-2">
        <span class="iconify lucide--x size-4"></span>
      </button>
    </form>
    <form id="save_form" method="POST" >
      @csrf
      <h3 class="text-lg font-medium" id="title">Add Role</h3>
      <input type="hidden" name="id" value="" />
      <fieldset class="fieldset mt-4">
        <p class="fieldset-label">Title *</p>
        <input class="input w-full" aria-label="Input" placeholder="Type title here" type="text" name="title" required/>
      </fieldset>
      <fieldset class="fieldset">
        <p class="fieldset-label">Description</p>
        <textarea aria-label="Textarea" class="textarea w-full" placeholder="Type description here" name="description"></textarea>
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
      Delete Role
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4">
      You are about to delete this role. Would you like to proceed further?
    </p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <form id="delete_form" method="POST" action="{{ route('delete-role') }}">
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
<dialog id="role_permission_modal" class="modal">
  <div class="modal-box">
    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute top-2 right-2">
        <span class="iconify lucide--x size-4"></span>
      </button>
    </form>
    <form id="role_permission_form" method="POST">
      @csrf
      <h3 class="text-lg font-medium" id="role_permission_form_title">Add Permission</h3>
      <input type="hidden" name="role_id" value="" />
      <fieldset class="fieldset mt-4">
        <p class="fieldset-label">Permissions *</p>
        <select class="select w-full" name="permission" required>
          <option value="" selected hidden>Choose Permission</option>
          @foreach ($permissions as $permission)
          <option value="{{ $permission->id }}">{{ $permission->title }}</option>
          @endforeach
        </select>
      </fieldset>
      <div class="flex items-center justify-between mt-4" id="crud_checkboxes_container">
        <label class="label flex">
          <input class="checkbox checkbox-sm" aria-label="Checkbox example" type="checkbox" name="can_create" />
          <span class="label-text cursor-pointer text-sm">
            Create
          </span>
        </label>
        <label class="label flex">
          <input class="checkbox checkbox-sm" aria-label="Checkbox example" type="checkbox" name="can_read" />
          <span class="label-text cursor-pointer text-sm">
            Read
          </span>
        </label>
        <label class="label flex">
          <input class="checkbox checkbox-sm" aria-label="Checkbox example" type="checkbox" name="can_update" />
          <span class="label-text cursor-pointer text-sm">
            Update
          </span>
        </label>
        <label class="label flex">
          <input class="checkbox checkbox-sm" aria-label="Checkbox example" type="checkbox" name="can_delete" />
          <span class="label-text cursor-pointer text-sm">
            Delete
          </span>
        </label>
      </div>
      <div class="modal-action">
        <button class="btn btn-primary" type="submit">Save</button>
      </div>
    </form>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>
<dialog id="remove_role_permission_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium">
      Remove Permission from Role
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4">
      You are about to remove this permission from the role. Would you like to proceed further?
    </p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <form id="remove_role_permission_form" method="POST" action="{{ route('delete-role-permission') }}">
        @csrf
        <input type="hidden" name="remove_role_id" value="" />
        <input type="hidden" name="remove_permission_id" value="" />
        <button class="btn btn-error">Remove</button>
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
    $('#save_form #title').text('Add Role');
    $('#save_form input[name=id]').val('');
    $('#save_form input[name=title]').val('');
    $('#save_form textarea[name=description]').val('');
    $('#save_form').attr('action', "{{ route('create-role') }}")
    role_modal.showModal();
  }

  function openEditModal(role) {
    $('#save_form #title').text('Edit Role');
    $('#save_form input[name=id]').val(role.id);
    $('#save_form input[name=title]').val(role.title);
    $('#save_form textarea[name=description]').val(role.description);
    $('#save_form').attr('action', "{{ route('update-role') }}")
    role_modal.showModal();
  }

  function openDeleteModal(id) {
    $('#delete_form input[name=id]').val(id);
    delete_modal.showModal();
  }

  function handleSearch(event) {
    if (event.key === 'Enter') {
      const searchValue = event.target.value;
      const url = `/roles?search=${encodeURIComponent(searchValue)}`;
      window.location.href = url;
    }
  }

  const disabledPermissions = ['Boardings', 'Daycares', 'Groomings', 'Private Trainings', 'Group Classes', 'A La Carte'];

  function openAddRolePermissionModal(roleId) {
    $('#role_permission_form')[0].reset();
    $('#role_permission_form #role_permission_form_title').text('Add Permission To Role');
    
    $('#role_permission_form input[name=role_id]').val(roleId);
    
    $('#role_permission_form select[name=permission]').val('').trigger('change');
    
    $('#role_permission_form input[name=can_create]').prop('checked', false);
    $('#role_permission_form input[name=can_read]').prop('checked', false);
    $('#role_permission_form input[name=can_update]').prop('checked', false);
    $('#role_permission_form input[name=can_delete]').prop('checked', false);
    
    $('#crud_checkboxes_container').show();
    
    $('#role_permission_form').attr('action', "{{ route('create-role-permission') }}");
    
    role_permission_modal.showModal();
  }

  // Handle permission selection change
  $(document).on('change', '#role_permission_form select[name=permission]', function() {
    const selectedOption = $(this).find('option:selected');
    const permissionTitle = selectedOption.text();
    
    if (disabledPermissions.includes(permissionTitle)) {
      // Hide CRUD checkboxes for these specific permissions
      $('#crud_checkboxes_container').hide();
      // But internally check them (will be set on form submit)
      $('#role_permission_form input[name=can_create]').prop('checked', true);
      $('#role_permission_form input[name=can_read]').prop('checked', true);
      $('#role_permission_form input[name=can_update]').prop('checked', true);
      $('#role_permission_form input[name=can_delete]').prop('checked', true);
    } else {
      // Show CRUD checkboxes for other permissions
      $('#crud_checkboxes_container').show();
    }
  });

  // Ensure checkboxes are checked before form submission for disabled permissions
  $(document).on('submit', '#role_permission_form', function(e) {
    const selectedOption = $('#role_permission_form select[name=permission] option:selected');
    const permissionTitle = selectedOption.text();
    
    if (disabledPermissions.includes(permissionTitle)) {
      // Ensure all CRUD checkboxes are checked before submission
      $('#role_permission_form input[name=can_create]').prop('checked', true);
      $('#role_permission_form input[name=can_read]').prop('checked', true);
      $('#role_permission_form input[name=can_update]').prop('checked', true);
      $('#role_permission_form input[name=can_delete]').prop('checked', true);
    }
  });

  $(document).on('change', '#role_permission_form input[name=can_create], #role_permission_form input[name=can_update], #role_permission_form input[name=can_delete]', function() {
    if ($(this).is(':checked')) {
      $('#role_permission_form input[name=can_read]').prop('checked', true);
    }
  });

  $(document).on('change', '#role_permission_form input[name=can_read]', function() {
    if (!$(this).is(':checked')) {
      $('#role_permission_form input[name=can_create]').prop('checked', false);
      $('#role_permission_form input[name=can_update]').prop('checked', false);
      $('#role_permission_form input[name=can_delete]').prop('checked', false);
    }
  });

  function openEditRolePermissionModal(roleId, permission) {
    $('#role_permission_form')[0].reset();
    
    $('#role_permission_form #role_permission_form_title').text('Edit Permission For Role');
    
    $('#role_permission_form input[name=role_id]').val(roleId);
    
    $('#role_permission_form select[name=permission]').val(permission.id).trigger('change');
    
    if (disabledPermissions.includes(permission.title)) {
      $('#crud_checkboxes_container').hide();
      // But internally check them
      $('#role_permission_form input[name=can_create]').prop('checked', true);
      $('#role_permission_form input[name=can_read]').prop('checked', true);
      $('#role_permission_form input[name=can_update]').prop('checked', true);
      $('#role_permission_form input[name=can_delete]').prop('checked', true);
    } else {
      $('#crud_checkboxes_container').show();
      $('#role_permission_form input[name=can_create]').prop('checked', permission.pivot.can_create);
      $('#role_permission_form input[name=can_read]').prop('checked', permission.pivot.can_read);
      $('#role_permission_form input[name=can_update]').prop('checked', permission.pivot.can_update);
      $('#role_permission_form input[name=can_delete]').prop('checked', permission.pivot.can_delete);
    }
    
    $('#role_permission_form').attr('action', "{{ route('update-role-permission') }}");
    
    role_permission_modal.showModal();
  }
  
  $('#role_permission_modal').on('close', function() {
    resetRolePermissionForm();
  });
  
  $(document).on('click', '#role_permission_modal form[method="dialog"] button, #role_permission_modal .modal-backdrop button', function() {
    setTimeout(function() {
      if (!$('#role_permission_modal')[0].open) {
        resetRolePermissionForm();
      }
    }, 100);
  });
  
  function resetRolePermissionForm() {
    $('#role_permission_form')[0].reset();
    $('#role_permission_form select[name=permission]').val('').trigger('change');
    $('#crud_checkboxes_container').show();
    $('#role_permission_form input[name=can_create]').prop('checked', false);
    $('#role_permission_form input[name=can_read]').prop('checked', false);
    $('#role_permission_form input[name=can_update]').prop('checked', false);
    $('#role_permission_form input[name=can_delete]').prop('checked', false);
    $('#role_permission_form input[name=role_id]').val('');
  }

  function openRemoveRolePermissionModal(roleId, permissionId) {
    $('#remove_role_permission_form input[name=remove_role_id]').val(roleId);
    $('#remove_role_permission_form input[name=remove_permission_id]').val(permissionId);
    remove_role_permission_modal.showModal();
  }
</script>
@endsection