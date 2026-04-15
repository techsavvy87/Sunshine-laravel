@extends('layouts.main')
@section('title', 'Inventory Categories')

@section('page-css')
<style>
  .table th,
  .table td {
    padding-block: 0.5rem;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Categories Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li><a href="{{ route('inventory-items') }}">Inventory</a></li>
      <li>Categories</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  <div class="mt-3 grid grid-cols-1 gap-4 xl:grid-cols-5 2xl:grid-cols-10">
    <div class="xl:col-span-2 2xl:col-span-3">
      <div class="card bg-base-100 card-border">
        <div class="card-body">
          <div class="card-title" id="category_form_title">Add Inventory Category</div>
          <input type="hidden" id="category_id" value="">
          <div class="fieldset">
            <div class="space-y-1 mt-3">
              <label class="fieldset-label" for="category_name">Name*</label>
              <input class="input w-full" id="category_name" type="text" placeholder="" />
            </div>
            <div class="space-y-1 mt-3">
              <label class="fieldset-label" for="parent_category">Parent Category</label>
              <select aria-label="Select" class="select" id="parent_category">
                <option value="" selected hidden>Pick Parent Category</option>
                @foreach ($parentCategories as $parentCategory)
                  <option value="{{ $parentCategory->id }}">{{ $parentCategory->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="mt-5 flex justify-end gap-3">
            <button class="btn btn-ghost btn-sm" id="cancel_btn" onclick="cancelCategory()">Cancel</button>
            <button class="btn btn-sm btn-primary gap-1" onclick="saveCategory()" id="save_btn">
              <span class="loading loading-spinner size-3.5" style="display:none;"></span>
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="xl:col-span-3 2xl:col-span-7">
      <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
          <div class="flex items-center justify-between px-5 pt-5">
            <div class="inline-flex items-center gap-3">
              <label class="input input-sm">
                <span class="iconify lucide--search text-base-content/80 size-3.5"></span>
                <input class="w-24 sm:w-36" placeholder="Search categories" aria-label="Search categories" type="search" onkeydown="handleSearch(event)"/>
              </label>
            </div>
          </div>
          <div class="mt-4">
            <table class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Name</th>
                  <th>Parent</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="category_list">
                @foreach ($inventoryCategories as $category)
                <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $category->name }}</td>
                  <td>{{ $category->parent ? $category->parent->name : '' }}</td>
                  <td>
                    <div class="inline-flex w-fit">
                      <button class="btn btn-square btn-primary btn-outline btn-xs border-transparent" onclick="editCategory({{ $category }})">
                        <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
                      </button>
                      <button class="btn btn-square btn-error btn-outline btn-xs border-transparent" onclick="openDeleteModal({{ $category->id }})">
                        <span class="iconify lucide--trash" style="font-size: 0.875rem;"></span>
                      </button>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<dialog id="delete_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium">
      Delete Category
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4">
      You are about to delete this inventory category. Would you like to proceed further?
    </p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <input type="hidden" id="delete_id" value="" />
      <button class="btn btn-error" onclick="deleteCategory()">Delete</button>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>
@endsection

@section('page-js')
<script>
  function saveCategory() {
    const categoryId = $('#category_id').val();
    const name = $('#category_name').val();
    const parentId = $('#parent_category').val();

    if (!name) {
      $('#alert_message').text('Please fill in all required fields.');
      alert_modal.showModal();
      return;
    }

    const data = {
      category_id: categoryId,
      name,
      parent_id: parentId,
    };

    // show loading spinner in the 'save' button and diasable buttons
    $('#save_btn .loading').css('display', 'inline-block');
    $('#save_btn').prop('disabled', true);
    // Remove the original 'Save' text from the save button
    $('#save_btn').contents().filter(function() {
      return this.nodeType === 3 && this.nodeValue.trim() === 'Save';
    }).remove();
    // Add 'Loading' text to the save button
    $('#save_btn').append('Loading');
    $('#cancel_btn').prop('disabled', true);

    $.ajax({
      url: categoryId ? '{{ route("update-inventory-category") }}' : '{{ route("create-inventory-category") }}',
      method: 'POST',
      data: data,
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      success: function(response) {
        $('#success_message').text(response.message);
        success_modal.showModal();

        $('#save_btn .loading').css('display', 'none');
        $('#save_btn').prop('disabled', false);
        // Remove 'Loading' text if present
        $('#save_btn').contents().filter(function() {
          return this.nodeType === 3 && this.nodeValue.trim() === 'Loading';
        }).remove();
        // Add 'Save' text if not present
        if ($('#save_btn').text().trim() === '') {
          $('#save_btn').append('Save');
        }
        $('#cancel_btn').prop('disabled', false);

        // Reset the form fields
        resetForm();

        // update the categories list
        updateCategories(response.result);

      },
      error: function(xhr) {
        let msg = 'An error occurred. Please try again.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        $('#alert_message').text(msg);
        alert_modal.showModal();

        $('#save_btn .loading').css('display', 'none');
        $('#save_btn').prop('disabled', false);
        // Remove 'Loading' text if present
        $('#save_btn').contents().filter(function() {
          return this.nodeType === 3 && this.nodeValue.trim() === 'Loading';
        }).remove();
        // Add 'Save' text if not present
        if ($('#save_btn').text().trim() === '') {
          $('#save_btn').append('Save');
        }
        $('#cancel_btn').prop('disabled', false);
      }
    });
  }

  function resetForm() {
    $('#category_id').val('');
    $('#category_name').val('');
    $('#parent_category').val('');

    //update the parent category dropdown
    $('#parent_category').empty();
    $('#parent_category').append('<option value="" selected hidden>Pick Parent Category</option>');
    $.ajax({
      url: '{{ route("get-parent-categories") }}',
      method: 'GET',
      success: function(response) {
        $.each(response, function(index, category) {
          $('#parent_category').append(`<option value="${category.id}">${category.name}</option>`);
        });
      }
    });

    $('#category_form_title').text('Add Category');
  }

  function updateCategories(result) {
    const categoryList = $('#category_list');
    categoryList.empty();

    $.each(result, function(index, category) {
      const row = `
        <tr class="hover:bg-base-200/40 cursor-pointer *:text-nowrap">
          <td>${index + 1}</td>
          <td style="text-align: left">${category.name}</td>
          <td>${category.parent ? category.parent.name : ''}</td>
          <td>
            <div class="inline-flex w-fit">
              <button class="btn btn-square btn-primary btn-outline btn-xs border-transparent" onclick='editCategory(${JSON.stringify(category)})'>
                <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
              </button>
              <button class="btn btn-square btn-error btn-outline btn-xs border-transparent" onclick='openDeleteModal(${category.id})'>
                <span class="iconify lucide--trash" style="font-size: 0.875rem;"></span>
              </button>
            </div>
          </td>
        </tr>`;
      categoryList.append(row);
    });
  }

  function cancelCategory() {
    resetForm();
    $('#category_form_title').text('Add Category');
  }

  function editCategory(category) {
    $('#category_id').val(category.id);
    $('#category_name').val(category.name);
    $('#parent_category').val(category.parent ? category.parent.id : '');

    $('#category_form_title').text('Edit Category');
  }

  function openDeleteModal(id) {
    $('#delete_id').val(id);
    delete_modal.showModal();
  }

  function deleteCategory() {
    delete_modal.close();

    const id = $('#delete_id').val();
    $.ajax({
      url: '{{ route("delete-inventory-category") }}',
      method: 'POST',
      data: { id: id },
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      success: function(response) {
        $('#success_message').text(response.message);
        success_modal.showModal();

        resetForm();
        // update the categories list
        updateCategories(response.result);
      },
      error: function(xhr) {
        let msg = 'An error occurred. Please try again.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        $('#alert_message').text(msg);
        alert_modal.showModal();
      }
    });
  }

  function handleSearch(event) {
    if (event.key === 'Enter') {
      const query = event.target.value.toLowerCase();
      $('#category_list tr').each(function() {
        const row = $(this);
        const text = row.text().toLowerCase();
        row.toggle(text.includes(query));
      });
    }
  }
</script>
@endsection