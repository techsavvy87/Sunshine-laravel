@extends('layouts.main')
@section('title', 'Update Incident Report')

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
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Update Incident Report</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('list-incident-reports', ['serviceId' => $incidentReport->service_id]) }}">Incident Reports</a></li>
      <li class="opacity-80">Update</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('update-incident-report') }}" method="POST" enctype="multipart/form-data" id="update_form">
    @csrf
    <input type="hidden" name="incident_report_id" value="{{ $incidentReport->id }}"/>
    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <div class="fieldset mt-2 grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div class="space-y-2">
            <label class="fieldset-label" for="pets">Pets involved *</label>
            <select class="select w-full" name="pets[]" id="pets" multiple>
              @foreach($selectedPets as $pet)
                <option value="{{ $pet->id }}" selected>{{ $pet->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="staffs">Staffs present *</label>
            <select class="select w-full" name="staffs[]" id="staffs" multiple>
              @foreach($selectedStaffs as $staff)
                <option value="{{ $staff->id }}" selected>{{ $staff->profile->first_name }} {{ $staff->profile->last_name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="fieldset mt-4">
          <label class="fieldset-label" for="description">Description of incident</label>
          <textarea class="textarea w-full" name="description" id="description" rows="3" placeholder="Describe the incident...">{{ $incidentReport->incident_description }}</textarea>
        </div>
        <div class="fieldset mt-1">
          @if($incidentReport->pictures)
            <div class="mb-4">
              <label class="fieldset-label text-sm">Current Pictures:</label>
              <div class="mt-2 flex flex-wrap gap-2" id="current_pictures_container">
                @foreach(explode(',', $incidentReport->pictures) as $index => $picture)
                <div class="relative picture-item" data-photo="{{ $picture }}">
                  <img src="{{ asset('storage/reports/' . $picture) }}" alt="Incident Picture" class="w-24 h-24 object-cover rounded-lg border cursor-pointer" onclick="viewPicture('{{ asset('storage/reports/' . $picture) }}')">
                  <button type="button" class="absolute -top-2 -right-2 btn btn-xs btn-error btn-circle" onclick="removePicture(this, '{{ $picture }}')">
                    <span class="iconify lucide--x size-2"></span>
                  </button>
                </div>
                @endforeach
              </div>
              <input type="hidden" name="current_pictures" id="current_pictures" value="{{ $incidentReport->pictures }}"/>
            </div>
          @endif
          <fieldset class="fieldset gap-1">
            <legend class="fieldset-legend">Pictures</legend>
            <input aria-label="File" class="file-input w-full" type="file" name="pictures[]" multiple />
            <span class="fieldset-label" style="margin-left: 4px; font-size: 13px;">Upload multiple photos to record an incident</span>
          </fieldset>
        </div>
        <div class="fieldset mt-5 space-y-2">
          <label class="fieldset-label font-semibold">Triage/Assessment</label>
          <div class="grid grid-cols-1 gap-6 xl:grid-cols-4 px-1">
            <div class="space-y-1">
              <label class="fieldset-label" for="injury_type">What type of injury? *</label>
              <select class="select w-full" name="injury_type" id="injury_type" value="{{ $incidentReport->injury_type }}">
                <option value="" hidden>Choose a injury type</option>
                <option value="bite" {{ $incidentReport->injury_type === 'bite' ? 'selected' : '' }}>Bite</option>
                <option value="laceration" {{ $incidentReport->injury_type === 'laceration' ? 'selected' : '' }}>Laceration</option>
                <option value="tear" {{ $incidentReport->injury_type === 'tear' ? 'selected' : '' }}>Tear</option>
                <option value="puncture" {{ $incidentReport->injury_type === 'puncture' ? 'selected' : '' }}>Puncture</option>
                <option value="fracture" {{ $incidentReport->injury_type === 'fracture' ? 'selected' : '' }}>Fracture</option>
                <option value="other" {{ $incidentReport->injury_type === 'other' ? 'selected' : '' }}>Other</option>
              </select>
            </div>
            <div class="space-y-1">
              <label class="fieldset-label" for="injury_location">Where is the injury? *</label>
              <input type="text" class="input w-full" name="injury_location" id="injury_location" placeholder="E.g., Left front leg" value="{{ $incidentReport->injury_location }}"/>
            </div>
            <div class="space-y-3">
              <label class="fieldset-label">Injury need treatment? *</label>
              <div class="flex gap-6">
                <label class="label cursor-pointer">
                  <input type="radio" name="needs_treatment" value="yes" class="radio radio-primary radio-sm" {{ $incidentReport->needs_treatment === 'yes' ? 'checked' : '' }}>
                  <span class="label-text ml-2">Yes</span>
                </label>
                <label class="label cursor-pointer">
                  <input type="radio" name="needs_treatment" value="no" class="radio radio-primary radio-sm" {{ $incidentReport->needs_treatment === 'no' ? 'checked' : '' }}>
                  <span class="label-text ml-2">No</span>
                </label>
              </div>
            </div>
            <div class="space-y-3">
              <label class="fieldset-label">Is this an emergency? *</label>
              <div class="flex gap-6">
                <label class="label cursor-pointer">
                  <input type="radio" name="is_emergency" value="yes" class="radio radio-primary radio-sm" {{ $incidentReport->is_emergency === 'yes' ? 'checked' : '' }}>
                  <span class="label-text ml-2">Yes</span>
                </label>
                <label class="label cursor-pointer">
                  <input type="radio" name="is_emergency" value="no" class="radio radio-primary radio-sm" {{ $incidentReport->is_emergency === 'no' ? 'checked' : '' }}>
                  <span class="label-text ml-2">No</span>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="fieldset mt-5 space-y-2">
          <label class="fieldset-label font-semibold">Actions taken</label>
          <div class="grid grid-cols-1 gap-4 xl:grid-cols-2 px-1">
            <div class="space-y-4">
              <div class="space-y-2">
                <label class="fieldset-label">Contact owner? *</label>
                <div class="flex gap-6 px-4">
                  <label class="label cursor-pointer">
                    <input type="radio" name="contact_owner" value="yes" class="radio radio-primary radio-sm" {{ $incidentReport->contact_owner === 'yes' ? 'checked' : '' }}>
                    <span class="label-text ml-2">Yes</span>
                  </label>
                  <label class="label cursor-pointer">
                    <input type="radio" name="contact_owner" value="no" class="radio radio-primary radio-sm" {{ $incidentReport->contact_owner === 'no' ? 'checked' : '' }}>
                    <span class="label-text ml-2">No (left message or do not contact)</span>
                  </label>
                </div>
              </div>
              <div class="space-y-1 px-4">
                <label class="fieldset-label" for="owner_conversation_notes">If yes, notes from conversation</label>
                <textarea class="textarea w-full" name="owner_conversation_notes" id="owner_conversation_notes" rows="3" placeholder="Enter notes..." {{ $incidentReport->contact_owner === 'yes' ? '' : 'disabled' }}>{{ $incidentReport->owner_conversation_notes }}</textarea>
              </div>
            </div>
            <div class="space-y-4">
              <div class="space-y-2">
                <label class="fieldset-label">Treatment *</label>
                <div class="flex gap-6 px-4">
                  <label class="label cursor-pointer">
                    <input type="radio" name="treatment_type" value="in_house" class="radio radio-primary radio-sm" {{ $incidentReport->treatment_type === 'in_house' ? 'checked' : '' }}>
                    <span class="label-text ml-2">In-house</span>
                  </label>
                  <label class="label cursor-pointer">
                    <input type="radio" name="treatment_type" value="vet" class="radio radio-primary radio-sm" {{ $incidentReport->treatment_type === 'vet' ? 'checked' : '' }}>
                    <span class="label-text ml-2">Vet</span>
                  </label>
                </div>
              </div>
              <div class="grid grid-cols-1 gap-2 xl:grid-cols-2 px-4">
                <div class="space-y-1">
                  <label class="fieldset-label" for="vet_name">Which vet</label>
                  <input class="input w-full input-sm" name="vet_name" id="vet_name" placeholder="Enter vet name..." value="{{ $incidentReport->vet_name }}" {{ $incidentReport->treatment_type === 'vet' ? '' : 'disabled' }}/>
                </div>
                <div class="space-y-1">
                  <label class="fieldset-label" for="vet_bill">Bill</label>
                  <label class="input w-full focus:outline-0 input-sm">
                    <input class="grow focus:outline-0" placeholder="e.g. 100.00" id="vet_bill" name="vet_bill" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" value="{{ $incidentReport->vet_bill }}" {{ $incidentReport->treatment_type === 'vet' ? '' : 'disabled' }}/>
                    <span class="badge badge-ghost badge-sm">USD</span>
                  </label>
                </div>
                <div class="space-y-1">
                  <label class="fieldset-label" for="vet_payment">Payment</label>
                  <input class="input w-full input-sm" name="vet_payment" id="vet_payment" placeholder="Enter vet payment..." value="{{ $incidentReport->vet_payment }}" {{ $incidentReport->treatment_type === 'vet' ? '' : 'disabled' }}/>
                </div>
                <div class="space-y-1">
                  <label class="fieldset-label" for="vet_results">Vet visit resolution</label>
                  <select class="select w-full select-sm" name="vet_results" id="vet_results" {{ $incidentReport->treatment_type === 'vet' ? '' : 'disabled' }}>
                    <option value="" hidden>Choose a resolution</option>
                    <option value="diagnosis" {{ $incidentReport->vet_results === 'diagnosis' ? 'selected' : '' }}>Diagnosis</option>
                    <option value="prognosis" {{ $incidentReport->vet_results === 'prognosis' ? 'selected' : '' }}>Prognosis</option>
                    <option value="treatment" {{ $incidentReport->vet_results === 'treatment' ? 'selected' : '' }}>Treatment</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="fieldset mt-2 space-y-1">
          <label class="fieldset-label" for="conclusion">Conclusion *</label>
          <textarea class="textarea w-full" name="conclusion" id="conclusion" rows="3" placeholder="How do we proceed after conversation with owner">{{ $incidentReport->conclusion }}</textarea>
        </div>
      </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
      <div class="flex gap-3">
        <a class="btn btn-sm btn-ghost" href="{{ url()->previous() }}">
          <span class="iconify lucide--x size-4"></span>
          Cancel
        </a>
        <button type="button" class="btn btn-sm btn-primary" onclick="updateReport()">
          <span class="iconify lucide--check size-4"></span>
          Update
        </button>
      </div>
    </div>
  </form>
</div>
<div id="picture_modal" class="modal" onclick="closePictureModal()">
  <div class="modal-box max-w-4xl" onclick="event.stopPropagation()">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-bold">Picture View</h3>
      <button class="btn btn-sm btn-circle btn-ghost" onclick="closePictureModal()">
        <span class="iconify lucide--x size-4"></span>
      </button>
    </div>
    <div class="flex justify-center">
      <img id="modal_picture" src="" alt="Photo" class="max-w-full max-h-96 object-contain rounded">
    </div>
  </div>
</div>
@endsection

@section('page-js')
<script src="{{ asset('src/libs/select2/select2.min.js') }}"></script>
<script>
  $(document).ready(function() {
    $('#pets').select2({
      placeholder: "Choose pets",
      ajax: {
        url: '{{ route("get-pets") }}',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term // Send the search term as 'q'
          };
        },
        processResults: function (data) {
          return {
            results: data.map(function (pet) {
              return {
                id: pet.id,
                text: pet.name
              };
            })
          };
        }
      }
    });

    $('#staffs').select2({
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
                text: staff.profile.first_name + " " + staff.profile.last_name,
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
        var $container = $(`
          <div class="flex items-center gap-2">
            <span class="font-medium">${staff.first_name} ${staff.last_name}</span>
            <span class="text-sm text-base-content/70">(${staff.email} | ${staff.phone_number})</span>
          </div>
        `);
        return $container;
      },
    });

    $('input[name=contact_owner]').on('change', function() {
      if ($(this).val() === 'yes') {
        $('#owner_conversation_notes').prop('disabled', false);
      } else {
        $('#owner_conversation_notes').prop('disabled', true).val('');
      }
    });

    $('input[name=treatment_type]').on('change', function() {
      if ($(this).val() === 'vet') {
        $('#vet_name').prop('disabled', false);
        $('#vet_bill').prop('disabled', false);
        $('#vet_payment').prop('disabled', false);
        $('#vet_results').prop('disabled', false);
      } else {
        $('#vet_name').prop('disabled', true).val('');
        $('#vet_bill').prop('disabled', true).val('');
        $('#vet_payment').prop('disabled', true).val('');
        $('#vet_results').prop('disabled', true).val('');
      }
    });
  });

  function viewPicture(photoSrc) {
    var $modal = $('#picture_modal');
    var $modalPicture = $('#modal_picture');

    $modalPicture.attr('src', photoSrc);
    $modal.addClass('modal-open');
  }

  function closePictureModal() {
    var $modal = $('#picture_modal');
    $modal.removeClass('modal-open');
  }

  function removePicture(button, pictureName) {
    // Remove the picture item from the DOM
    $(button).closest('.picture-item').remove();

    // Update the hidden input field to reflect the removed picture
    var currentPictures = $('#current_pictures').val().split(',');
    currentPictures = currentPictures.filter(function(name) {
      return name !== pictureName;
    });
    $('#current_pictures').val(currentPictures.join(','));
  }

  function updateReport() {
    // validate the involved pets
    const pets = $('#pets').val();
    const staffs = $('#staffs').val();
    const injuryType = $('#injury_type').val();
    const injuryLocation = $('#injury_location').val();
    const needsTreatment = $('input[name="needs_treatment"]:checked').val();
    const isEmergency = $('input[name="is_emergency"]:checked').val();
    const contactOwner = $('input[name="contact_owner"]:checked').val();
    const treatmentType = $('input[name="treatment_type"]:checked').val();
    const conclusion = $('#conclusion').val().trim();

    if (pets.length === 0 || staffs.length === 0 || !injuryType || !injuryLocation || !needsTreatment || !isEmergency || !contactOwner || !treatmentType || !conclusion) {
      $('#alert_message').text('Please fill in all required fields.');
      alert_modal.showModal();
      return;
    }

    $('#update_form').submit();
  }
</script>
@endsection