@extends('layouts.main')
@section('title', 'Create Group Class')

@section('page-css')
<style>
  .table th,
  .table td {
    padding-block: 0.6rem;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Create Group Class</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('group-classes') }}">Group Classes</a></li>
      <li class="opacity-80">Create</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('create-group-class') }}" method="POST" id="create_form">
    @csrf
    <div class="card bg-base-100 shadow mt-3">
      <div class="card-body">
        <div class="fieldset mt-2 grid grid-cols-1 gap-4 xl:grid-cols-2">
          <div class="space-y-2">
            <label class="fieldset-label" for="name">Class Name*</label>
            <label class="input w-full focus:outline-0">
              <input placeholder="e.g. Puppy Training Class" id="name" name="name" type="text" />
            </label>
          </div>
        </div>
        <div class="fieldset mt-2 grid grid-cols-1 gap-4 xl:grid-cols-4">
          <div class="space-y-2">
            <label class="fieldset-label" for="price">Price*</label>
            <label class="input w-full focus:outline-0">
              <input class="grow focus:outline-0" placeholder="e.g. 150.00" id="price" name="price" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" />
              <span class="badge badge-ghost badge-sm">USD</span>
            </label>
          </div>
          <div class="flex items-center gap-2">
            <div class="space-y-2">
              <label class="fieldset-label" for="duration_amount">Duration Amount*</label>
              <label class="input w-full focus:outline-0">
                <input placeholder="e.g. 6" id="duration_amount" name="duration_amount" type="text" oninput="this.value = this.value.replace(/[^0-9]/g, '')"/>
              </label>
            </div>
            <div class="space-y-2">
              <label class="fieldset-label" for="duration_unit">Duration unit*</label>
              <select class="select w-full focus:outline-0" id="duration_unit" name="duration_unit">
                <option value="days">Days</option>
                <option value="weeks" selected>Weeks</option>
                <option value="months">Months</option>
              </select>
            </div>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="started_at">Start Date & Time*</label>
            <label class="input w-full focus:outline-0">
              <input id="started_at" name="started_at" type="datetime-local" />
            </label>
          </div>
        </div>
        <div class="fieldset mt-2">
          <div class="flex items-center gap-3">
            <label class="fieldset-label">Schedule*</label>
            <button type="button" class="btn btn-soft btn-primary btn-sm px-2" onclick="addScheduleRow()">
              <span class="iconify lucide--plus size-4"></span>
            </button>
            <input type="hidden" id="schedule" name="schedule" />
          </div>
          <div class="grid grid-cols-1 gap-4 xl:grid-cols-3" id="schedule_container">
            <div class="flex items-center gap-2">
              <select class="select w-full focus:outline-0 schedule-day">
                <option>Monday</option>
                <option>Tuesday</option>
                <option>Wednesday</option>
                <option>Thursday</option>
                <option>Friday</option>
                <option>Saturday</option>
                <option>Sunday</option>
                <option>Everyday</option>
              </select>
              <input class="input w-full focus:outline-0 schedule-start-time" type="time" />
              <button type="button" class="btn btn-outline btn-error btn-sm px-2" onclick="removeScheduleRow(this)">
                <span class="iconify lucide--x size-3"></span>
              </button>
            </div>
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
    <div class="mt-6 flex justify-end gap-3">
      <a class="btn btn-sm btn-ghost" href="{{ url()->previous() }}">
        <span class="iconify lucide--x size-4"></span>
        Cancel
      </a>
      <button class="btn btn-sm btn-primary" type="button" onclick="saveGroupClass()">
        <span class="iconify lucide--check size-4"></span>
        Save
      </button>
    </div>
  </form>
</div>
@endsection

@section('page-js')
<script>
  function addScheduleRow() {
    const scheduleRow = `
      <div class="flex items-center gap-2">
        <select class="select w-full focus:outline-0 schedule-day">
          <option>Monday</option>
          <option>Tuesday</option>
          <option>Wednesday</option>
          <option>Thursday</option>
          <option>Friday</option>
          <option>Saturday</option>
          <option>Sunday</option>
          <option>Everyday</option>
        </select>
        <input class="input w-full focus:outline-0 schedule-start-time" type="time" />
        <button type="button" class="btn btn-outline btn-error btn-sm px-2" onclick="removeScheduleRow(this)">
          <span class="iconify lucide--x size-3"></span>
        </button>
      </div>
    `;
    $('#schedule_container').append(scheduleRow);
  }

  function removeScheduleRow(button) {
    $(button).closest('div.flex').remove();
  }

  function saveGroupClass() {
    const name = $('#name').val();
    const price = $('#price').val();
    const durationAmount = $('#duration_amount').val();
    const startedAt = $('#started_at').val();

    if (!name || !price || !durationAmount || !startedAt) {
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

    // validate the schedule rows
    var hasEmptyTime = false;
    $('.schedule-start-time').each(function() {
      if (!$(this).val()) {
        hasEmptyTime = true;
        return false; // break the loop
      }
    });
    if (hasEmptyTime) {
      $('#alert_message').text('Please fill in all schedule times.');
      alert_modal.showModal();
      return;
    }

    let scheduleArr = [];
    $('.schedule-day').each(function(index) {
      const day = $(this).val();
      const time = $('.schedule-start-time').eq(index).val();
      scheduleArr.push(`${day} ${time}`);
    });

    $('#schedule').val(scheduleArr.join(','));

    $('#create_form').submit();
  }
</script>
@endsection

