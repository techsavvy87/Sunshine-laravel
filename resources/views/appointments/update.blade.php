@extends('layouts.main')
@section('title', 'Update Appointment')

@section('page-css')
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
    .select2-container--default.select2-container--disabled .select2-selection--multiple {
      background-color: unset !important;
    }
  </style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Update Appointment</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('appointments') }}">Appointments</a></li>
      <li class="opacity-80">Update</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('update-appointment') }}" method="POST" id="update_form">
    @csrf
    <input type="hidden" name="appointment_id" id="appointment_id" value="{{ $appointment->id }}" />
    <input type="hidden" name="status" id="form_status" value="" />
    @if(isPackageService($appointment->service))
      <input type="hidden" name="customer" value="{{ $appointment->customer_id }}" />
      <input type="hidden" name="service" value="{{ $appointment->service_id }}" />
      @php
        $selectedPackageId = $appointment->metadata && isset($appointment->metadata['package_id']) ? $appointment->metadata['package_id'] : null;
      @endphp
      @if($selectedPackageId)
        <input type="hidden" name="package_id" value="{{ $selectedPackageId }}" />
      @endif
    @endif
    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <div class="fieldset mt-2 grid grid-cols-1 gap-6 xl:grid-cols-2">
          <div class="space-y-2">
            <label class="fieldset-label" for="customer">Customer*</label>
            <select class="select w-full" name="customer" id="customer" {{ isPackageService($appointment->service) ? 'disabled' : '' }}>
              <option value="" hidden selected>Choose a customer</option>
            </select>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="pet">Pet*</label>
            <select class="select w-full" name="pet" id="pet">
              <option value="" hidden selected>Choose a pet</option>
            </select>
          </div>
        </div>
        <div class="fieldset mt-2 grid grid-cols-1 gap-6 xl:grid-cols-3">
          <div class="space-y-2">
            <label class="fieldset-label" for="service">Service*</label>
            <select class="select w-full" name="service" id="service" onchange="changeService(this)" value="{{ $appointment->service_id }}" {{ isPackageService($appointment->service) ? 'disabled' : '' }}>
              <option value="" hidden selected>Choose a service</option>
              @foreach($services as $service)
                <option value="{{ $service->id }}" {{ $service->id == $appointment->service_id ? 'selected' : '' }}>{{ $service->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="xl:col-span-2" id="additional_services_group">
            <div class="space-y-2">
              <label class="fieldset-label" for="additional_services">Additional Services</label>
              <select class="select w-full" name="additional_services[]" id="additional_services" multiple data-selected="{{ $appointment->additional_service_ids }}">
                @foreach($additionalServices as $service)
                  @php
                    $selectedAdditionalServices = $appointment->additional_service_ids ? explode(',', $appointment->additional_service_ids) : [];
                  @endphp
                  <option value="{{ $service->id }}" {{ in_array($service->id, $selectedAdditionalServices) ? 'selected' : '' }}>{{ $service->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="xl:col-span-2 hidden" id="secondary_services_group">
            <div class="space-y-2">
              <label class="fieldset-label" for="secondary_services">Grooming Services*</label>
              <select class="select w-full" name="secondary_services[]" id="secondary_services" multiple>
                @php
                  $selectedSecondaryServices = $appointment->metadata && isset($appointment->metadata['secondary_service_ids']) ? explode(',', $appointment->metadata['secondary_service_ids']) : [];
                @endphp
                @foreach($secondaryServices as $service)
                  <option value="{{ $service->id }}" {{ in_array((string)$service->id, $selectedSecondaryServices) ? 'selected' : '' }}>{{ $service->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="xl:col-span-2 hidden" id="group_classes_group">
            <div class="space-y-2">
              <label class="fieldset-label" for="group_classes">Group Classes*</label>
              @php
                $selectedGroupClasses = $appointment->metadata && isset($appointment->metadata['group_class_ids']) ? explode(',', $appointment->metadata['group_class_ids']) : [];
              @endphp
              <select class="select w-full" name="group_class_ids[]" id="group_classes" multiple disabled>
                @isset($groupClasses)
                  @foreach($groupClasses as $cls)
                    <option value="{{ $cls->id }}" {{ in_array((string)$cls->id, $selectedGroupClasses) ? 'selected' : '' }}>{{ $cls->name }}</option>
                  @endforeach
                @endisset
              </select>
              <div id="group_classes_details" class="mt-3 space-y-2"></div>
            </div>
          </div>
          <div class="xl:col-span-2 {{ isPackageService($appointment->service) ? '' : 'hidden' }}" id="packages_group">
            <div class="space-y-2">
              <label class="fieldset-label" for="packages">Packages*</label>
              <select class="select w-full" name="package_id" id="packages" {{ isPackageService($appointment->service) ? 'disabled' : '' }}>
                @isset($packages)
                  @php
                    $selectedPackageId = $appointment->metadata && isset($appointment->metadata['package_id']) ? $appointment->metadata['package_id'] : null;
                  @endphp
                  @if(!$selectedPackageId)
                    <option value="" hidden selected>Choose a package</option>
                  @endif
                  @foreach($packages as $package)
                    <option value="{{ $package->id }}" data-package='@json($package)' {{ $selectedPackageId == $package->id ? 'selected' : '' }}>{{ $package->name }}</option>
                  @endforeach
                @endisset
              </select>
              <div id="packages_details" class="mt-3 space-y-2"></div>
            </div>
          </div>
        </div>
        <div class="fieldset mt-3 grid grid-cols-1 gap-6 xl:grid-cols-3">
          <input type="hidden" id="date" name="date" />
          <div class="space-y-2" id="date_group">
            <label class="fieldset-label" for="date">Date*</label>
            <div class="dropdown w-full">
              <div role="button" class="btn btn-outline border-base-300 flex items-center gap-2" tabindex="0">
                <span class="iconify lucide--calendar text-base-content/60 size-4"></span>
                <p class="text-start" id="button_cally_target">{{ $appointment->date ? Carbon\Carbon::parse($appointment->date)->format('Y-m-d') : '-' }}</p>
                <span class="iconify lucide--chevron-down text-base-content/70 size-4"></span>
              </div>
              <div class="dropdown-content mt-2" tabindex="0">
                <calendar-date class="cally bg-base-100 rounded-box shadow-md transition-all hover:shadow-lg" id="button_cally_element" value="{{ $appointment->date ? Carbon\Carbon::parse($appointment->date)->format('Y-m-d') : '-' }}" >
                  <span class="iconify lucide--chevron-left" slot="previous"></span>
                  <span class="iconify lucide--chevron-right" slot="next"></span>
                  <calendar-month></calendar-month>
                </calendar-date>
              </div>
            </div>
          </div>
          <div class="space-y-2 {{ $appointment->service && str_contains(strtolower($appointment->service->category->name), 'daycare') ? '' : 'hidden' }}" id="daycare_duration_group">
            <label class="fieldset-label" for="daycare_duration">Duration*</label>
            <select class="select w-full" name="daycare_duration" id="daycare_duration">
              <option value="" hidden selected>Choose duration</option>
              <option value="half" {{ $appointment->metadata && isset($appointment->metadata['daycare_duration']) && $appointment->metadata['daycare_duration'] === 'half_day' ? 'selected' : '' }}>Half Day</option>
              <option value="full" {{ $appointment->metadata && isset($appointment->metadata['daycare_duration']) && $appointment->metadata['daycare_duration'] === 'full_day' ? 'selected' : '' }}>Full Day</option>
            </select>
          </div>
          <div class="space-y-2 {{ $appointment->service && str_contains(strtolower($appointment->service->category->name), 'training') ? '' : 'hidden' }}" id="private_training_duration_group">
            <label class="fieldset-label" for="private_training_duration">Duration*</label>
            <select class="select w-full" name="private_training_duration" id="private_training_duration">
              <option value="" hidden selected>Choose duration</option>
              <option value="half" {{ $appointment->metadata && isset($appointment->metadata['private_training_duration']) && $appointment->metadata['private_training_duration'] === 'half_hour' ? 'selected' : '' }}>Half Hour</option>
              <option value="one" {{ $appointment->metadata && isset($appointment->metadata['private_training_duration']) && $appointment->metadata['private_training_duration'] === 'one_hour' ? 'selected' : '' }}>One Hour</option>
            </select>
          </div>
          <div class="space-y-2" id="time_slot_group">
            <label class="fieldset-label" for="time_slot">Start Time - End Time*</label>
            @if (isAlaCarteService($appointment->service))
            <input type="text" value="{{ $appointment->start_time }} - {{ $appointment->end_time}}" disabled class="input w-full bg-base-200" />
            @else
            <select class="select w-full" name="time_slot" id="time_slot">
              <option value="" hidden selected>Choose a time slot</option>
              @foreach ($timeSlots as $slot)
                <option value="{{ $slot->id }}" {{ $slot->start_time == $appointment->start_time ? 'selected' : '' }}>{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}</option>
              @endforeach
            </select>
            <input type="hidden" name="time_slot_data" id="time_slot_data" />
            @endif
          </div>
          <div class="space-y-2 hidden" id="boarding_start_group">
            <label class="fieldset-label">Drop Off Date/Time*</label>
            <input
              type="datetime-local"
              class="input w-full"
              id="boarding_start_datetime"
              name="boarding_start_datetime"
              format="YYYY-MM-DD HH:mm" placeholder="Select drop off date/time"
              value="{{ $appointment->date ? $appointment->date . 'T' . \Carbon\Carbon::parse($appointment->start_time)->format('H:i') : '' }}"
            />
          </div>
          <div class="space-y-2 hidden" id="boarding_end_group">
            <label class="fieldset-label">Pick Up Date/Time*</label>
            <input
              type="datetime-local"
              class="input w-full"
              id="boarding_end_datetime"
              name="boarding_end_datetime"
              format="YYYY-MM-DD HH:mm" placeholder="Select drop off date/time"
              value="{{ $appointment->end_date ? $appointment->end_date . 'T' . \Carbon\Carbon::parse($appointment->end_time)->format('H:i') : '' }}"
            />
          </div>
        </div>
          <div class="fieldset mt-3 grid grid-cols-1 gap-6 xl:grid-cols-2">
          <div class="space-y-2 {{ isPackageService($appointment->service) ? 'hidden' : '' }}" id="staff_group">
            <label class="fieldset-label" for="staff">Staff</label>
            <select class="select w-full" name="staff" id="staff">
              <option value="" hidden selected>Choose a staff</option>
            </select>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="appointment_status">Appointment Status</label>
            <select class="select w-full" name="appointment_status" id="appointment_status">
              <option value="">-- Select Status --</option>
              <option value="cancelled" {{ $appointment->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              <option value="no_show" {{ $appointment->status === 'no_show' ? 'selected' : '' }}>No Show</option>
            </select>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
      <a class="btn btn-sm btn-ghost" href="{{ url()->previous() }}">
        <span class="iconify lucide--x size-4"></span>
        Cancel
      </a>
      <button class="btn btn-sm btn-primary" type="button" onclick="saveAppointment()">
        <span class="iconify lucide--check size-4"></span>
        Save
      </button>
    </div>
  </form>
</div>
<dialog id="confirm_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium">
      Confirm
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4" id="confirm_message"></p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost btn-sm">No</button>
      </form>
      <button class="btn btn-primary btn-sm btn-soft" id="confirm_status_button" onclick="confirmAction()">Yes</button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>
@endsection

@section('page-js')
  <script src="{{ asset('src/libs/select2/select2.min.js') }}"></script>
  <script src="{{ asset('src/assets/ui-components-calendar.js') }}"></script>
  <script type="module" src="https://unpkg.com/cally"></script>

  <script>
    const confirm_modal = document.getElementById('confirm_modal');
    const alert_modal = document.getElementById('alert_modal') || null;

    document.getElementById("button_cally_element")?.addEventListener("change", (e) => {
      document.getElementById("button_cally_target").innerText = e.target.value

      const serviceId = $('#service').val();
      const petId = $('#pet').val();
      const daycareDuration = $('#daycare_duration').val();
      const privateTrainingDuration = $('#private_training_duration').val();
      const secondaryServiceIds = $('#secondary_services').val() || [];

      populateTimeSlots(serviceId, e.target.value, petId, daycareDuration, privateTrainingDuration, secondaryServiceIds);
    })

    $(document).ready(function() {
      // customer select2 with ajax
      $('#customer').select2({
        placeholder: "Choose a customer",
        ajax: {
          url: '{{ route("get-appointment-customers") }}',
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              q: params.term // Send the search term as 'q'
            };
          },
          processResults: function (data) {
            return {
              results: data.map(function (customer) {
                return {
                  id: customer.id,
                  first_name: customer.profile.first_name,
                  last_name: customer.profile.last_name,
                  email: customer.email,
                  phone_number: customer.profile.phone_number_1
                };
              })
            };
          }
        },
        templateResult: function (customer) {
          if (!customer.id) {
            return customer.text;
          }
          if (!customer.first_name) {
            return customer.text;
          }
          var $container = $(`
            <div class="flex items-center gap-2">
              <span class="font-medium">${customer.first_name} ${customer.last_name}</span>
              <span class="text-sm text-base-content/70">(${customer.email} | ${customer.phone_number})</span>
            </div>
          `);
          return $container;
        },
        templateSelection: function (customer) {
          if (!customer.id) {
            return customer.text;
          }
          if (!customer.first_name) {
            return customer.text;
          }
          var $container = $(`
            <div class="flex items-center gap-2">
              <span class="font-medium">${customer.first_name} ${customer.last_name}</span>
              <span class="text-sm text-base-content/70">(${customer.email} | ${customer.phone_number})</span>
            </div>
          `);
          return $container;
        }
      });

      // Add the customer option if not present
      var customerText = "{{ $appointment->customer->profile->first_name ?? '' }} {{ $appointment->customer->profile->last_name ?? '' }} ({{ $appointment->customer->email ?? '' }} | {{ $appointment->customer->profile->phone_number_1 ?? '' }})";
      var customerOption = new Option(customerText, "{{ $appointment->customer_id }}", true, true);
      $('#customer').append(customerOption).trigger('change');

      $('#customer').on('select2:select', function (e) {
        const selectedData = e.params.data;
        const customerId = selectedData.id;

        // Fetch pets for the selected customer
        $.ajax({
          url: '{{ url("/appointment/pets") }}/' + customerId,
          type: 'GET',
          dataType: 'json',
          success: function(pets) {
            // Clear existing options
            $('#pet').empty();
            $('#pet').append('<option value="" hidden selected>Choose a pet</option>');

            // Populate the pet dropdown with new options
            $.each(pets, function(index, pet) {
              $('#pet').append('<option value="' + pet.id + '">' + pet.name + '</option>');
            });
          },
          error: function() {
            console.error('Failed to fetch pets for the selected customer.');
          }
        });
      });

      // Populate pets for the current customer
      var currentCustomerId = "{{ $appointment->customer_id }}";
      var currentPetId = "{{ $appointment->pet_id }}";
      if (currentCustomerId) {
        $.ajax({
          url: '{{ url("/appointment/pets") }}/' + currentCustomerId,
          method: 'GET',
          dataType: 'json',
          success: function(pets) {
            $('#pet').empty();
            $('#pet').append('<option value="" hidden selected>Choose a pet</option>');
            $.each(pets, function(index, pet) {
              var selected = (pet.id == currentPetId) ? 'selected' : '';
              $('#pet').append('<option value="' + pet.id + '" ' + selected + '>' + pet.name + '</option>');
            });
          },
          error: function() {
            console.error('Failed to fetch pets for the selected customer.');
          }
        });
      }

      @if(!isPackageService($appointment->service))
      $('#staff').select2({
        placeholder: "Choose a staff",
        ajax: {
          url: '{{ route("get-appointment-staffs") }}',
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              q: params.term // Send the search term as 'q'
            };
          },
          processResults: function (data) {
            return {
              results: data.map(function (staff) {
                return {
                  id: staff.id,
                  first_name: staff.profile.first_name,
                  last_name: staff.profile.last_name,
                  email: staff.email,
                  phone_number: staff.profile.phone_number_1
                };
              })
            };
          }
        },
        templateResult: function (staff) {
          if (!staff.id) {
            return staff.text;
          }
          if (!staff.first_name) {
            return staff.text;
          }
          var $container = $(`
            <div class="flex items-center gap-2">
              <span class="font-medium">${staff.first_name} ${staff.last_name}</span>
              <span class="text-sm text-base-content/70">(${staff.email} | ${staff.phone_number})</span>
            </div>
          `);
          return $container;
        },
        templateSelection: function (staff) {
          if (!staff.id) {
            return staff.text;
          }
          if (!staff.first_name) {
            return staff.text;
          }
          var $container = $(`
            <div class="flex items-center gap-2">
              <span class="font-medium">${staff.first_name} ${staff.last_name}</span>
              <span class="text-sm text-base-content/70">(${staff.email} | ${staff.phone_number})</span>
            </div>
          `);
          return $container;
        }
      });
      // Add the staff option if not present
      var staffText = "{{ $appointment->staff->profile->first_name ?? '' }} {{ $appointment->staff->profile->last_name ?? '' }} ({{ $appointment->staff->email ?? '' }} | {{ $appointment->staff->profile->phone_number_1 ?? '' }})";
      var staffOption = new Option(staffText, "{{ $appointment->staff_id }}", true, true);
      $('#staff').append(staffOption).trigger('change');
      @endif

      window.originalAdditionalOptions = $('#additional_services').html();

      // Define servicesData globally so it's accessible to all functions
      window.servicesData = [];
      @foreach($services as $s)
        window.servicesData.push({
          id: {{ $s->id }},
          name: '{{ addslashes($s->name) }}',
          category_name: '{{ $s->category ? addslashes($s->category->name) : '' }}',
          price_small: {{ $s->price_small !== null ? $s->price_small : 'null' }},
        });
      @endforeach

      // Define additionalServicesData with category and level info
      window.additionalServicesData = [];
      @foreach($additionalServices as $s)
        window.additionalServicesData.push({
          id: {{ $s->id }},
          name: '{{ addslashes($s->name) }}',
          category_name: '{{ $s->category ? addslashes($s->category->name) : '' }}',
          level: '{{ $s->level }}',
        });
      @endforeach

      window.packagesData = [];
      @isset($packages)
        @foreach($packages as $package)
          window.packagesData.push({
            id: {{ $package->id }},
            name: '{{ addslashes($package->name) }}',
            price: {{ $package->price }},
            days: {{ $package->days ?? 0 }},
            service_ids: '{{ $package->service_ids }}',
            description: `{!! addslashes($package->description ?? '') !!}`
          });
        @endforeach
      @endisset

      $('#additional_services').select2({
        placeholder: "Choose additional services (optional)",
        allowClear: true,
        multiple: true,
        width: '100%',
        closeOnSelect: false
      });

      $('#secondary_services').select2({
        placeholder: "Choose secondary services (required)",
        allowClear: false,
        multiple: true,
        width: '100%',
        closeOnSelect: false
      }).on('change', function() {
        const serviceId = $('#service').val();
        const date = $('#button_cally_target').text();
        const petId = $('#pet').val();
        const secondaryServiceIds = $(this).val() || [];

        if (serviceId && date !== '-' && petId && secondaryServiceIds.length > 0) {
          populateTimeSlots(serviceId, date, petId, '', '', secondaryServiceIds);
        } else {
          $('#time_slot').empty();
          $('#time_slot').append('<option value="" hidden selected>Choose a time slot</option>');
        }
      });

      $('#group_classes').select2({
        placeholder: "Select group classes",
        multiple: true,
        width: '100%',
        closeOnSelect: false
      }).on('change', function() {
        renderGroupClassDetails();
      });
      renderGroupClassDetails();

      $('#packages').select2({
        placeholder: "Choose a package",
        width: '100%',
        disabled: {{ isPackageService($appointment->service) ? 'true' : 'false' }}
      }).on('change', function() {
        renderPackageDetails();
      });
      
      @php
        $isPackage = isPackageService($appointment->service);
        $selectedPackageId = null;
        if ($isPackage && $appointment->metadata) {
          $metadata = is_array($appointment->metadata) ? $appointment->metadata : json_decode($appointment->metadata, true);
          if ($metadata && isset($metadata['package_id'])) {
            $selectedPackageId = $metadata['package_id'];
          }
        }
      @endphp
      
      @if($isPackage && $appointment->metadata)
      const metadataFromPage = @json($appointment->metadata);
      let packageIdFromMetadata = null;
      if (metadataFromPage && metadataFromPage.package_id) {
        packageIdFromMetadata = metadataFromPage.package_id;
      }
      
      @if($selectedPackageId)
      setTimeout(function() {
        const packageId = {{ $selectedPackageId }};
        $('#packages').val(packageId).trigger('change.select2');
        setTimeout(function() {
          renderPackageDetails();
        }, 50);
      }, 200);
      @elseif(isset($appointment->metadata['package_id']))
      setTimeout(function() {
        if (packageIdFromMetadata) {
          $('#packages').val(packageIdFromMetadata).trigger('change.select2');
          setTimeout(function() {
            renderPackageDetails();
          }, 50);
        }
      }, 200);
      @endif
      @else
      console.log('Package initialization skipped - isPackage:', {{ $isPackage ? 'true' : 'false' }}, 'metadata exists:', {{ $appointment->metadata ? 'true' : 'false' }});
      @endif

      $('#pet').on('change', function() {
        const serviceId = $('#service').val();
        const date = $('#button_cally_target').text();
        const petId = $(this).val();
        const daycareDuration = $('#daycare_duration').val();
        const privateTrainingDuration = $('#private_training_duration').val();

        if (serviceId && date !== '-' && petId) {
          const secondaryServiceIds = $('#secondary_services').val() || [];
          populateTimeSlots(serviceId, date, petId, daycareDuration, privateTrainingDuration, secondaryServiceIds);
        }
      });

      $('#time_slot').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const slotDataAttr = selectedOption.attr('data-slot-data');
        if (slotDataAttr) {
          const slotData = JSON.parse(decodeURIComponent(slotDataAttr));
          $('#time_slot_data').val(JSON.stringify(slotData));
        } else {
          $('#time_slot_data').val('');
        }
      });

      var selectedServiceId = $('#service').val();
      if (selectedServiceId) {
        checkServiceType(selectedServiceId);
        // Keep persisted appointment selections only on initial page load.
        updateAdditionalServices(selectedServiceId, true);

        const isAlaCarte = $('#secondary_services_group').is(':visible');
        if (isAlaCarte) {
          const date = $('#button_cally_target').text();
          const petId = $('#pet').val();
          const secondaryServiceIds = $('#secondary_services').val() || [];

          if (date !== '-' && petId && secondaryServiceIds.length > 0) {
            populateTimeSlots(selectedServiceId, date, petId, '', '', secondaryServiceIds);
          }
        }
      }

      $('#daycare_duration').on('change', function() {
        const daycareDuration = $(this).val();
        const serviceId = $('#service').val();
        const date = $('#button_cally_target').text();
        const petId = $('#pet').val();
        const secondaryServiceIds = $('#secondary_services').val() || [];

        populateTimeSlots(serviceId, date, petId, daycareDuration, '', secondaryServiceIds);
      });

      $('#private_training_duration').on('change', function() {
        const privateTrainingDuration = $(this).val();
        const serviceId = $('#service').val();
        const date = $('#button_cally_target').text();
        const petId = $('#pet').val();
        const secondaryServiceIds = $('#secondary_services').val() || [];

        populateTimeSlots(serviceId, date, petId, '', privateTrainingDuration, secondaryServiceIds);
      });
    });

    function changeService(ele) {
      const serviceId = $(ele).val();
      const date = $('#button_cally_target').text();
      const petId = $('#pet').val();
      const daycareDuration = $('#daycare_duration').val();
      const privateTrainingDuration = $('#private_training_duration').val();
      const service = window.servicesData.find(function(s) { return s.id == serviceId; });
      const categoryName = service && service.category_name ? service.category_name.toLowerCase() : '';

      // Clear stale selections when moving away from Group Class / Package services.
      if (!categoryName.includes('group')) {
        $('#group_classes').val(null).trigger('change');
        $('#group_classes_details').empty();
      }

      if (!categoryName.includes('package')) {
        $('#packages').val(null).trigger('change');
        $('#packages_details').empty();
        $('#customer_package_id').val('');
      }

      checkServiceType(serviceId);

      const secondaryServiceIds = $('#secondary_services').val() || [];
      populateTimeSlots(serviceId, date, petId, daycareDuration, privateTrainingDuration, secondaryServiceIds);
      // On manual service changes, do not keep stale additional-service selections.
      updateAdditionalServices(serviceId, false);
    }

    function checkServiceType(serviceId) {
      $('#daycare_duration').val('');
      $('#private_training_duration').val('');

      // Reset all conditional groups first to avoid stale UI state across service switches.
      $('#daycare_duration_group').addClass('hidden');
      $('#private_training_duration_group').addClass('hidden');
      $('#group_classes_group').addClass('hidden');
      $('#packages_group').addClass('hidden');
      $('#additional_services_group').addClass('hidden');
      $('#secondary_services_group').addClass('hidden');
      $('#date_group').addClass('hidden');
      $('#time_slot_group').addClass('hidden');
      $('#boarding_start_group').addClass('hidden');
      $('#boarding_end_group').addClass('hidden');
      $('#staff_group').addClass('hidden');

      if (!serviceId) {
        return;
      }

      const service = window.servicesData.find(function(s) { return s.id == serviceId; });

      if (service && service.category_name && service.category_name.toLowerCase().includes('daycare')) {
        $('#daycare_duration_group').removeClass('hidden');
        $('#additional_services_group').removeClass('hidden');
        $('#date_group').removeClass('hidden');
        $('#time_slot_group').removeClass('hidden');
        $('#staff_group').removeClass('hidden');
      } else if (service && service.category_name && service.category_name.toLowerCase().includes('group')) {
        $('#additional_services_group').removeClass('hidden');
        $('#group_classes_group').removeClass('hidden');
        $('#staff_group').removeClass('hidden');
      } else if (service && service.category_name && service.category_name.toLowerCase().includes('training')) {
        $('#private_training_duration_group').removeClass('hidden');
        $('#additional_services_group').removeClass('hidden');
        $('#date_group').removeClass('hidden');
        $('#time_slot_group').removeClass('hidden');
        $('#staff_group').removeClass('hidden');
      } else if (service && service.category_name && service.category_name.toLowerCase().includes('carte')) {
        $('#additional_services_group').removeClass('hidden');
        $('#secondary_services_group').removeClass('hidden');
        $('#date_group').removeClass('hidden');
        $('#time_slot_group').removeClass('hidden');
        $('#staff_group').removeClass('hidden');
      } else if (service && service.category_name && service.category_name.toLowerCase().includes('boarding')) {
        $('#additional_services_group').removeClass('hidden');
        $('#boarding_start_group').removeClass('hidden');
        $('#boarding_end_group').removeClass('hidden');
        $('#staff_group').removeClass('hidden');
      } else if (service && service.category_name && service.category_name.toLowerCase().includes('package')) {
        $('#additional_services_group').removeClass('hidden');
        $('#packages_group').removeClass('hidden');
        $('#date_group').removeClass('hidden');

        const customerId = $('#customer').val();
        if (customerId) {
          loadCustomerPackages(customerId);
        }
      } else {
        $('#additional_services_group').removeClass('hidden');
        $('#date_group').removeClass('hidden');
        $('#time_slot_group').removeClass('hidden');
        $('#staff_group').removeClass('hidden');
      }
    }

    function updateAdditionalServices(selectedServiceId, preserveSelection = true) {
      var currentValues = $('#additional_services').val() || [];

      if (currentValues.includes(selectedServiceId)) {
        var newValues = currentValues.filter(function(value) {
          return value !== selectedServiceId;
        });
        currentValues = newValues;
      }

      try {
        $('#additional_services').select2('destroy');
      } catch (e) {
        console.error('Failed to destroy select2 for additional services.');
      }

      $('#additional_services').html(window.originalAdditionalOptions);

      // Get the selected service to determine category
      var service = window.servicesData.find(function(s) { return s.id == selectedServiceId; });
      const categoryName = service ? (service.category_name || '').toLowerCase() : '';

      // Filter additional services based on service category
      if (categoryName.includes('daycare') || categoryName.includes('boarding')) {
        // For daycare and boarding: show grooming (secondary level) and training services
        $('#additional_services option').each(function() {
          var optionVal = $(this).val();
          var additionalService = window.additionalServicesData.find(function(s) { return String(s.id) === String(optionVal); });
          if (additionalService) {
            const catName = (additionalService.category_name || '').toLowerCase();
            const isGroomingSecondary = (catName.includes('groom') || catName.includes('chauffeur')) && additionalService.level === 'secondary';
            const isTraining = catName.includes('training');
            if (!isGroomingSecondary && !isTraining) {
              $(this).remove();
            }
          }
        });
      } else if (categoryName.includes('training')) {
        // For private training: show only grooming services (secondary level)
        $('#additional_services option').each(function() {
          var optionVal = $(this).val();
          var additionalService = window.additionalServicesData.find(function(s) { return String(s.id) === String(optionVal); });
          if (additionalService) {
            const catName = (additionalService.category_name || '').toLowerCase();
            const isGroomingSecondary = catName.includes('groom') && additionalService.level === 'secondary';
            if (!isGroomingSecondary) {
              $(this).remove();
            }
          }
        });
      } else if (categoryName.includes('grooming') || categoryName.includes('groom')) {
        // For grooming: only allow secondary grooming services
        $('#additional_services option').each(function() {
          var optionVal = $(this).val();
          var additionalService = window.additionalServicesData.find(function(s) { return String(s.id) === String(optionVal); });
          if (additionalService) {
            const catName = (additionalService.category_name || '').toLowerCase();
            const isGroomingSecondary = (catName.includes('groom') || catName.includes('chauffeur')) && additionalService.level === 'secondary';
            if (!isGroomingSecondary) {
              $(this).remove();
            }
          }
        });
      } else if (categoryName.includes('carte') || categoryName.includes('package') || categoryName.includes('group')) {
        $('#additional_services option').each(function() {
          var optionVal = $(this).val();
          var additionalService = window.additionalServicesData.find(function(s) { return String(s.id) === String(optionVal); });
          if (additionalService) {
            const catName = (additionalService.category_name || '').toLowerCase();
            const isGroomingSecondary = catName.includes('chauffeur') && additionalService.level === 'secondary';
            if (!isGroomingSecondary) {
              $(this).remove();
            }
          }
        });
      }

      // Always remove the selected service from the list
      if (selectedServiceId) {
        var removedOption = $('#additional_services option[value="' + selectedServiceId + '"]');
        removedOption.remove();
      }

      $('#additional_services').select2({
        placeholder: "Choose additional services (optional)",
        allowClear: true,
        multiple: true,
        width: '100%',
        closeOnSelect: false
      });

      if (preserveSelection && currentValues.length > 0) {
        const validValues = currentValues.filter(function(value) {
          return $('#additional_services option[value="' + value + '"]').length > 0;
        });
        $('#additional_services').val(validValues).trigger('change');
      } else {
        $('#additional_services').val(null).trigger('change');
      }
    }

    function populateTimeSlots(serviceId, date, petId, daycareDuration = '', privateTrainingDuration = '', secondaryServiceIds = []) {
      if (!serviceId || date === '-' || !petId) {
        $('#time_slot').empty();
        $('#time_slot').append('<option value="" hidden selected>Choose a time slot</option>');
        return;
      }

      const isAlaCarte = $('#secondary_services_group').is(':visible');
      if (isAlaCarte && (!secondaryServiceIds || secondaryServiceIds.length === 0)) {
        secondaryServiceIds = $('#secondary_services').val() || [];
      }

      $.ajax({
        url: '{{ route("get-appointment-timeslots") }}',
        method: 'POST',
        data: {
          service_id: serviceId,
          date: date,
          pet_id: petId,
          daycare_duration: daycareDuration,
          private_training_duration: privateTrainingDuration,
          secondary_service_ids: secondaryServiceIds
        },
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        dataType: 'json',
        success: function(timeSlots) {
          $('#time_slot').empty();
          $('#time_slot').append('<option value="" hidden selected>Choose a time slot</option>');

          if (timeSlots.length === 0) {
            $('#time_slot').append('<option value="" disabled>No available time slots</option>');
          } else {
            $.each(timeSlots, function(index, slot) {
              let displayText = '';
              if (slot.is_virtual && slot.optimized_service_order) {
                // For ala carte, show the optimized service order
                const services = slot.optimized_service_order.map(function(s) {
                  return s.service_name + ' (' + formatTimeToAMPM(s.start_time) + ' - ' + formatTimeToAMPM(s.end_time) + ')';
                }).join(', ');
                displayText = formatTimeToAMPM(slot.start_time) + ' - ' + formatTimeToAMPM(slot.end_time) + ' (' + services + ')';
              } else {
                const start = formatTimeToAMPM(slot.start_time);
                const end = formatTimeToAMPM(slot.end_time);
                displayText = start + ' - ' + end;
              }
              const disabled = slot.status !== 'available' ? 'disabled' : '';
              const slotValue = slot.is_virtual ? slot.start_time : (slot.id || slot.start_time);

              let isSelected = false;

              @if($appointment->start_time)
                const appointmentStartTime = "{{ $appointment->start_time }}";

                if (slot.is_virtual) {
                  @if($appointment->metadata && isset($appointment->metadata['used_slot_ids']))
                    const appointmentUsedSlots = @json(explode(',', $appointment->metadata['used_slot_ids']));
                    if (slot.start_time === appointmentStartTime && slot.used_slot_ids && slot.used_slot_ids.length > 0) {
                      const slotIdsMatch = slot.used_slot_ids.every(function(id) {
                        return appointmentUsedSlots.includes(String(id));
                      }) && slot.used_slot_ids.length === appointmentUsedSlots.length;
                      if (slotIdsMatch) {
                        isSelected = true;
                      }
                    }
                  @else
                    isSelected = slot.start_time === appointmentStartTime;
                  @endif
                } else {
                  isSelected = (slot.id && slot.id == "{{ $timeSlots->firstWhere('start_time', $appointment->start_time)->id ?? '' }}") ||
                              slot.start_time === appointmentStartTime;
                }
              @endif

              $('#time_slot').append('<option value="' + slotValue + '" ' + disabled + (isSelected ? ' selected' : '') + ' data-slot-data="' + encodeURIComponent(JSON.stringify(slot)) + '">' + displayText + '</option>');

              if (isSelected && slot.is_virtual) {
                $('#time_slot_data').val(JSON.stringify(slot));
              }
            });
          }
        },
        error: function() {
          console.error('Failed to fetch time slots for the selected service and date.');
        }
      });
    }

    function formatTimeToAMPM(timeStr) {
      // timeStr is '09:00:00'
      const [hours, minutes, seconds] = timeStr.split(':');
      const date = new Date();
      date.setHours(hours, minutes, seconds || 0);
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    function saveAppointment() {
      const selectedStatus = $('#appointment_status').val();
      const currentStatus = '{{ $appointment->status }}';
      
      if (selectedStatus === 'cancelled' || selectedStatus === 'no_show') {
        const statusText = selectedStatus === 'cancelled' ? 'cancel' : 'mark as no show';
        $('#confirm_message').text(`Are you sure you want to ${statusText} this appointment?`);
        
        $('#confirm_modal .btn-primary').off('click').on('click', function() {
          confirm_modal.close();
          $('#form_status').val(selectedStatus);
          proceedWithFormSubmission();
        });
        
        confirm_modal.showModal();
        return;
      } else if (selectedStatus === '' && (currentStatus === 'cancelled' || currentStatus === 'no_show')) {
        $('#form_status').val('checked_in');
        proceedWithFormSubmission();
        return;
      }

      if (selectedStatus) {
        $('#form_status').val(selectedStatus === '' ? 'checked_in' : selectedStatus);
      }
      proceedWithFormSubmission();
    }

    function proceedWithFormSubmission() {
      const customer = $('#customer').val();
      const pet = $('#pet').val();
      const service = $('#service').val();
      const date = $('#button_cally_target').text();
      const timeSlot = $('#time_slot').val();
      const isDaycare = $('#daycare_duration_group').is(':visible');
      const daycareDuration = $('#daycare_duration').val();
      const isPrivateTraining = $('#private_training_duration_group').is(':visible');
      const privateTrainingDuration = $('#private_training_duration').val();
      const isGroupClasses = $('#group_classes_group').is(':visible');
      const groupClassesSelected = $('#group_classes').val() || [];
      const isPackage = $('#packages_group').is(':visible');
      const packageSelected = $('#packages').val();
      const isAlaCarte = $('#secondary_services_group').is(':visible');
      const secondaryServicesSelected = $('#secondary_services').val() || [];

      const isBoarding = $('#boarding_start_group').is(':visible');
      const boardingStart = $('#boarding_start_datetime').val();
      const boardingEnd = $('#boarding_end_datetime').val();

      if (!customer || !pet || !service) {
        $('#alert_message').text('Please fill in all required fields.');
        alert_modal.showModal();
        return;
      }

      if (isAlaCarte && secondaryServicesSelected.length === 0) {
        $('#alert_message').text('Please select at least one secondary service for ala carte.');
        alert_modal.showModal();
        return;
      }

      if (isPackage && !packageSelected) {
        $('#alert_message').text('Please select a package.');
        alert_modal.showModal();
        return;
      }

      if (!isGroupClasses && !isPackage && (!date || !timeSlot) && !isBoarding) {
        $('#alert_message').text('Please select a date and time slot.');
        alert_modal.showModal();
        return;
      }

      if (isGroupClasses && groupClassesSelected.length === 0) {
        $('#alert_message').text('Please select at least one group class.');
        alert_modal.showModal();
        return;
      }

      if (isPackage && !date) {
        $('#alert_message').text('Please select a date for the package.');
        alert_modal.showModal();
        return;
      }

      if (!isGroupClasses && !isPackage && isDaycare && !daycareDuration) {
        $('#alert_message').text('Please select Half Day or Full Day for daycare service.');
        alert_modal.showModal();
        return;
      }

      if (!isGroupClasses && !isPackage && isPrivateTraining && !privateTrainingDuration) {
        $('#alert_message').text('Please select Half Hour or One Hour for private training service.');
        alert_modal.showModal();
        return;
      }

      if (isGroupClasses || isBoarding) {
        $('#date').val('');
        $('#time_slot').val('').trigger('change');
      } else if (isPackage) {
        // For packages, set the date but clear time slot
        if (date) {
          $('#date').val(date);
        }
        $('#time_slot').val('').trigger('change');
      } else {
        if (date) {
          $('#date').val(date);
        }
      }

      if (isBoarding) {
        if (!boardingStart || !boardingEnd) {
          $('#alert_message').text('Please select both drop off and pick up date/time for boarding service.');
          alert_modal.showModal();
          return;
        }
        if (new Date(boardingStart) >= new Date(boardingEnd)) {
          $('#alert_message').text('Pick up date/time must be after drop off date/time for boarding service.');
          alert_modal.showModal();
          return;
        }
      }

      const packageId = isPackage && packageSelected ? packageSelected : null;

      $.ajax({
        url: '{{ route("get-validation-info") }}',
        method: 'POST',
        data: {
          pet_id: pet,
          service_id: service,
          package_id: packageId,
        },
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        dataType: 'json',
        success: function(response) {
          let validationMessage = '';
          if (!response.owner_status) {
            validationMessage += `<li>Pet owner's profile is inactive.</li>`;
          }
          if (!response.vaccine_status) {
            validationMessage += '<li>Pet vaccination records is not approved.</li>';
          }
          if (!response.questionnaire_status) {
            if (isPackage) {
              validationMessage += '<li>Pet questionnaire for daycare or grooming (as required by the package) is not approved.</li>';
            } else {
              validationMessage += '<li>Pet questionnaire is not approved.</li>';
            }
          }
          if (validationMessage) {
            validationMessage = `Please address the following issues before creating the appointment:<br>
              <ul style="list-style: disc; font-size: 14px; padding-left: 24px; padding-top: 6px;">${validationMessage}</ul>`;
            $('#confirm_message').html(validationMessage);
            confirm_modal.showModal();
          } else {
            const selectedStatus = $('#appointment_status').val();
            if (selectedStatus) {
              $('#form_status').val(selectedStatus === '' ? 'checked_in' : selectedStatus);
            }
            $('#update_form').submit();
          }
        },
        error: function() {
          console.error('Failed to validate appointment details.');
          $('#alert_message').text('An error occurred while validating the appointment. Please try again.');
          alert_modal.showModal();
        }
      });
    }

    function confirmAction() {
      const selectedStatus = $('#appointment_status').val();
      if (selectedStatus) {
        $('#form_status').val(selectedStatus === '' ? 'checked_in' : selectedStatus);
      }
      $('#update_form').submit();
    }

    function populateAllPackagesOptions() {
      $('#packages').empty();
      $('#packages').append('<option value="" hidden selected>Choose a package</option>');

      const packages = window.packagesData || [];
      $.each(packages, function(index, pkg) {
        const option = $('<option></option>')
          .attr('value', pkg.id)
          .attr('data-customer-package-id', '')
          .attr('data-package', JSON.stringify(pkg))
          .text(pkg.name);
        $('#packages').append(option);
      });

      $('#packages').trigger('change');
    }

    function loadCustomerPackages(customerId) {
      $.ajax({
        url: '{{ url("/appointment/customer-packages") }}/' + customerId,
        type: 'GET',
        dataType: 'json',
        success: function(customerPackages) {
          if (!customerPackages || customerPackages.length === 0) {
            populateAllPackagesOptions();
            return;
          }

          $('#packages').empty();
          $('#packages').append('<option value="" hidden selected>Choose a package</option>');

          $.each(customerPackages, function(index, cp) {
            const option = $('<option></option>')
              .attr('value', cp.id)
              .attr('data-customer-package-id', cp.customer_package_id || '')
              .attr('data-package', JSON.stringify(cp))
              .text(cp.name + (cp.remaining_days ? ' (Remaining: ' + cp.remaining_days + ' days)' : ''));
            $('#packages').append(option);
          });

          $('#packages').trigger('change');
        },
        error: function() {
          console.error('Failed to fetch customer packages.');
          populateAllPackagesOptions();
        }
      });
    }

    function renderGroupClassDetails() {
      const selectedIds = $('#group_classes').val() || [];
      const detailsDiv = $('#group_classes_details');
      detailsDiv.empty();
      if (selectedIds.length === 0) {
        return;
      }
      const classes = [
        @isset($groupClasses)
        @foreach($groupClasses as $gc)
          { id: '{{ $gc->id }}', name: '{{ addslashes($gc->name) }}', price: '{{ number_format($gc->price, 2) }}', duration: '{{ $gc->duration_amount . " " . $gc->duration_unit }}', schedule: '{{ addslashes($gc->schedule) }}', started_at: '{{ \Carbon\Carbon::parse($gc->started_at)->format('M d, Y') }}', description: `{!! addslashes($gc->description) !!}` },
        @endforeach
        @endisset
      ];
      selectedIds.forEach(function(id) {
        const c = classes.find(x => String(x.id) === String(id));
        if (c) {
          const html = `
            <div class="p-3 border border-base-300 rounded-box">
              <p class="font-medium">${c.name} - $${c.price}</p>
              <p class="text-sm text-base-content/70">Starts: ${c.started_at} | Duration: ${c.duration}</p>
              <p class="text-sm text-base-content/70">Schedule: ${c.schedule}</p>
              <p class="text-sm mt-2">${c.description || ''}</p>
            </div>
          `;
          detailsDiv.append(html);
        }
      });
    }

    function renderPackageDetails() {
      const selectedId = $('#packages').val();
      const detailsDiv = $('#packages_details');
      detailsDiv.empty();
      if (!selectedId) {
        return;
      }
      
      const packageData = window.packagesData.find(function(p) { return String(p.id) === String(selectedId); });
      
      if (packageData) {
        let servicesList = 'No services';
        if (packageData.service_ids) {
          const serviceIds = packageData.service_ids.split(',').map(id => id.trim()).filter(id => id);
          const serviceNames = [];
          serviceIds.forEach(function(id) {
            const service = window.servicesData.find(function(s) { return String(s.id) === String(id); });
            if (service) {
              serviceNames.push(service.name);
            }
          });
          if (serviceNames.length > 0) {
            servicesList = serviceNames.join(', ');
          } else {
            servicesList = serviceIds.length + ' service(s)';
          }
        }
        const html = `
          <div class="p-3 border border-base-300 rounded-box">
            <p class="font-medium">${packageData.name} - $${parseFloat(packageData.price).toFixed(2)}</p>
            ${packageData.days ? `<p class="text-sm text-base-content/70">Duration: ${packageData.days} day(s)</p>` : ''}
            <p class="text-sm text-base-content/70">Services: ${servicesList}</p>
            ${packageData.description ? `<p class="text-sm mt-2">${packageData.description}</p>` : ''}
          </div>
        `;
        detailsDiv.append(html);
      } else {
        console.error('Package data not found for ID:', selectedId);
      }
    }
  </script>
@endsection