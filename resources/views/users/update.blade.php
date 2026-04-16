@extends('layouts.main')
@section('title', 'Update User')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('src/libs/filepond/filepond.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('src/libs/filepond/filepond-plugin-image-preview.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('src/libs/select2/select2.min.css') }}" />
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Update User</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('users') }}">Users</a></li>
      <li class="opacity-80">Update</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('update-user') }}" method="POST" enctype="multipart/form-data" id="update_form">
    @csrf
    <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}" />
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12 mt-3">
      <div class="xl:col-span-6">
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <div class="card-title">Account Information</div>
            <div class="fieldset mt-2 grid grid-cols-1 gap-4 lg:grid-cols-2">
              <div class="space-y-2">
                <label class="fieldset-label" for="username">Username*</label>
                <label class="input w-full focus:outline-0">
                  <span class="iconify lucide--user text-base-content/60 size-4"></span>
                  <input class="grow focus:outline-0" placeholder="User Name" id="username" name="username" type="text" value="{{ $user->name }}" />
                </label>
              </div>
              <div class="space-y-2">
                <label class="fieldset-label" for="email">Email*</label>
                <label class="input w-full focus:outline-0">
                  <span class="iconify lucide--mail text-base-content/60 size-4"></span>
                  <input class="grow focus:outline-0" placeholder="Email" id="email" name="email" type="email" value="{{ $user->email }}" />
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
            </div>
            <div class="fieldset mt-5 flex flex-wrap items-center gap-5 xl:flex-nowrap">
              <div class="flex items-center gap-3">
                <input class="toggle toggle-sm" id="email_verified" type="checkbox" name="email_verified" {{ $user->email_verified_at ? 'checked' : '' }}/>
                <label class="label" for="email_verified">Email Verified</label>
              </div>
              <div class="flex items-center gap-3">
                <input class="toggle toggle-sm" id="is_active" type="checkbox" name="is_active" {{ $user->status ? 'checked' : '' }}/>
                <label class="label" for="is_active">Is Active</label>
              </div>
              <div class="flex items-center gap-3">
                <input class="toggle toggle-sm" id="block_reservations" type="checkbox" name="block_reservations" {{ $user->block_reservations ? 'checked' : '' }}/>
                <label class="label" for="block_reservations">Block Reservations</label>
              </div>
              <div class="flex items-center gap-3">
                <input class="toggle toggle-sm" id="block_messages" type="checkbox" name="block_messages" {{ $user->block_messages ? 'checked' : '' }}/>
                <label class="label" for="block_messages">Block Messages</label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="xl:col-span-3">
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <div class="card-title">Upload Avatar</div>
              <div class="mt-4">
                <input type="file" data-filepond class="uploadFile" name="avatar_img"/>
                <input type="hidden" id="temp_file" name="temp_file" />
                <input type="hidden" id="avatar_action" name="avatar_action" value="keep" />
                <input type="hidden" id="current_avatar" name="current_avatar" value="{{ $user->profile->avatar_img ?? '' }}" />
              </div>
          </div>
        </div>
      </div>
      <div class="xl:col-span-3">
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <div class="card-title">Assign Roles</div>
            <div class="fieldset mt-2">
              <select class="select w-full" name="roles[]" id="roles" multiple="multiple">
                @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ $role->title }}</option>
                @endforeach
              </select>
            </div>
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
              <input class="input w-full" placeholder="First Name" name="first_name" type="text" id="first_name" value="{{ $user->profile ? $user->profile->first_name : '' }}" />
            </div>
            <div class="space-y-2">
              <label class="fieldset-label">Last Name*</label>
              <input class="input w-full" placeholder="Last Name" name="last_name" type="text" id="last_name" value="{{ $user->profile ? $user->profile->last_name : '' }}" />
            </div>
            <div class="space-y-2">
              <label class="fieldset-label">Phone Number*</label>
              <input class="input w-full" placeholder="(098) 765 4321" type="tel" name="phone_number_1" id="phone_number_1" value="{{ $user->profile ? $user->profile->phone_number_1 : '' }}" oninput="formatPhoneNumber(this)"/>
            </div>
            <div class="space-y-2">
              <label class="fieldset-label">Phone Number2</label>
              <input class="input w-full" placeholder="(098) 765 4321" type="tel" name="phone_number_2" id="phone_number_2" value="{{ $user->profile ? $user->profile->phone_number_2 : '' }}" oninput="formatPhoneNumber(this)"/>
            </div>
            <div class="space-y-2">
              <label class="fieldset-label">Home Number</label>
              <input class="input w-full" placeholder="(098) 765 4321" type="tel" name="home_number" id="home_number" value="{{ $user->profile ? $user->profile->home_number : '' }}" oninput="formatPhoneNumber(this)"/>
            </div>
            <div class="space-y-2">
              <label class="fieldset-label">Work Number</label>
              <input class="input w-full" placeholder="(098) 765 4321" type="tel" name="work_number" id="work_number" value="{{ $user->profile ? $user->profile->work_number : '' }}" oninput="formatPhoneNumber(this)"/>
            </div>
            <div class="flex items-center gap-3">
              <input class="radio radio-sm" id="gender-male" type="radio" value="male" name="gender" {{ $user->profile && $user->profile->gender === 'male' ? 'checked' : '' }}/>
              <label class="fieldset-label" for="gender-male">Male</label>
              <input class="radio radio-sm" id="gender-female" type="radio" value="female" name="gender" {{ $user->profile && $user->profile->gender === 'female' ? 'checked' : '' }}/>
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
              <input class="input w-full" id="street_address" placeholder="Street" type="text" name="street_address" value="{{ $user->profile ? $user->profile->address : '' }}" />
            </div>
            <div class="space-y-2">
              <label class="fieldset-label" for="city">City</label>
              <input class="input w-full" id="city" placeholder="City" type="text" name="city" value="{{ $user->profile ? $user->profile->city : '' }}" />
            </div>
            <div class="space-y-2">
              <label class="fieldset-label" for="state">State</label>
              <select class="select w-full" name="state" id="state" style="height: 60px">
                <option value="" {{ $user->profile && !$user->profile->state ? 'selected' : '' }}>Select a state</option>
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
                  <option value="{{ $code }}" {{ $user->profile && $user->profile->state === $code ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
              </select>
            </div>
            <div class="space-y-2">
              <label class="fieldset-label" for="zip_code">Zip Code</label>
              <input class="input w-full" id="zip_code" placeholder="564-879" type="text" name="zip_code" value="{{ $user->profile ? $user->profile->zip_code : '' }}"/>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="card-title">Emergency Contact Info</div>
          <div class="fieldset mt-2">
            <textarea class="textarea w-full" placeholder="Emergency Contact Info" name="emergency_contact_info" id="emergency_contact_info">{{ $user->profile ? $user->profile->emergency_contact_info : '' }}</textarea>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
      <a class="btn btn-sm btn-ghost" href="{{ route('users') }}">
        <span class="iconify lucide--x size-4"></span>
        Cancel
      </a>
      <button class="btn btn-sm btn-primary" type="button" onclick="saveUser()">
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

    // Prepare initial files array for existing avatar
    let initialFiles = [];
    @if($user->profile && $user->profile->avatar_img)
      initialFiles = [{
        source: '{{ $user->profile->avatar_img }}',
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
        // Additional validation
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
          url: '{{ route("process-file-upload") }}',
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          onload: (response) => {
            // Handle successful upload response
            const result = JSON.parse(response);
            $('#temp_file').val(result.temp_file); // Store the file path in a hidden input
            $('#avatar_action').val('change'); // Set action to change when new file is uploaded
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
          const imageUrl = '{{ asset("storage/profiles") }}/' + source;

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
          url: '{{ route("revert-file-upload") }}',
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

    $(document).ready(function() {
      $('#state').select2({
        placeholder: "Select a state",
      });

      $('#roles').select2({
        placeholder: "Select a role",
      });

      // Set the selected values for roles
      @if($user->roles && count($user->roles) > 0)
        $('#roles').val([
          @foreach($user->roles as $role)
            '{{ $role->id }}'{{ !$loop->last ? ',' : '' }}
          @endforeach
        ]).trigger('change');
      @endif
    });

    function saveUser() {
      const username = $('#username').val();
      const email = $('#email').val();
      const password = $('#password').val();
      const confirmPassword = $('#confirm_password').val();
      const firstName = $('#first_name').val();
      const lastName = $('#last_name').val();
      const phoneNumber1 = $('#phone_number_1').val();

      if (!username || !email || !firstName || !lastName || !phoneNumber1) {
        $('#alert_message').text('Please fill in all required fields.');
        alert_modal.showModal();
        return;
      }

      if (password !== confirmPassword) {
        $('#alert_message').text('Passwords do not match.');
        alert_modal.showModal();
        return;
      }

      $('#update_form').submit();
    }
  </script>
@endsection