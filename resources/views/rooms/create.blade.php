@extends('layouts.main')
@section('title', 'Create Room')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('src/libs/filepond/filepond.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('src/libs/filepond/filepond-plugin-image-preview.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('src/libs/select2/select2.min.css') }}" />
  <style>
    .select2-container--default .select2-selection--multiple {
      min-height: 40px;
      overflow-y: auto;
      overflow-x: hidden !important;
      white-space: normal !important;
      border-color: hsl(var(--bc) / 0.2);
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
      margin-top: 8px !important;
      margin-left: 8px !important;
    }

    .select2-container .select2-search--inline .select2-search__field {
      margin-top: 8px !important;
      margin-left: 8px !important;
    }

    .select2-container {
      width: 100% !important;
      min-width: 0 !important;
    }
  </style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Create Room</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">Sunshine</a></li>
      <li><a href="{{ route('rooms') }}">Rooms</a></li>
      <li class="opacity-80">Create</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  @php
    $selectedKennelIds = array_map('intval', old('kennel_ids', []));
    $selectedRoomType = old('room_type', 'standard');
    $selectedPetLabels = old('pet_type_labels', []);
  @endphp
  <form action="{{ route('create-room') }}" method="POST" enctype="multipart/form-data" id="create_form">
    @csrf
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-4 mt-3">
      <div class="xl:col-span-1">
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <div class="card-title">Upload Room Image</div>
            <div class="mt-4">
              <input type="file" data-filepond class="uploadFile" name="img"/>
              <input type="hidden" id="temp_file" name="temp_file" value="{{ old('temp_file') }}" />
            </div>
          </div>
        </div>
      </div>
      <div class="xl:col-span-3">
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <div class="card-title">Basic Information</div>
            <div class="fieldset mt-2 grid grid-cols-1 gap-4 xl:grid-cols-4">
              <div class="space-y-2">
                <label class="fieldset-label" for="name">Room Name*</label>
                <label class="input w-full focus:outline-0">
                  <input class="grow focus:outline-0" placeholder="e.g. Boarding Suite A" id="name" name="name" type="text" value="{{ old('name') }}" />
                </label>
              </div>
              <div class="space-y-2">
                <label class="fieldset-label">Room Type*</label>
                <div class="flex flex-wrap gap-4 rounded-box border border-base-300 px-3 py-2">
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input class="radio radio-sm" type="radio" name="room_type" value="standard" id="room_type_standard" {{ $selectedRoomType === 'standard' ? 'checked' : '' }} />
                    <span>Standard</span>
                  </label>
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input class="radio radio-sm" type="radio" name="room_type" value="space" id="room_type_space" {{ $selectedRoomType === 'space' ? 'checked' : '' }} />
                    <span>Space</span>
                  </label>
                </div>
              </div>
              <div class="space-y-2">
                <label class="fieldset-label" for="status">Status*</label>
                <select class="select w-full" name="status" id="status">
                  <option value="Available" {{ old('status', 'Available') === 'Available' ? 'selected' : '' }}>Available</option>
                  <option value="Out of Service" {{ old('status', 'Out of Service') === 'Out of Service' ? 'selected' : '' }}>Out of Service</option>
                  <option value="Blocked" {{ old('status') === 'Blocked' ? 'selected' : '' }}>Blocked</option>
                  <option value="Maintenance" {{ old('status') === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
              </div>
              <div id="kennel_ids_wrapper" class="space-y-2 {{ $selectedRoomType === 'standard' ? '' : 'hidden' }}">
                <label class="fieldset-label" for="kennel_ids">Assigned Kennels</label>
                <select class="select w-full" name="kennel_ids[]" id="kennel_ids" multiple>
                  @foreach ($kennels as $kennel)
                  <option value="{{ $kennel->id }}" {{ in_array($kennel->id, $selectedKennelIds) ? 'selected' : '' }}>
                    {{ $kennel->name }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div id="space_option_wrapper" class="space-y-2 xl:col-span-2 {{ $selectedRoomType === 'space' ? '' : 'hidden' }}">
                <label class="fieldset-label">Space Option*</label>
                <div class="flex flex-wrap gap-4 rounded-box border border-base-300 px-3 py-2">
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input class="radio radio-sm" type="radio" name="space_option" value="restrict" {{ old('space_option') === 'restrict' ? 'checked' : '' }} />
                    <span>Restrict</span>
                  </label>
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input class="radio radio-sm" type="radio" name="space_option" value="multi" {{ old('space_option') === 'multi' ? 'checked' : '' }} />
                    <span>Multi</span>
                  </label>
                </div>
              </div>
              <div id="restrict_count_wrapper" class="space-y-2 xl:col-span-2 {{ old('space_option') === 'restrict' && $selectedRoomType === 'space' ? '' : 'hidden' }}">
                <label class="fieldset-label" for="restrict_count">Restrict Count*</label>
                <label class="input w-full focus:outline-0">
                  <input class="grow focus:outline-0" type="number" min="1" step="1" id="restrict_count" name="restrict_count" value="{{ old('restrict_count') }}" placeholder="Enter max count" />
                </label>
              </div>
              <div id="pet_type_labels_wrapper" class="space-y-2 xl:col-span-2 {{ $selectedRoomType === 'space' ? '' : 'hidden' }}">
                <label class="fieldset-label">Pet Type Labels</label>
                <div class="flex flex-wrap gap-4 rounded-box border border-base-300 px-3 py-2">
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input class="checkbox checkbox-sm" type="checkbox" name="pet_type_labels[]" value="dog" {{ in_array('dog', $selectedPetLabels) ? 'checked' : '' }} />
                    <span>Dog</span>
                  </label>
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input class="checkbox checkbox-sm" type="checkbox" name="pet_type_labels[]" value="cat" {{ in_array('cat', $selectedPetLabels) ? 'checked' : '' }} />
                    <span>Cat</span>
                  </label>
                </div>
              </div>
              <div class="space-y-2 xl:col-span-4">
                <label class="fieldset-label" for="description">Description</label>
                <textarea class="textarea w-full min-h-24" placeholder="Description" name="description" id="description">{{ old('description') }}</textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-6 flex justify-end gap-3">
      <a class="btn btn-sm btn-ghost" href="{{ route('rooms') }}">
        <span class="iconify lucide--x size-4"></span>
        Cancel
      </a>
      <button class="btn btn-sm btn-primary" type="submit">
        <span class="iconify lucide--check size-4"></span>
        Save
      </button>
    </div>
  </form>
</div>
@endsection

@section('page-js')
  <script src="{{ asset('src/libs/filepond/filepond.min.js') }}"></script>
  <script src="{{ asset('src/libs/filepond/filepond-plugin-image-preview.min.js') }}"></script>
  <script src="{{ asset('src/libs/select2/select2.min.js') }}"></script>

  <script>
    const alert_modal = document.getElementById('alert_modal') || null;
    const pageAlertMessage = @json(session('status') === 'fail' ? session('message') : ($errors->any() ? $errors->first() : ''));

    if (pageAlertMessage && alert_modal) {
      $('#alert_message').text(pageAlertMessage);
      alert_modal.showModal();
    }

    $(document).ready(function() {
      $('#kennel_ids').select2({
        placeholder: 'Select kennels',
        allowClear: true,
        multiple: true,
        width: '100%',
        closeOnSelect: false,
      });

      const syncRoomTypeSections = () => {
        const roomType = $('input[name="room_type"]:checked').val() || 'standard';
        const isStandard = roomType === 'standard';
        const isSpace = roomType === 'space';

        $('#kennel_ids_wrapper').toggleClass('hidden', !isStandard);
        $('#kennel_ids').prop('disabled', !isStandard).trigger('change.select2');

        $('#space_option_wrapper').toggleClass('hidden', !isSpace);
        $('#pet_type_labels_wrapper').toggleClass('hidden', !isSpace);
        if (!isSpace) {
          $('input[name="space_option"]').prop('checked', false);
          $('input[name="pet_type_labels[]"]').prop('checked', false);
          $('#restrict_count').val('');
          $('#restrict_count_wrapper').addClass('hidden');
        }

        if (!isStandard) {
          $('#kennel_ids').val(null).trigger('change');
        }
      };

      const syncRestrictCount = () => {
        const isSpace = ($('input[name="room_type"]:checked').val() || 'standard') === 'space';
        const isRestrict = $('input[name="space_option"]:checked').val() === 'restrict';
        $('#restrict_count_wrapper').toggleClass('hidden', !(isSpace && isRestrict));

        if (!(isSpace && isRestrict)) {
          $('#restrict_count').val('');
        }
      };

      $('input[name="room_type"]').on('change', function() {
        syncRoomTypeSections();
        syncRestrictCount();
      });
      $('input[name="space_option"]').on('change', syncRestrictCount);
      syncRoomTypeSections();
      syncRestrictCount();

      $('#create_form').on('submit', function(event) {
        const roomType = $('input[name="room_type"]:checked').val();
        const isSpace = roomType === 'space';

        if (isSpace && !$('input[name="space_option"]:checked').length) {
          event.preventDefault();
          $('#alert_message').text('Please choose Restrict or Multi when Space is selected.');
          alert_modal.showModal();
          return;
        }

        if (isSpace && $('input[name="space_option"]:checked').val() === 'restrict' && !$('#restrict_count').val()) {
          event.preventDefault();
          $('#alert_message').text('Please enter Restrict count when Restrict is selected.');
          alert_modal.showModal();
          return;
        }
      });
    });

    FilePond.registerPlugin(FilePondPluginImagePreview);

    const inputElement = document.querySelector('input[type="file"][data-filepond]');
    if (inputElement) {
      FilePond.create(inputElement, {
        acceptedFileTypes: ['image/*'],
        allowImagePreview: true,
        allowImageFilter: false,
        allowImageExifOrientation: false,
        allowImageCrop: false,
        imagePreviewHeight: 170,
        imageCropAspectRatio: '1:1',
        imageResizeTargetWidth: 200,
        imageResizeTargetHeight: 200,
        stylePanelLayout: 'compact',
        styleLoadIndicatorPosition: 'center bottom',
        styleProgressIndicatorPosition: 'right bottom',
        styleButtonRemoveItemPosition: 'left bottom',
        styleButtonProcessItemPosition: 'right bottom',
        beforeAddFile: (item) => {
          const file = item.file;

          if (file.size > 1024 * 1024 * 2) {
            $('#alert_message').text('The size of image should be smaller than 2M.');
            alert_modal.showModal();
            return false;
          }

          if (!file.type.startsWith('image/')) {
            $('#alert_message').text('Uploaded file must be an image.');
            alert_modal.showModal();
            return false;
          }

          return true;
        },
        server: {
          process: {
            url: '{{ route("process-file-room") }}',
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            onload: (response) => {
              const result = JSON.parse(response);
              $('#temp_file').val(result.temp_file);
              return result.temp_file;
            },
            onerror: () => {
              $('#alert_message').text('Error uploading image.');
              alert_modal.showModal();
              return '';
            }
          },
          revert: {
            url: '{{ route("revert-file-room") }}',
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
          }
        },
        onprocessfile: (error) => {
          if (error) {
            $('#alert_message').text('Error processing image.');
            alert_modal.showModal();
          }
        },
        onremovefile: (error) => {
          if (!error) {
            $('#temp_file').val('');
          }
        }
      });
    }
  </script>
@endsection
