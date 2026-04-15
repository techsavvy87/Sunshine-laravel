@extends('layouts.main')
@section('title', 'Holidays')

@section('page-css')
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Holidays Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Holidays</li>
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
            <input class="w-24 sm:w-36" placeholder="Search holidays" aria-label="Search holidays" type="search" onkeydown="handleSearch(event)"/>
          </label>
        </div>
        @if (hasPermission(7, 'can_create'))
        <a aria-label="Create holiday link" class="btn btn-primary btn-sm max-sm:btn-square" href="{{ route('add-holiday') }}">
          <span class="iconify lucide--plus size-4"></span>
          <span class="hidden sm:inline">New Holiday</span>
        </a>
        @endif
      </div>
      <div class="mt-4 space-y-1" id="holiday_list">
        @foreach ($holidays as $month=>$holiday)
        <div class="rounded-box collapse border border-base-300">
          <input aria-label="Collapse trigger" type="checkbox" name="accordion-multiple" style="min-height: 0px" @if ($loop->first) checked @endif/>
          <div class="collapse-title text-md font-medium collapse-title-custom">
            <span>{{ $month }}</span>
          </div>
          <div class="collapse-content">
            <div class="overflow-x-auto">
              <table class="table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Percent <br>Increase</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($holiday as $index => $item)
                  <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->date->format('Y-m-d') }}</td>
                    <td>{{ $item->percent_increase }}%</td>
                    <td>
                      <div class="inline-flex w-fit">
                        @if (hasPermission(7, 'can_update'))
                        <a class="btn btn-square btn-primary btn-outline btn-xs border-transparent" href="{{ route('edit-holiday', ['id' => $item->id]) }}">
                          <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
                        </a>
                        @endif
                        @if (hasPermission(7, 'can_delete'))
                        <button class="btn btn-square btn-error btn-outline btn-xs border-transparent" onclick="openDeleteModal({{ $item->id }})">
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
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
<dialog id="delete_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium">
      Delete Holiday
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4">
      You are about to delete this holiday. Would you like to proceed further?
    </p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <form id="delete_form" method="POST" action="{{ route('delete-holiday') }}">
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
  const holidays = @json($holidays);
  const services = @json($services);
  const delete_modal = document.getElementById('delete_modal');

  function handleSearch(event) {
    if (event.key === 'Enter') {
      const query = event.target.value.toLowerCase();
      const rows = document.querySelectorAll('tbody tr');
      rows.forEach(row => {
        const name = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
        const date = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        if (name.includes(query) || date.includes(query)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }
  }

  function openDeleteModal(id) {
    $('#delete_form input[name="id"]').val(id);
    delete_modal.showModal();
  }
</script>
@endsection
