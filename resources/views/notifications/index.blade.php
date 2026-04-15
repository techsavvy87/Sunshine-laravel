@extends('layouts.main')
@section('title', 'Notifications')

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
  <h3 class="text-lg font-medium">Notifications</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Notifications</li>
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
      </div>
      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Title</th>
              <th>Message</th>
              <th>Sender</th>
              <th style="text-align:center">Read/Unread</th>
              <th style="text-align:center">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($notifications as $notification)
            @php
              $sender = $notification->sender;
              $senderProfile = $sender?->profile;
              $senderName = trim(($senderProfile->first_name ?? '') . ' ' . ($senderProfile->last_name ?? ''));
              $senderName = $senderName !== '' ? $senderName : ($sender?->name ?: 'System');
            @endphp
            <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap" onclick="openNotification({{ $notification->id }})">
              <td>{{ $loop->iteration }}</td>
              <td>{{ $notification->title }}</td>
              <td>{{ $notification->message }}</td>
              <td>
                <div class="flex items-center space-x-3 truncate">
                  @if (empty($senderProfile?->avatar_img))
                  <img src="{{ asset('images/default-user-avatar.png') }}" alt="Seller Image" class="rounded-full bg-base-200 size-7">
                  @else
                  <img src="{{ asset('storage/profiles/'. $senderProfile->avatar_img) }}" alt="Seller Image" class="rounded-full bg-base-200 size-7">
                  @endif
                  <div>
                    <p class="font-medium">{{ $senderName }}</p>
                  </div>
                </div>
              </td>
              <td style="text-align:center">
                <span class="badge badge-soft badge-success badge-sm {{ $notification->is_read ? '' : 'hidden' }}">Read</span>
                <span class="badge badge-soft badge-warning badge-sm {{ $notification->is_read ? 'hidden' : '' }}">Unread</span>
              </td>
              <td style="text-align:center">
                <div class="inline-flex w-fit">
                  <button onclick="event.stopPropagation(); changeStatus({{ $notification->id }})" class="btn btn-square btn-primary btn-outline btn-sm border-transparent">
                    <span class="iconify lucide--eye size-4 {{ $notification->is_read ? 'hidden' : '' }}"></span>
                    <span class="iconify lucide--eye-off size-4 {{ $notification->is_read ? '' : 'hidden' }}"></span>
                  </button>
                  <button onclick="event.stopPropagation(); confirmDelete({{ $notification }})" class="btn btn-square btn-error btn-outline btn-sm border-transparent">
                    <span class="iconify lucide--trash size-4"></span>
                  </button>
                </div>
              </td>
            </tr>
            @endforeach
          </thead>
        </table>
      </div>
      {{ $notifications->links('layouts.pagination', ['items' => $notifications]) }}
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
      <form id="delete_form" method="POST" action="{{ route('delete-notification') }}">
        @csrf
        <input type="hidden" name="notification_id" value="" />
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
  function openNotification(notificationId) {
    window.location.href = `/notification/open/${notificationId}`;
  }

  function handleSearch(event) {
    if (event.key === 'Enter') {
      const searchValue = event.target.value;
      const url = `/notifications?search=${encodeURIComponent(searchValue)}`;
      window.location.href = url;
    }
  }
  function confirmDelete(notification) {
    const message = `You are about to delete the notification "${notification.title}". Would you like to proceed?`;
    $('#delete_modal_message').text(message);
    $('#delete_form input[name=notification_id]').val(notification.id);
    delete_modal.showModal();
  }
  function changeStatus(notificationId) {
    $.ajax({
      url: `/notification/mark-read/${notificationId}`,
      method: 'GET',
      dataType: 'json',
      success: function(data) {
        var isRead = data.result.is_read;
        var $btn = $("[onclick='changeStatus(" + notificationId + ")']").first();
        var $row = $btn.closest('tr');
        var $readBadge = $row.find('.badge-success').first();
        var $unreadBadge = $row.find('.badge-warning').first();

        if (isRead) {
          $btn.find('.lucide--eye').addClass('hidden');
          $btn.find('.lucide--eye-off').removeClass('hidden');

          $readBadge.removeClass('hidden');
          $unreadBadge.addClass('hidden');
        } else {
          $btn.find('.lucide--eye').removeClass('hidden');
          $btn.find('.lucide--eye-off').addClass('hidden');

          $readBadge.addClass('hidden');
          $unreadBadge.removeClass('hidden');
        }
      },
      error: function(xhr) {
        console.error('Error marking notification as read:', xhr);
      }
    });
  }
</script>
@endsection