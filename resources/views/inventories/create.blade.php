@extends('layouts.main')
@section('title', 'Create Item')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('src/libs/select2/select2.min.css') }}" />
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Create Item</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('inventory-items') }}">Items</a></li>
      <li class="opacity-80">Create</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <form action="{{ route('create-inventory-item') }}" method="POST" id="create_form">
    @csrf
    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <div class="fieldset mt-2 grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div class="space-y-2">
            <label class="fieldset-label" for="vendor">Vendor*</label>
            <input class="input w-full" placeholder="e.g. Pet Food Provider" id="vendor" name="vendor" type="text" />
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="brand">Brand*</label>
            <input class="input w-full" placeholder="e.g. Taste of the Wild" id="brand" name="brand" type="text" />
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="description">Description</label>
            <input class="input w-full" placeholder="e.g. Dog food" id="description" name="description" type="text" />
          </div>
        </div>
        <div class="fieldset mt-3 grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div class="space-y-2">
            <label class="fieldset-label" for="sku">SKU</label>
            <input class="input w-full" placeholder="e.g. SKU123" id="sku" name="sku" type="text" />
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="cost">Cost*</label>
            <label class="input w-full">
              $<input class="grow" placeholder="e.g. 123.45" id="cost" name="cost" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
            </label>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="wholesale_cost">Cost Wholesale($)*</label>
            <label class="input w-full">
              $<input class="grow" placeholder="e.g. 123.45" id="wholesale_cost" name="wholesale_cost" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
            </label>
          </div>
        </div>
        <div class="fieldset mt-3 grid grid-cols-1 gap-6 lg:grid-cols-2">
          <div class="space-y-2">
            <label class="fieldset-label" for="category">Category*</label>
            <select class="select" name="category" id="category">
              <option value="" disabled selected>Select a category</option>
              @foreach ($categories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="space-y-2">
            <label class="fieldset-label" for="par">PAR</label>
            <input class="input w-full" placeholder="e.g. 10" id="par" name="par" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"/>
          </div>
        </div>
        <div class="fieldset mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
          <div class="flex items-center gap-3">
            <input class="toggle toggle-sm" id="is_hidden" type="checkbox" name="is_hidden"/>
            <label class="label" for="is_hidden">Hidden</label>
          </div>
          <div class="flex items-center gap-3">
            <input class="toggle toggle-sm" id="is_service" type="checkbox" name="is_service"/>
            <label class="label" for="is_service">Is Service</label>
          </div>
        </div>
        <input type="hidden" name="attrs" value="">
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4 mt-4">
          <legend class="fieldset-legend bg-base-100 px-1.5 pb-0 font-medium">
            Attributes
            <button type="button" class="btn btn-square btn-primary btn-outline btn-sm border-transparent" onclick="addAttribute();">
              <span class="iconify lucide--plus size-4"></span>
            </button>
          </legend>
          <div class="fieldset grid grid-cols-4 gap-3 lg:grid-cols-4" id="attributes_container">
          </div>
        </fieldset>
      </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
      <a class="btn btn-sm btn-ghost" href="{{ route('inventory-items') }}">
        <span class="iconify lucide--x size-4"></span>
        Cancel
      </a>
      <button class="btn btn-sm btn-primary" type="button" onclick="saveItem()">
        <span class="iconify lucide--check size-4"></span>
        Save
      </button>
    </div>
  </form>
</div>
@endsection

@section('page-js')
  <script src="{{ asset('src/libs/select2/select2.min.js') }}"></script>

  <script>
    $(document).ready(function() {
      $('#category').select2({
        placeholder: "Select a category",
      });
    });

    $(document).on('click', '.remove-attribute', function(e) {
      e.preventDefault();
      $(this).closest('.attribute-block').remove();
    });

    let attributeIndex = 0;
    function addAttribute() {
      $('#attributes_container').append(getAttributeBlock(attributeIndex));
      attributeIndex++;
    }

    function getAttributeBlock(index) {
      return `
        <div class="space-y-2 attribute-block" data-index="${index}">
          <div class="space-y-1">
            <label class="fieldset-label" for="attribute_name_${index}">Name*</label>
            <input class="input input-sm w-full" id="attribute_name_${index}" placeholder="e.g. Color" type="text" />
          </div>
          <div class="space-y-1">
            <label class="fieldset-label" for="attribute_value_${index}">Value*</label>
            <input class="input input-sm w-full" id="attribute_value_${index}" placeholder="e.g. Red" type="text" />
          </div>
          <div class="flex justify-center">
            <a class="btn btn-square btn-error btn-outline btn-sm border-transparent remove-attribute" data-index="${index}">
              <span class="iconify lucide--x size-4"></span>
            </a>
          </div>
        </div>
      `;
    }

    function saveItem() {
      const vendor = $('#vendor').val();
      const brand = $('#brand').val();
      const cost = $('#cost').val();
      const wholesaleCost = $('#wholesale_cost').val();
      const category = $('#category').val();

      if (!vendor || !brand || !cost || !wholesaleCost || !category) {
        $('#alert_message').text('Please fill in all required fields.');
        alert_modal.showModal();
        return;
      }

      // validate if there is an empty attribute name or empty attribute value
      var hasEmptyAttribute = false;
      $('.attribute-block').each(function() {
        const name = $(this).find('input[id^="attribute_name_"]').val();
        const value = $(this).find('input[id^="attribute_value_"]').val();
        if (!name || !value) {
          hasEmptyAttribute = true;
          return false; // Stop further iteration
        }
      });

      if (hasEmptyAttribute) {
        $('#alert_message').text('Please fill in all attribute fields or remove empty ones.');
        alert_modal.showModal();
        return;
      }

      var attributesData = [];
      var attributeDivs = $('.attribute-block');
      for(let attributeDiv of attributeDivs) {
        var attributeName = $(attributeDiv).find('input[id^="attribute_name_"]').val();
        var attributeValue = $(attributeDiv).find('input[id^="attribute_value_"]').val();

        var attributeData = {
          name: attributeName,
          value: attributeValue
        };
        attributesData.push(attributeData);
      }
      $('input[name=attrs]').val(JSON.stringify(attributesData));

      $('#create_form').submit();
    }
  </script>
@endsection