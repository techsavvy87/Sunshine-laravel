@extends('layouts.main')
@section('title', 'Update Package')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('src/libs/filepond/filepond.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('src/libs/filepond/filepond-plugin-image-preview.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('src/libs/select2/select2.min.css') }}" />
  <style>
    .select2-container--default .select2-selection--multiple {
      min-height: 40px;
      height: 40px;
      overflow-y: auto;
      overflow-x: hidden !important;
      white-space: normal !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
      margin-top: 10px !important;
      margin-left: 10px !important;
    }

    .select2-container .select2-search--inline .select2-search__field {
      margin-top: 10px !important;
      margin-left: 10px !important;
    }
    /* Also ensure the dropdown fits the parent */
    .select2-container {
      width: 100% !important;
      min-width: 0 !important;
    }
  </style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Update Package</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('packages') }}">Packages</a></li>
      <li class="opacity-80">Update</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('update-package') }}" method="POST" id="update_form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="package_id" value="{{ $package->id }}">
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-4 mt-3">
      <div class="xl:col-span-1">
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <div class="card-title">Upload Image</div>
            <div class="mt-4">
              <input type="file" data-filepond class="uploadFile" name="avatar_img"/>
              <input type="hidden" id="temp_file" name="temp_file" />
              <input type="hidden" id="avatar_action" name="avatar_action" value="keep" />
              <input type="hidden" id="current_avatar" name="current_avatar" value="{{ $package->image ?? '' }}" />
            </div>
          </div>
        </div>
      </div>
      <div class="xl:col-span-3">
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <div class="card-title">Basic Information</div>
            <div class="fieldset mt-2 grid grid-cols-1 gap-4 xl:grid-cols-4">
              <div class="xl:col-span-2 space-y-2">
                <label class="fieldset-label" for="name">Package Name*</label>
                <label class="input w-full focus:outline-0">
                  <input placeholder="e.g. Premium Grooming Package" id="name" name="name" type="text" value="{{ $package->name }}" />
                </label>
              </div>
              <div class="space-y-2">
                <label class="fieldset-label" for="price">Price*</label>
                <label class="input w-full focus:outline-0">
                  <input class="grow focus:outline-0" placeholder="e.g. 150.00" id="price" name="price" type="text" value="{{ $package->price }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" />
                  <span class="badge badge-ghost badge-sm">USD</span>
                </label>
              </div>
              <div class="space-y-2">
                <label class="fieldset-label" for="days">Days*</label>
                <label class="input w-full focus:outline-0">
                  <input placeholder="e.g. 30" id="days" name="days" type="text" value="{{ $package->days }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"/>
                </label>
              </div>
            </div>
            <div class="fieldset mt-2">
              <div class="space-y-2">
                <label class="fieldset-label" for="service_ids">Services</label>
                <select class="select w-full focus:outline-0" id="service_ids" multiple>
                  @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ in_array($service->id, $selectedServiceIds) ? 'selected' : '' }}>{{ $service->name }}</option>
                  @endforeach
                </select>
                <input type="hidden" id="service_ids_hidden" name="service_ids" value="{{ $package->service_ids }}" />
              </div>
            </div>
            <div class="fieldset mt-2">
              <div class="space-y-2">
                <label class="fieldset-label" for="description">Description</label>
                <textarea placeholder="Type here" class="textarea w-full" name="description" id="description" rows="4">{{ $package->description }}</textarea>
              </div>
            </div>
            <div class="fieldset mt-2">
              <div class="space-y-2">
                <label class="fieldset-label" for="status">Status</label>
                <div class="flex items-center gap-3">
                  <input class="toggle toggle-sm" id="status" type="checkbox" name="status" {{ $package->status === 'active' ? 'checked' : '' }}/>
                  <label class="label" for="status">Is Active</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
      <a class="btn btn-sm btn-ghost" href="{{ url()->previous() }}">
        <span class="iconify lucide--x size-4"></span>
        Cancel
      </a>
      <button class="btn btn-sm btn-primary" type="button" onclick="savePackage()">
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
    $('#service_ids').select2({
      placeholder: "Select services",
      allowClear: true,
      multiple: true,
      width: '100%',
      closeOnSelect: false
    });
  });

  // Register FilePond plugins
  FilePond.registerPlugin(FilePondPluginImagePreview);

  let initialFiles = [];
  @if($package->image)
    initialFiles = [{
      source: '{{ $package->image }}',
      options: {
        type: 'local'
      }
    }];
  @endif

  // Create a FilePond instance
  const inputElement = document.querySelector('input[type="file"][data-filepond]');
  const pond = FilePond.create(inputElement, {
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
      // Additional validation if needed
      const file = item.file;

      if (file.size > 1024 * 1024 * 2) { // 2MB
        $('#alert_message').text('The size of image should be smaller than 2M.');
        alert_modal.showModal();
        return false; // Prevent file from being added
      }

      if (!file.type.startsWith('image/')) {
        $('#alert_message').text('Uploaded file must be an image.');
        alert_modal.showModal();
        return false; // Prevent file from being added
      }

      return true; // Allow file to be added
    },
    server: {
      process: {
        url: '{{ route("process-file-service") }}',
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        onload: (response) => {
          // Handle successful upload response
          const result = JSON.parse(response);
          $('#temp_file').val(result.temp_file); // Store the file path in a hidden input
          $('#avatar_action').val('change');
          return result.temp_file; // Return the file path to FilePond
        },
        onerror: (response) => {
          $('#alert_message').text('Error uploading image.');
          alert_modal.showModal();
          return '';
        }
      },
      // Add this load configuration for existing files
      load: (source, load, error, progress, abort, headers) => {
        const imageUrl = '{{ asset("storage/services") }}/' + source;

        fetch(imageUrl)
          .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.blob();
          })
          .then(blob => {
            load(blob);
          })
          .catch(() => {
            error('Could not load existing avatar');
          });
      },
      revert: {
        url: '{{ route("revert-file-service") }}',
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      }
    },

    onprocessfile: (error, file) => {
      if (error) {
        console.error('Error processing file:', error);
        $('#alert_message').text('Error processing image.');
        alert_modal.showModal();
      } else {
        console.log('File processed successfully:', file);
        $('#avatar_action').val('change');
      }
    },

    onremovefile: (error, file) => {
      if (error) {
        console.error('Error removing file:', error);
        $('#alert_message').text('Error removing image.');
        alert_modal.showModal();
      } else {
        console.log('File removed successfully:', file);
        $('#temp_file').val(''); // Clear the hidden input when file is removed

        // Check if user had an original avatar
        const currentAvatar = $('#current_avatar').val();
        if (currentAvatar) {
          $('#avatar_action').val('delete'); // Set action to delete if original existed
        } else {
          $('#avatar_action').val('keep'); // Set action to keep if no original
        }
      }
    }
  });

  function savePackage() {
    const name = $('#name').val();
    const price = $('#price').val();
    const days = $('#days').val();

    if (!name || !price || !days) {
      $('#alert_message').text('Please fill in all required fields.');
      alert_modal.showModal();
      return;
    }

    // Validate price is a valid number
    if (isNaN(parseFloat(price)) || parseFloat(price) <= 0) {
      $('#alert_message').text('Please enter a valid price.');
      alert_modal.showModal();
      return;
    }

    // Get selected service IDs
    const selectedServices = $('#service_ids').val() || [];
    $('#service_ids_hidden').val(selectedServices.join(','));

    $('#update_form').submit();
  }
</script>
@endsection

