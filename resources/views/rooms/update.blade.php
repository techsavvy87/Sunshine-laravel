@extends('layouts.main')
@section('title', 'Update Room')

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
  <h3 class="text-lg font-medium">Update Room</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('rooms') }}">Rooms</a></li>
      <li class="opacity-80">Update</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  @php
    $selectedKennelIds = array_map('intval', old('kennel_ids', $room->kennel_id_array));
  @endphp
  <form action="{{ route('update-room') }}" method="POST" enctype="multipart/form-data" id="update_form">
    @csrf
    <input type="hidden" name="id" value="{{ $room->id }}" />
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-4 mt-3">
      <div class="xl:col-span-1">
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <div class="card-title">Upload Room Image</div>
            <div class="mt-4">
              <input type="file" data-filepond class="uploadFile" name="img"/>
              <input type="hidden" id="temp_file" name="temp_file" />
              <input type="hidden" id="img_action" name="img_action" value="keep" />
              <input type="hidden" id="current_img" name="current_img" value="{{ $room->img ?? '' }}" />
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
                  <input class="grow focus:outline-0" placeholder="e.g. Boarding Suite A" id="name" name="name" type="text" value="{{ old('name', $room->name) }}" />
                </label>
              </div>
              <div class="space-y-2">
                <label class="fieldset-label" for="type">Type*</label>
                <select class="select w-full" name="type" id="type">
                  <option value="dog" {{ old('type', $room->type) === 'dog' ? 'selected' : '' }}>Dog</option>
                  <option value="cat" {{ old('type', $room->type) === 'cat' ? 'selected' : '' }}>Cat</option>
                  <option value="other" {{ old('type', $room->type) === 'other' ? 'selected' : '' }}>Other</option>
                </select>
              </div>
              <div class="space-y-2">
                <label class="fieldset-label" for="status">Status*</label>
                <select class="select w-full" name="status" id="status">
                  <option value="Available" {{ old('status', $room->status) === 'Available' ? 'selected' : '' }}>Available</option>
                  <option value="Blocked" {{ old('status', $room->status) === 'Blocked' ? 'selected' : '' }}>Blocked</option>
                  <option value="Maintenance" {{ old('status', $room->status) === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
              </div>
              <div id="kennel_ids_wrapper" class="space-y-2 {{ old('type', $room->type) === 'cat' ? 'hidden' : '' }}">
                <label class="fieldset-label" for="kennel_ids">Assigned Kennels</label>
                <select class="select w-full" name="kennel_ids[]" id="kennel_ids" multiple>
                  @foreach ($kennels as $kennel)
                  <option value="{{ $kennel->id }}" {{ in_array($kennel->id, $selectedKennelIds) ? 'selected' : '' }}>
                    {{ $kennel->name }} ({{ ucfirst($kennel->type) }})
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="space-y-2 xl:col-span-4">
                <label class="fieldset-label" for="description">Description</label>
                <textarea class="textarea w-full min-h-24" placeholder="Description" name="description" id="description">{{ old('description', $room->description) }}</textarea>
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
    $(document).ready(function() {
      $('#kennel_ids').select2({
        placeholder: 'Select kennels',
        allowClear: true,
        multiple: true,
        width: '100%',
        closeOnSelect: false
      });

      const toggleKennelField = () => {
        const isCat = $('#type').val() === 'cat';
        $('#kennel_ids_wrapper').toggleClass('hidden', isCat);
        $('#kennel_ids').prop('disabled', isCat).trigger('change.select2');
      };

      $('#type').on('change', toggleKennelField);
      toggleKennelField();
    });

    FilePond.registerPlugin(FilePondPluginImagePreview);

    let initialFiles = [];
    @if($room->img)
      initialFiles = [{
        source: '{{ $room->img }}',
        options: {
          type: 'local'
        }
      }];
    @endif

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
        files: initialFiles,
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
              $('#img_action').val('change');
              return result.temp_file;
            },
            onerror: () => {
              $('#alert_message').text('Error uploading image.');
              alert_modal.showModal();
              return '';
            }
          },
          load: (source, load, error) => {
            const imageUrl = '{{ asset("storage/rooms") }}/' + source;

            fetch(imageUrl)
              .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.blob();
              })
              .then(blob => {
                load(blob);
              })
              .catch(() => {
                error('Could not load existing image');
              });
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
            if ($('#current_img').val()) {
              $('#img_action').val('delete');
            }
          }
        }
      });
    }
  </script>
@endsection
