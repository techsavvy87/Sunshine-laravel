@extends('layouts.main')
@section('title', 'Create Incident Report')

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
  <h3 class="text-lg font-medium">Create Incident Report</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('list-incident-reports', ['serviceId' => $serviceId]) }}">Incident Reports</a></li>
      <li class="opacity-80">Create</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('create-incident-report') }}" method="POST" enctype="multipart/form-data" id="create_form">
    @csrf
    <input type="hidden" name="service_id" value="{{ $serviceId }}" />
    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <div class="fieldset mt-2 grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div class="space-y-2">
            <label class="fieldset-label" for="pets">Pets involved *</label>
            <select class="select w-full" name="pets[]" id="pets" multiple></select>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="staffs">Staffs present *</label>
            <select class="select w-full" name="staffs[]" id="staffs" multiple></select>
          </div>
        </div>
        <div class="fieldset mt-4">
          <label class="fieldset-label" for="description">Description of incident</label>
          <textarea class="textarea w-full" name="description" id="description" rows="3" placeholder="Describe the incident..."></textarea>
        </div>
        <div class="fieldset mt-1">
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
              <select class="select w-full" name="injury_type" id="injury_type">
                <option value="" hidden>Choose a injury type</option>
                <option value="bite">Bite</option>
                <option value="laceration">Laceration</option>
                <option value="tear">Tear</option>
                <option value="puncture">Puncture</option>
                <option value="fracture">Fracture</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="space-y-1">
              <label class="fieldset-label" for="injury_location">Where is the injury? *</label>
              <input type="text" class="input w-full" name="injury_location" id="injury_location" placeholder="E.g., Left front leg" />
            </div>
            <div class="space-y-3">
              <label class="fieldset-label">Injury need treatment? *</label>
              <div class="flex gap-6">
                <label class="label cursor-pointer">
                  <input type="radio" name="needs_treatment" value="yes" class="radio radio-primary radio-sm">
                  <span class="label-text ml-2">Yes</span>
                </label>
                <label class="label cursor-pointer">
                  <input type="radio" name="needs_treatment" value="no" class="radio radio-primary radio-sm">
                  <span class="label-text ml-2">No</span>
                </label>
              </div>
            </div>
            <div class="space-y-3">
              <label class="fieldset-label">Is this an emergency? *</label>
              <div class="flex gap-6">
                <label class="label cursor-pointer">
                  <input type="radio" name="is_emergency" value="yes" class="radio radio-primary radio-sm">
                  <span class="label-text ml-2">Yes</span>
                </label>
                <label class="label cursor-pointer">
                  <input type="radio" name="is_emergency" value="no" class="radio radio-primary radio-sm">
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
                    <input type="radio" name="contact_owner" value="yes" class="radio radio-primary radio-sm">
                    <span class="label-text ml-2">Yes</span>
                  </label>
                  <label class="label cursor-pointer">
                    <input type="radio" name="contact_owner" value="no" class="radio radio-primary radio-sm">
                    <span class="label-text ml-2">No (left message or do not contact)</span>
                  </label>
                </div>
              </div>
              <div class="space-y-1 px-4">
                <label class="fieldset-label" for="owner_conversation_notes">If yes, notes from conversation</label>
                <textarea class="textarea w-full" name="owner_conversation_notes" id="owner_conversation_notes" rows="3" placeholder="Enter notes..."></textarea>
              </div>
            </div>
            <div class="space-y-4">
              <div class="space-y-2">
                <label class="fieldset-label">Treatment *</label>
                <div class="flex gap-6 px-4">
                  <label class="label cursor-pointer">
                    <input type="radio" name="treatment_type" value="in_house" class="radio radio-primary radio-sm">
                    <span class="label-text ml-2">In-house</span>
                  </label>
                  <label class="label cursor-pointer">
                    <input type="radio" name="treatment_type" value="vet" class="radio radio-primary radio-sm">
                    <span class="label-text ml-2">Vet</span>
                  </label>
                </div>
              </div>
              <div class="grid grid-cols-1 gap-2 xl:grid-cols-2 px-4">
                <div class="space-y-1">
                  <label class="fieldset-label" for="vet_name">Which vet</label>
                  <input class="input w-full input-sm" name="vet_name" id="vet_name" placeholder="Enter vet name..."/>
                </div>
                <div class="space-y-1">
                  <label class="fieldset-label" for="vet_bill">Bill</label>
                  <label class="input w-full focus:outline-0 input-sm">
                    <input class="grow focus:outline-0" placeholder="e.g. 100.00" id="vet_bill" name="vet_bill" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
                    <span class="badge badge-ghost badge-sm">USD</span>
                  </label>
                </div>
                <div class="space-y-1">
                  <label class="fieldset-label" for="vet_payment">Payment</label>
                  <input class="input w-full input-sm" name="vet_payment" id="vet_payment" placeholder="Enter vet payment..."/>
                </div>
                <div class="space-y-1">
                  <label class="fieldset-label" for="vet_results">Vet visit resolution</label>
                  <select class="select w-full select-sm" name="vet_results" id="vet_results">
                    <option value="" hidden>Choose a resolution</option>
                    <option value="diagnosis">Diagnosis</option>
                    <option value="prognosis">Prognosis</option>
                    <option value="treatment">Treatment</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="fieldset mt-2 space-y-1">
          <label class="fieldset-label" for="conclusion">Conclusion *</label>
          <textarea class="textarea w-full" name="conclusion" id="conclusion" rows="3" placeholder="How do we proceed after conversation with owner"></textarea>
        </div>
      </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
      <div class="flex gap-3">
        <a class="btn btn-sm btn-ghost" href="{{ url()->previous() }}">
          <span class="iconify lucide--x size-4"></span>
          Cancel
        </a>
        <button type="button" class="btn btn-sm btn-primary" onclick="createReport()">
          <span class="iconify lucide--check size-4"></span>
          Create
        </button>
      </div>
    </div>
  </form>
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

  function createReport() {
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

    $('#create_form').submit();
  }
</script>
@endsection