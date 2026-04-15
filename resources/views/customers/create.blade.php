@extends('layouts.main')
@section('title', 'Create Customer')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('src/libs/filepond/filepond.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('src/libs/filepond/filepond-plugin-image-preview.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('src/libs/select2/select2.min.css') }}" />
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Create Customer</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('customers') }}">Customers</a></li>
      <li class="opacity-80">Create</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('create-customer') }}" method="POST" enctype="multipart/form-data" id="create_form">
    @csrf
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12 mt-3">
      <div class="xl:col-span-3">
        <div class="card bg-base-100 shadow" style="height: 100%">
          <div class="card-body">
            <div class="card-title">Upload Avatar</div>
              <div class="mt-4">
                <input type="file" data-filepond class="uploadFile" name="avatar_img"/>
                <input type="hidden" id="temp_file" name="temp_file" />
              </div>
          </div>
        </div>
      </div>
      <div class="xl:col-span-6">
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <div class="card-title">Account Information</div>
            <div class="fieldset mt-2 grid grid-cols-1 gap-4 lg:grid-cols-2">
              <div class="space-y-2">
                <label class="fieldset-label" for="username">Username*</label>
                <label class="input w-full focus:outline-0">
                  <span class="iconify lucide--user text-base-content/60 size-4"></span>
                  <input class="grow focus:outline-0" placeholder="User Name" id="username" name="username" type="text" />
                </label>
              </div>
              <div class="space-y-2">
                <label class="fieldset-label" for="email">Email*</label>
                <label class="input w-full focus:outline-0">
                  <span class="iconify lucide--mail text-base-content/60 size-4"></span>
                  <input class="grow focus:outline-0" placeholder="Email" id="email" name="email" type="email" />
                </label>
              </div>
              <div class="space-y-2">
                <label class="fieldset-label" for="password">Password*</label>
                <label class="input w-full focus:outline-0">
                  <span class="iconify lucide--key-round text-base-content/60 size-4"></span>
                  <input class="grow focus:outline-0" placeholder="Password" id="password" name="password" type="password" />
                  <label class="swap btn btn-xs btn-ghost btn-circle text-base-content/60">
                    <input type="checkbox" aria-label="Show password" data-password="password" />
                    <span class="iconify lucide--eye swap-off size-4"></span>
                    <span class="iconify lucide--eye-off swap-on size-4"></span>
                  </label>
                </label>
              </div>
              <div class="space-y-2">
                <label class="fieldset-label" for="confirm_password">Confirm Password</label>
                <label class="input w-full focus:outline-0">
                  <span class="iconify lucide--key-round text-base-content/60 size-4"></span>
                  <input class="grow focus:outline-0" id="confirm_password" placeholder="Confirm Password" type="password" />
                  <label class="swap btn btn-xs btn-ghost btn-circle text-base-content/60">
                    <input type="checkbox" aria-label="Show password" data-password="confirm_password" />
                    <span class="iconify lucide--eye swap-off size-4"></span>
                    <span class="iconify lucide--eye-off swap-on size-4"></span>
                  </label>
                </label>
              </div>
              <div class="flex items-center gap-3">
                <input class="toggle toggle-sm" id="email_verified" type="checkbox" name="email_verified"/>
                <label class="label" for="email_verified">Email Verified</label>
              </div>
              <div class="flex items-center gap-3">
                <input class="toggle toggle-sm" id="account_status" type="checkbox" name="status"/>
                <label class="label" for="account_status">Account Active</label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="xl:col-span-3">
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <div class="flex justify-between">
              <div class="card-title">Additional Owners</div>
              <button class="btn btn-primary btn-soft btn-sm" onclick="addAdditionalOwner()" type="button">
                <span class="iconify lucide--plus size-3.5"></span>
                Add
              </button>
            </div>
            <input type="hidden" name="owners" id="owners" value="" />
            <div class="fieldset mt-3" id="additional_owners"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 mt-5">
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <div class="card-title">Basic Information</div>
          <div class="fieldset mt-2 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="space-y-2">
              <label class="fieldset-label">First Name*</label>
              <input class="input w-full" placeholder="First Name" name="first_name" type="text" id="first_name" />
            </div>
            <div class="space-y-2">
              <label class="fieldset-label">Last Name*</label>
              <input class="input w-full" placeholder="Last Name" name="last_name" type="text" id="last_name"/>
            </div>
            <div class="space-y-2">
              <label class="fieldset-label">Phone Number*</label>
              <input class="input w-full" placeholder="(098) 765-4321" type="tel" name="phone_number_1" id="phone_number_1" oninput="formatPhoneNumber(this)"/>
            </div>
            <div class="space-y-2">
              <label class="fieldset-label">Phone Number2</label>
              <input class="input w-full" placeholder="(098) 765-4321" type="tel" name="phone_number_2" id="phone_number_2" oninput="formatPhoneNumber(this)"/>
            </div>
            <div class="flex items-center gap-3">
              <input class="radio radio-sm" id="gender-male" type="radio" value="male" checked name="gender" />
              <label class="fieldset-label" for="gender-male">Male</label>
              <input class="radio radio-sm" id="gender-female" type="radio" value="female" name="gender" />
              <label class="fieldset-label" for="gender-female">Female</label>
            </div>
          </div>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <div class="card-title">Address</div>
          <div class="fieldset mt-2 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="space-y-2">
              <label class="fieldset-label" for="street_address">Street</label>
              <input class="input w-full" id="street_address" placeholder="Street" type="text" name="street_address"/>
            </div>
            <div class="space-y-2">
              <label class="fieldset-label" for="city">City</label>
              <input class="input w-full" id="city" placeholder="City" type="text" name="city" />
            </div>
            <div class="space-y-2">
              <label class="fieldset-label" for="state">State</label>
              <select class="select w-full" name="state" id="state" style="height: 60px">
                <option value="" disabled selected>Select a state</option>
                @php
                  $states = [
                    'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
                    'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
                    'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
                    'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
                    'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
                    'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
                    'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
                    'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
                    'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
                    'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
                    'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
                    'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
                    'WI' => 'Wisconsin', 'WY' => 'Wyoming'
                  ];
                @endphp
                @foreach($states as $code => $name)
                  <option value="{{ $code }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
            <div class="space-y-2">
              <label class="fieldset-label" for="zip_code">Zip Code</label>
              <input class="input w-full" id="zip_code" placeholder="564-879" type="text" name="zip_code"/>
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
      <button class="btn btn-sm btn-primary" type="button" onclick="saveCustomer()">
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

  <script src="{{ asset('src/js/components/password-field.js') }}"></script>
  <script src="{{ asset('src/libs/select2/select2.min.js') }}"></script>

  <script>
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
          url: '{{ route("process-file-customer") }}',
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
          url: '{{ route("revert-file-customer") }}',
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

    $(document).ready(function() {
      $('#state').select2({
        placeholder: "Select a state",
      });
    });

    let idx = 0;
    function addAdditionalOwner() {
      const ownerContainer = $('#additional_owners');
      const ownerCount = ownerContainer.children().length;

      if (ownerCount >= 4) {
        $('#alert_message').text('You can only add up to 4 owners.');
        alert_modal.showModal();
        return;
      }

      const newOwner = $(`
        <div class="flex gap-2" id="owner_${idx}">
          <input class="input w-full input-sm" placeholder="Name" id="owner_name_${idx}" type="text" />
          <input class="input w-full input-sm" placeholder="Phone Number" id="owner_phone_${idx}" type="text" oninput="formatPhoneNumber(this)"/>
          <button type="button" class="btn btn-sm btn-ghost btn-square" aria-label="remove" onclick="removeOwner(${idx})">
            <span class="iconify lucide--x size-3"></span>
          </button>
        </div>
      `);

      ownerContainer.append(newOwner);
      idx++;
    }

    function removeOwner(ownerId) {
      $(`#owner_${ownerId}`).remove();
    }

    function saveCustomer() {
      const username = $('#username').val();
      const email = $('#email').val();
      const password = $('#password').val();
      const confirmPassword = $('#confirm_password').val();
      const firstName = $('#first_name').val();
      const lastName = $('#last_name').val();
      const phoneNumber1 = $('#phone_number_1').val();

      if (!username || !email || !password || !confirmPassword || !firstName || !lastName || !phoneNumber1) {
        $('#alert_message').text('Please fill in all required fields.');
        alert_modal.showModal();
        return;
      }

      if (password !== confirmPassword) {
        $('#alert_message').text('Passwords do not match.');
        alert_modal.showModal();
        return;
      }

      // validate if there is an empty owner name or empty owner phone number
      var hasEmptyOwner = false;
      $('#additional_owners').children('div').each(function() {
        const name = $(this).find('input[id^="owner_name_"]').val();
        const phone = $(this).find('input[id^="owner_phone_"]').val();
        if (!name || !phone) {
          hasEmptyOwner = true;
          return false; // Stop further iteration
        }
      });

      if (hasEmptyOwner) {
        $('#alert_message').text('Please fill in all additional owner fields or remove empty ones.');
        alert_modal.showModal();
        return;
      }

      // collecting the additional owners
      var ownerDatas = [];
      $('#additional_owners').children('div').each(function() {
        const name = $(this).find('input[id^="owner_name_"]').val();
        const phone = $(this).find('input[id^="owner_phone_"]').val();

        if (name && phone) {
          ownerDatas.push({ name: name, phone: phone });
        }
      });
      $('#owners').val(JSON.stringify(ownerDatas));

      $('#create_form').submit();
    }
  </script>
@endsection