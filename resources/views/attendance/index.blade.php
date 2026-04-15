@extends('layouts.main')
@section('title', 'Attendance')

@section('page-css')
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Attendance</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('users') }}">Users</a></li>
      <li>Attendance</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="card bg-base-100 shadow mt-3">
    <div class="card-body p-0">
      <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-5">
        <div class="inline-flex items-center gap-2">
          <a href="{{ route('attendance', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}" class="btn btn-ghost btn-sm btn-square" aria-label="Previous day">
            <span class="iconify lucide--chevron-left size-4"></span>
          </a>
          <form method="GET" action="{{ route('attendance') }}" class="inline-flex items-center">
            <input type="date" name="date" id="attendance_date" class="input input-bordered input-sm w-40" value="{{ $date->format('Y-m-d') }}" aria-label="Select date" />
          </form>
          <a href="{{ route('attendance', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}" class="btn btn-ghost btn-sm btn-square" aria-label="Next day">
            <span class="iconify lucide--chevron-right size-4"></span>
          </a>
        </div>
      </div>
      <div class="overflow-x-auto mt-4">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>User</th>
              <th class="text-center">Attendance</th>
              <th class="text-center">Injury / Sickness</th>
              <th>Issue</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($records as $index => $record)
            <tr class="hover:bg-base-200/40 attendance-row" data-id="{{ $record->id }}">
              <td class="font-medium">{{ $index + 1 }}</td>
              <td>
                <div class="flex items-center gap-3">
                  @if ($record->user && $record->user->profile && $record->user->profile->avatar_img)
                  <img src="{{ asset('storage/profiles/'. $record->user->profile->avatar_img) }}" alt="" class="mask mask-squircle bg-base-200 size-10" />
                  @else
                  <img src="{{ asset('images/default-user-avatar.png') }}" alt="" class="mask mask-squircle bg-base-200 size-10" />
                  @endif
                  <div>
                    @if ($record->user && $record->user->profile)
                    <p class="font-medium">{{ $record->user->profile->first_name }} {{ $record->user->profile->last_name }}</p>
                    @else
                    <p class="font-medium">{{ $record->user->name ?? '—' }}</p>
                    @endif
                  </div>
                </div>
              </td>
              <td class="text-center">
                @if (hasPermission(5, 'can_update'))
                <input type="checkbox" class="checkbox checkbox-sm checkbox-primary attendance-present" data-id="{{ $record->id }}" {{ $record->present ? 'checked' : '' }} aria-label="Attendance" />
                @else
                {{ $record->present ? 'Yes' : 'No' }}
                @endif
              </td>
              <td class="text-center">
                @if (hasPermission(5, 'can_update'))
                <input type="checkbox" class="checkbox checkbox-sm checkbox-primary attendance-injury" data-id="{{ $record->id }}" {{ $record->injury_sickness ? 'checked' : '' }} aria-label="Injury/Sickness" />
                @else
                {{ $record->injury_sickness ? 'Yes' : 'No' }}
                @endif
              </td>
              <td>
                @if (hasPermission(5, 'can_update'))
                <input type="text" class="input input-bordered input-sm w-full attendance-issue" data-id="{{ $record->id }}" value="{{ $record->notes ?? '' }}" placeholder="Issue" />
                @else
                {{ $record->notes ?? '—' }}
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @if (hasPermission(5, 'can_update'))
      <div class="px-5 pb-4 pt-2 flex justify-end">
        <button type="button" id="attendance_save_btn" class="btn btn-primary btn-sm">
          <span class="iconify lucide--check size-4"></span>
          Save
        </button>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection

@section('page-js')
<script>
  $(function() {
    $('#attendance_date').on('change', function() {
      $(this).closest('form').submit();
    });

    $('#attendance_save_btn').on('click', function() {
      var $btn = $(this);
      var rows = [];
      $('.attendance-row').each(function() {
        var $tr = $(this);
        var id = $tr.data('id');
        var $present = $tr.find('.attendance-present');
        var $injury = $tr.find('.attendance-injury');
        var $issue = $tr.find('.attendance-issue');
        rows.push({
          id: id,
          present: $present.length ? $present.is(':checked') : true,
          injury_sickness: $injury.length ? $injury.is(':checked') : false,
          notes: $issue.length ? $issue.val() : ''
        });
      });

      function showMessage(msg, isError) {
        msg = msg || (isError ? 'Failed to save.' : 'Attendance saved.');
        if (isError) {
          $('#alert_message').text(msg);
          $('#alert_modal')[0].showModal();
        } else {
          $('#success_message').text(msg);
          $('#success_modal')[0].showModal();
        }
      }

      $btn.prop('disabled', true);
      $.ajax({
        url: '{{ route("update-attendance") }}',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ rows: rows }),
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .done(function(data) {
        $btn.prop('disabled', false);
        showMessage(data.message || 'Attendance saved.', false);
      })
      .fail(function() {
        $btn.prop('disabled', false);
        showMessage('Failed to save.', true);
      });
    });
  });
</script>
@endsection
