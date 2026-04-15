@extends('layouts.main')
@section('title', 'Boarding Process Log')

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
  <h3 class="text-lg font-medium">Boarding Process Log</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Boarding Process Log</li>
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
            <input class="w-24 sm:w-36" placeholder="Search logs" aria-label="Search logs" type="search" onkeydown="handleSearch(event)" value="{{ request('search') }}"/>
          </label>
        </div>
        @if (hasPermission(23, 'can_create'))
        <a aria-label="Create process log link" class="btn btn-primary btn-sm max-sm:btn-square" href="{{ route('boarding-process-log-create') }}">
          <span class="iconify lucide--plus size-4"></span>
          <span class="hidden sm:inline">Create Process Log</span>
        </a>
        @endif
      </div>
      @if($groupedProcesses->count() > 0)
      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th style="text-align:center">Pet Number</th>
              <th style="text-align:center">Start Time</th>
              <th style="text-align:center">End Time</th>
              <th style="text-align:center">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($groupedProcesses as $group)
              <tr class="hover:bg-base-200/40 *:text-nowrap">
                <td>{{ \Carbon\Carbon::parse($group['date'])->format('M d, Y') }}</td>
                <td style="text-align:center">
                  <button type="button" 
                          class="btn btn-link btn-sm p-0 min-h-0 h-auto text-primary" 
                          onclick="showPetsModal({{ $loop->index }})"
                          title="View pets">
                    {{ count($group['processes']) }}
                  </button>
                </td>
                <td style="text-align:center">
                  @if(!empty($group['earliest_time']))
                    {{ \Carbon\Carbon::createFromFormat('H:i:s', $group['earliest_time'])->format('h:i A') }}
                  @else
                    N/A
                  @endif
                </td>
                <td style="text-align:center">
                  @if(!empty($group['latest_time']))
                    {{ \Carbon\Carbon::createFromFormat('H:i:s', $group['latest_time'])->format('h:i A') }}
                  @else
                    N/A
                  @endif
                </td>
                <td style="text-align:center">
                  <div class="flex items-center justify-center gap-2">
                    @if (hasPermission(23, 'can_update'))
                    <a href="{{ route('boarding-process-log-edit', $group['processes'][0]['id']) }}" 
                       class="btn btn-square btn-primary btn-outline btn-sm border-transparent" 
                       title="Edit Process Log">
                      <span class="iconify lucide--pencil size-4"></span>
                    </a>
                    @endif
                    @if (hasPermission(23, 'can_delete'))
                    <button type="button"
                            class="btn btn-square btn-error btn-outline btn-sm border-transparent"
                            title="Delete Process Log"
                            onclick="confirmDeleteProcessLog({{ $group['processes'][0]['id'] }})">
                      <span class="iconify lucide--trash-2 size-4"></span>
                    </button>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <div class="text-center py-8">
        <p class="text-base-content/70">No boarding appointments on property found.</p>
      </div>
      @endif
    </div>
  </div>
</div>

<dialog id="pets_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium mb-4">
      <span>Pets in Boarding</span>
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <div id="pets_modal_content" class="space-y-2">
    </div>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-sm">Close</button>
      </form>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>

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
    <p class="py-4" id="delete_modal_message">
      You are about to delete this process log. This action cannot be undone. Would you like to proceed?
    </p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost btn-sm">No</button>
      </form>
      <form id="delete_form" method="POST" action="{{ route('boarding-process-log-delete', 0) }}">
        @csrf
        @method('DELETE')
        <button class="btn btn-error btn-sm">Delete</button>
      </form>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>

@php
  $petsData = [];
  foreach($groupedProcesses as $index => $group) {
    $petsData[$index] = [];
    foreach($group['processes'] as $process) {
      $petsData[$index][] = [
        'pet_name' => $process['pet_name'],
        'customer_name' => $process['customer_name'],
        'appointment_id' => $process['appointment_id']
      ];
    }
  }
@endphp
@endsection

@section('page-js')
<script>
  const petsData = @json($petsData);
  const petsModal = document.getElementById('pets_modal');
  const delete_modal = document.getElementById('delete_modal');

  function handleSearch(event) {
    if (event.key === 'Enter') {
      const searchValue = event.target.value;
      const url = `{{ route('boarding-process-log') }}?search=${encodeURIComponent(searchValue)}`;
      window.location.href = url;
    }
  }

  function showPetsModal(groupIndex) {
    const pets = petsData[groupIndex] || [];
    const content = document.getElementById('pets_modal_content');
    const baseUrl = '{{ route("appointment-dashboard", 0) }}'.replace('/0', '');
    
    if (pets.length === 0) {
      content.innerHTML = '<p class="text-base-content/70 text-center py-4">No pets found.</p>';
    } else {
      let html = '<div class="space-y-2">';
      pets.forEach(function(pet) {
        const appointmentUrl = baseUrl + '/' + pet.appointment_id;
        html += `
          <a href="${appointmentUrl}" 
             class="block p-3 border border-base-300 rounded-box hover:bg-base-200/40 transition-colors">
            <div class="font-medium text-sm">${pet.pet_name || 'N/A'}</div>
            <div class="text-xs text-base-content/70 mt-1">${pet.customer_name || 'N/A'}</div>
          </a>
        `;
      });
      html += '</div>';
      content.innerHTML = html;
    }
    
    petsModal.showModal();
  }

  function confirmDeleteProcessLog(processId) {
    const deleteForm = document.getElementById('delete_form');
    if (deleteForm) {
      const baseAction = '{{ route("boarding-process-log-delete", 0) }}';
      deleteForm.action = baseAction.replace('/0/delete', '/' + processId + '/delete');
    }

    if (delete_modal) {
      delete_modal.showModal();
    }
  }
</script>
@endsection
