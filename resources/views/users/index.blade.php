@extends('layouts.main')
@section('title', 'Users')

@section('page-css')
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Users Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Users</li>
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
        @if (hasPermission(5, 'can_create'))
        <a aria-label="Create seller link" class="btn btn-primary btn-sm max-sm:btn-square" href="{{ route('add-user') }}">
          <span class="iconify lucide--plus size-4"></span>
          <span class="hidden sm:inline">New User</span>
        </a>
        @endif
      </div>
      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Name</th>
              <th>Username</th>
              <th>Email</th>
              <th>Phone 1</th>
              <th>Phone 2</th>
              <th>Verified</th>
              <th>Status</th>
              <th>Block</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($users as $user)
            <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
              <td class="font-medium">{{ $loop->iteration }}</td>
              <td>
                <div class="flex items-center space-x-3 truncate">
                  @if (empty($user->profile) || empty($user->profile->avatar_img))
                  <img src="{{ asset('images/default-user-avatar.png') }}" alt="Seller Image" class="mask mask-squircle bg-base-200 size-10">
                  @else
                  <img src="{{ asset('storage/profiles/'. $user->profile->avatar_img) }}" alt="Seller Image" class="mask mask-squircle bg-base-200 size-10">
                  @endif
                  <div>
                    <p class="font-medium">{{ $user->profile ? $user->profile->first_name . ' ' . $user->profile->last_name : ''   }}</p>
                    <p class="text-base-content/60 text-xs capitalize">{{ $user->profile ? $user->profile->gender : '' }}</p>
                  </div>
                </div>
              </td>
              <td class="font-medium">{{ $user->name }}</td>
              <td>{{ $user->email }}</td>
              <td>{{ $user->profile ? $user->profile->phone_number_1 : '' }}</td>
              <td>{{ $user->profile ? $user->profile->phone_number_2 : '' }}</td>
              <td>
                @if ($user->email_verified_at)
                <span class="iconify lucide--badge-check text-success size-4.5"></span>
                @else
                <span class="iconify lucide--badge-x text-error size-4.5"></span>
                @endif
              </td>
              <td>
                @if ($user->status)
                <span class="iconify lucide--badge-check text-success size-4.5"></span>
                @else
                <span class="iconify lucide--badge-x text-error size-4.5"></span>
                @endif
              </td>
              <td>
                <div class="flex flex-wrap gap-1">
                  @if ($user->block_reservations)
                    <div class="badge badge-soft badge-warning badge-sm">Reservations</div>
                  @endif
                  @if ($user->block_messages)
                    <div class="badge badge-soft badge-secondary badge-sm">Messages</div>
                  @endif
                </div>
              </td>
              <td>
                <div class="inline-flex w-fit">
                  @if (hasPermission(5, 'can_update'))
                  <a aria-label="Edit seller link" class="btn btn-square btn-ghost btn-sm" href="{{ route('edit-user', ['id' => $user->id]) }}">
                    <span class="iconify lucide--pencil text-base-content/80 size-4"></span>
                  </a>
                  @endif
                  @if (hasPermission(5, 'can_delete'))
                  <button aria-label="Dummy delete seller" onclick="confirmDelete({{ $user }})" class="btn btn-square btn-error btn-outline btn-sm border-transparent">
                    <span class="iconify lucide--trash size-4"></span>
                  </button>
                  @endif
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      {{ $users->links('layouts.pagination', ['items' => $users]) }}
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
      <form id="delete_form" method="POST" action="{{ route('delete-user') }}">
        @csrf
        <input type="hidden" name="user_id" value="" />
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
      const url = `/users?search=${encodeURIComponent(searchValue)}`;
      window.location.href = url;
    }
  }
  function confirmDelete(user) {
    const message = `You are about to delete the user ${user.profile ? user.profile.first_name + ' ' + user.profile.last_name : ''}. Would you like to proceed?`;
    $('#delete_modal_message').text(message);
    $('#delete_form input[name=user_id]').val(user.id);
    delete_modal.showModal();
  }
</script>
@endsection