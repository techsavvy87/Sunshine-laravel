@extends('layouts.main')
@section('title', 'Create Package')

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
  <h3 class="text-lg font-medium">Create Package</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('packages') }}">Packages</a></li>
      <li class="opacity-80">Create</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('create-package') }}" method="POST" id="create_form" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-4 mt-3">
      <div class="xl:col-span-1">
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <div class="card-title">Upload Image</div>
            <div class="mt-4">
              <input type="file" data-filepond class="uploadFile" name="avatar_img"/>
              <input type="hidden" id="temp_file" name="temp_file" />
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
                  <input placeholder="e.g. Premium Grooming Package" id="name" name="name" type="text" />
                </label>
              </div>
              <div class="space-y-2">
                <label class="fieldset-label" for="price">Price*</label>
                <label class="input w-full focus:outline-0">
                  <input class="grow focus:outline-0" placeholder="e.g. 150.00" id="price" name="price" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" />
                  <span class="badge badge-ghost badge-sm">USD</span>
                </label>
              </div>
              <div class="space-y-2">
                <label class="fieldset-label" for="days">Days*</label>
                <label class="input w-full focus:outline-0">
                  <input placeholder="e.g. 30" id="days" name="days" type="text" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')" />
                </label>
              </div>
            </div>
            <div class="fieldset mt-2">
              <div class="space-y-2">
                <label class="fieldset-label" for="service_ids">Services</label>
                <select class="select w-full focus:outline-0" id="service_ids" multiple>
                  @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                  @endforeach
                </select>
                <input type="hidden" id="service_ids_hidden" name="service_ids" />
              </div>
            </div>
            <div class="fieldset mt-2">
              <div class="space-y-2">
                <label class="fieldset-label" for="description">Description</label>
                <textarea placeholder="Type here" class="textarea w-full" name="description" id="description" rows="4"></textarea>
              </div>
            </div>
            <div class="fieldset mt-2">
              <div class="space-y-2">
                <label class="fieldset-label" for="status">Status</label>
                <div class="flex items-center gap-3">
                  <input class="toggle toggle-sm" id="status" type="checkbox" name="status" checked/>
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
          return result.temp_file; // Return the file path to FilePond
        },
        onerror: (response) => {
          $('#alert_message').text('Error uploading image.');
          alert_modal.showModal();
          return '';
        }
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

    $('#create_form').submit();
  }
</script>
@endsection

