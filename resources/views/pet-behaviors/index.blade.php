@extends('layouts.main')
@section('title', 'Capacities')

@section('page-css')
<style>

  .icon-picker-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 0.4rem;
    max-height: 11rem;
    overflow-y: auto;
    padding: 0.35rem;
    border: 1px solid oklch(var(--bc) / 0.2);
    border-radius: 0.5rem;
  }

  .icon-picker-dropdown {
    width: 100%;
  }

  .icon-picker-trigger {
    width: 100%;
    justify-content: space-between;
  }

  .icon-picker-panel {
    width: 100%;
    z-index: 30;
  }

  .icon-picker-item {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 2rem;
    border-radius: 0.4rem;
    border: 1px solid transparent;
    cursor: pointer;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Pet Behaviors Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Pet Behaviors</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="mt-3 grid grid-cols-1 gap-4 xl:grid-cols-5 2xl:grid-cols-10">
    <div class="xl:col-span-2 2xl:col-span-3">
      <div class="card bg-base-100 card-border">
        <div class="card-body">
          <div class="card-title" id="behavior_icon_form_title">@if (hasPermission(31, 'can_create')) Add Pet Behavior @else View Pet Behavior @endif</div>
          <input type="hidden" id="behavior_icon_id" value="">
          <div class="fieldset">
            <div class="space-y-1 mt-2">
              <label class="fieldset-label" for="icon">Icon*</label>
              <input type="hidden" id="icon" value="">
              <div class="dropdown icon-picker-dropdown" id="icon_dropdown">
                <label tabindex="0" class="btn btn-outline icon-picker-trigger" id="icon_trigger">
                  <span class="inline-flex items-center gap-2" id="icon_selected_preview">
                    <span class="text-base-content/60">Select Icon</span>
                  </span>
                  <span class="iconify lucide--chevron-down size-4"></span>
                </label>
                <div tabindex="0" class="dropdown-content card bg-base-100 shadow icon-picker-panel p-2">
                  <div class="icon-picker-grid" id="icon_picker">
                    @foreach ($icons as $icon)
                      <button
                        type="button"
                        class="icon-picker-item"
                        data-icon-id="{{ $icon->id }}"
                        title="Icon"
                      >
                        {!! $icon->icon !!}
                      </button>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
            <div class="space-y-1 mt-2">
              <label class="fieldset-label" for="description">Description</label>
              <textarea class="textarea w-full focus:outline-0" placeholder="Enter description..." id="description" rows="3"></textarea>
            </div>
          </div>
          <div class="mt-5 flex justify-end gap-3">
            @if (hasPermission(31, 'can_create') || hasPermission(31, 'can_update'))
            <button class="btn btn-ghost btn-sm" id="cancel_btn" onclick="cancelBehavior()">Cancel</button>
            <button class="btn btn-sm btn-primary gap-1" onclick="saveBehavior()" id="save_btn">
              <span class="loading loading-spinner size-3.5" style="display:none;"></span>
              Save
            </button>
            @endif
          </div>
        </div>
      </div>
    </div>
    <div class="xl:col-span-3 2xl:col-span-7">
      <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
          <div class="flex items-center justify-between px-5 pt-5">
            <div class="inline-flex items-center gap-3">
              <label class="input input-sm">
                <span class="iconify lucide--search text-base-content/80 size-3.5"></span>
                <input class="w-24 sm:w-36" placeholder="Search behaviors" aria-label="Search behaviors" type="search" onkeydown="handleSearch(event)"/>
              </label>
            </div>
          </div>
          <div class="mt-4">
            <table class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Icon</th>
                  <th style="text-align: left !important">Description</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="behavior_icon_list">
                @foreach ($behaviors as $behavior)
                <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
                  <td>{{ $loop->iteration }}</td>
                  <td>{!! $behavior->icon?->icon !!}</td>
                  <td>{{ $behavior->description }}</td>
                  <td>
                    <div class="inline-flex w-fit">
                      @if (hasPermission(31, 'can_update'))
                      <button class="btn btn-square btn-primary btn-outline btn-xs border-transparent" onclick="editBehavior({{ $behavior }})">
                        <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
                      </button>
                      @endif
                      @if (hasPermission(31, 'can_delete'))
                      <button class="btn btn-square btn-error btn-outline btn-xs border-transparent" onclick="openDeleteModal({{ $behavior->id }})">
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
    </div>
  </div>
</div>
<dialog id="delete_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium">
      Delete Pet Behavior
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4">
      You are about to delete this pet behavior. Would you like to proceed further?
    </p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <input type="hidden" id="delete_id" value="" />
      <button class="btn btn-error" onclick="deleteBehavior()">Delete</button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>
@endsection

@section('page-js')
<script>
  // Icon picker logic
  document.querySelectorAll('#icon_picker .icon-picker-item').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('#icon_picker .icon-picker-item.active').forEach(function(activeBtn) {
        activeBtn.classList.remove('active');
      });

      this.classList.add('active');
      document.getElementById('icon').value = this.getAttribute('data-icon-id') || '';
      setSelectedIconPreview(this.innerHTML);
      closeIconDropdown();
    });
  });

  function closeIconDropdown() {
    const dropdown = document.getElementById('icon_dropdown');
    if (!dropdown) {
      return;
    }

    dropdown.classList.remove('dropdown-open');

    const activeEl = document.activeElement;
    if (activeEl && dropdown.contains(activeEl)) {
      activeEl.blur();
    }

    dropdown.querySelectorAll('[tabindex="0"]').forEach(function(el) {
      el.blur();
    });
  }

  function setSelectedIconPreview(html) {
    const preview = document.getElementById('icon_selected_preview');
    preview.innerHTML = html;
  }
// End of icon picker logic

  function saveBehavior() {
    const behaviorId = $('#behavior_icon_id').val();
    const iconId = $('#icon').val();
    const description = $('#description').val();

    if (!iconId) {
      $('#alert_message').text('Please select the icon.');
      alert_modal.showModal();
      return;
    }

    if (!description) {
      $('#alert_message').text('Please enter the description.');
      alert_modal.showModal();
      return;
    }

    const data = {
      behavior_icon_id: behaviorId,
      icon_id: iconId,
      description: description
    };

    // show loading spinner in the 'save' button and diasable buttons
    $('#save_btn .loading').css('display', 'inline-block');
    $('#save_btn').prop('disabled', true);
    // Remove the original 'Save' text from the save button
    $('#save_btn').contents().filter(function() {
      return this.nodeType === 3 && this.nodeValue.trim() === 'Save';
    }).remove();
    // Add 'Loading' text to the save button
    $('#save_btn').append('Loading');
    $('#cancel_btn').prop('disabled', true);

    $.ajax({
      url: behaviorId ? '{{ route("update-behavior") }}' : '{{ route("create-behavior") }}',
      method: 'POST',
      data: data,
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      success: function(response) {
        $('#success_message').text(response.message);
        success_modal.showModal();

        $('#save_btn .loading').css('display', 'none');
        $('#save_btn').prop('disabled', false);
        // Remove 'Loading' text if present
        $('#save_btn').contents().filter(function() {
          return this.nodeType === 3 && this.nodeValue.trim() === 'Loading';
        }).remove();
        // Add 'Save' text if not present
        if ($('#save_btn').text().trim() === '') {
          $('#save_btn').append('Save');
        }
        $('#cancel_btn').prop('disabled', false);

        // Reset the form fields
        resetForm();

        // update the behavior list
        updateBehaviors(response.result);
      },
      error: function(xhr) {
        let msg = 'An error occurred. Please try again.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        $('#alert_message').text(msg);
        alert_modal.showModal();

        $('#save_btn .loading').css('display', 'none');
        $('#save_btn').prop('disabled', false);
        // Remove 'Loading' text if present
        $('#save_btn').contents().filter(function() {
          return this.nodeType === 3 && this.nodeValue.trim() === 'Loading';
        }).remove();
        // Add 'Save' text if not present
        if ($('#save_btn').text().trim() === '') {
          $('#save_btn').append('Save');
        }
        $('#cancel_btn').prop('disabled', false);
      }
    });
  }

  function resetForm() {
    $('#behavior_icon_id').val('');
    $('#icon').val('');
    $('#description').val('');
    $('#icon_picker .icon-picker-item').removeClass('active');
    setSelectedIconPreview('<span class="text-base-content/60">Select Icon</span>');

    $('#behavior_icon_form_title').text('Add Behavior');
  }

  function updateBehaviors(result) {
    const behaviorList = $('#behavior_icon_list');
    behaviorList.empty();

    $.each(result, function(index, behavior) {
      const row = `
        <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
          <td>${index + 1}</td>
          <td style="text-align: left">${behavior.icon ? behavior.icon.icon : ''}</td>
          <td>${behavior.description ?? ''}</td>
          <td>
            <div class="inline-flex w-fit">
              <button class="btn btn-square btn-primary btn-outline btn-xs border-transparent" onclick='editBehavior(${JSON.stringify(behavior)})'>
                <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
              </button>
              <button class="btn btn-square btn-error btn-outline btn-xs border-transparent" onclick='openDeleteModal(${behavior.id})'>
                <span class="iconify lucide--trash" style="font-size: 0.875rem;"></span>
              </button>
            </div>
          </td>
        </tr>`;
      behaviorList.append(row);
    });
  }

  function cancelBehavior() {
    resetForm();
    $('#behavior_icon_form_title').text('Add Behavior');
  }

  function editBehavior(behavior) {
    $('#behavior_icon_id').val(behavior.id);
    $('#icon').val(behavior.icon_id);
    $('#description').val(behavior.description || '');

    const selectedButton = document.querySelector('#icon_picker .icon-picker-item[data-icon-id="' + behavior.icon_id + '"]');
    $('#icon_picker .icon-picker-item').removeClass('active');
    if (selectedButton) {
      selectedButton.classList.add('active');
      setSelectedIconPreview(selectedButton.innerHTML);
    }
  }

  function openDeleteModal(id) {
    $('#delete_id').val(id);
    delete_modal.showModal();
  }

  function deleteBehavior() {
    delete_modal.close();

    const id = $('#delete_id').val();
    $.ajax({
      url: '{{ route("delete-behavior") }}',
      method: 'POST',
      data: { id: id },
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      success: function(response) {
        $('#success_message').text(response.message);
        success_modal.showModal();
        // update the behavior list
        updateBehaviors(response.result);
      },
      error: function(xhr) {
        let msg = 'An error occurred. Please try again.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        $('#alert_message').text(msg);
        alert_modal.showModal();
      }
    });
  }

  function handleSearch(event) {
    if (event.key === 'Enter') {
      const query = event.target.value.toLowerCase();
      $('#behavior_icon_list tr').each(function() {
        const row = $(this);
        const text = row.text().toLowerCase();
        row.toggle(text.includes(query));
      });
    }
  }
</script>
@endsection