@extends('layouts.main')
@section('title', 'Pets')

@section('page-css')
<style>
  .pet-behavior-tooltip-wrap {
    position: relative;
    display: inline-flex;
  }

  .pet-behavior-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    color: var(--color-base-content);
    cursor: pointer;
  }

  .pet-behavior-icon svg,
  .pet-behavior-icon .iconify {
    width: 1rem;
    height: 1rem;
    display: block;
  }

  .pet-behavior-tooltip {
    position: absolute;
    left: 50%;
    bottom: calc(100% + 0.3rem);
    transform: translateX(-50%);
    z-index: 30;
    width: 150px;
    padding: 0.25rem 0.45rem;
    border-radius: 0.35rem;
    background: color-mix(in oklab, var(--color-base-content) 88%, black);
    color: var(--color-base-100);
    font-size: 0.72rem;
    line-height: 1.2;
    white-space: normal;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  }

  .pet-behavior-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border-width: 4px;
    border-style: solid;
    border-color: color-mix(in oklab, var(--color-base-content) 88%, black) transparent transparent transparent;
  }

  .pet-behavior-tooltip.hidden {
    display: none;
  }
</style>
@endsection

@section('content')
<div class="flex items-center justify-between">
  <h3 class="text-lg font-medium">Pets Overview</h3>
  <div class="breadcrumbs hidden p-0 text-sm sm:inline">
    <ul>
      <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
      <li>Pets</li>
    </ul>
  </div>
</div>
<div class="mt-3">
  @include('layouts.alerts')
  <div class="card bg-base-100 shadow mt-3">
    <div class="card-body p-0">
      <div class="flex items-center justify-between px-5 pt-5">
        <div class="inline-flex items-center gap-3">
          <label class="input input-sm">
            <span class="iconify lucide--search text-base-content/80 size-3.5"></span>
            <input class="w-24 sm:w-36" placeholder="Search pets" aria-label="Search pets" type="search" onkeydown="handleSearch(event)" value="{{ $search }}"/>
          </label>
        </div>
        @if (hasPermission(2, 'can_create'))
        <a aria-label="Create seller link" class="btn btn-primary btn-sm max-sm:btn-square" href="{{ route('add-pet') }}">
          <span class="iconify lucide--plus size-4"></span>
          <span class="hidden sm:inline">New Pet</span>
        </a>
        @endif
      </div>
      <div class="mt-4 overflow-auto">
        <table class="table">
          <thead>
            <tr>
              <th>No</th>
              <th>Name</th>
              <th>Owner</th>
              <th>Birth Date/Age</th>
              <th>Breed</th>
              <th>Weight</th>
              <th>Color</th>
              <th>Vaccine Status</th>
              <th>Behavior</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($pets as $pet)
            <tr class="hover:bg-base-200/40 *:text-nowrap">
              <td class="font-medium">{{ $loop->iteration }}</td>
              <td>
                <div class="flex items-center space-x-3 truncate">
                  @if (empty($pet->pet_img))
                  <img src="{{ asset('images/no_image.jpg') }}" alt="Pet Image" class="mask mask-squircle bg-base-200 size-10">
                  @else
                  <img src="{{ asset('storage/pets/'. $pet->pet_img) }}" alt="Pet Image" class="mask mask-squircle bg-base-200 size-10">
                  @endif
                  <div>
                    <p class="font-medium">
                      <span>{{ $pet->name }}</span>
                      @if ($pet->rating === 'green')
                      <i class="fa-solid fa-star" style="color: lightseagreen"></i>
                      @elseif ($pet->rating === 'yellow')
                      <i class="fa-solid fa-star" style="color: gold"></i>
                      @elseif ($pet->rating === 'red')
                      <i class="fa-solid fa-star" style="color: tomato"></i>
                      @endif
                    </p>
                    <p class="text-base-content/60 text-xs capitalize">{{ $pet->sex }}</p>
                  </div>
                </div>
              </td>
              <td class="cursor-pointer">
                <a href="{{ route('edit-customer', ['id' => $pet->owner->id]) }}">
                  <div class="truncate">
                    <p class="font-medium">{{ $pet->owner->profile->first_name . ' ' . $pet->owner->profile->last_name }}</p>
                    <p class="text-base-content/60 text-xs capitalize">{{ $pet->owner->profile->phone_number_1 }}</p>
                  </div>
                </a>
              </td>
              @if ($pet->birthdate)
              <td>{{ \Carbon\Carbon::parse($pet->birthdate)->format('m/d/Y') }}</td>
              @else
              <td>{{ $pet->age }} years</td>
              @endif
              <td>{{ $pet->breed->name }}</td>
              <td>{{ $pet->weight }} lbs</td>
              <td>{{ $pet->color->name }}</td>
              <td>
                @if ($pet->vaccine_status === 'missing')
                <span class="badge badge-error badge-sm">Missing</span>
                @elseif ($pet->vaccine_status === 'submitted')
                <span class="badge badge-warning badge-sm">Submitted</span>
                @elseif ($pet->vaccine_status === 'approved')
                <span class="badge badge-primary badge-sm">Approved</span>
                @elseif ($pet->vaccine_status === 'expired')
                <span class="badge badge-neutral badge-sm">Expired</span>
                @else
                <span class="badge badge-neutral badge-sm">Rejected</span>
                @endif
              </td>
              <td>
                @php
                  $behaviorIds = is_array($pet->pet_behavior_id ?? null)
                    ? $pet->pet_behavior_id
                    : (!empty($pet->pet_behavior_id) ? [$pet->pet_behavior_id] : []);

                  $behaviorItems = collect($behaviorIds)
                    ->map(function ($id) use ($behaviorMap) {
                      $behavior = $behaviorMap[(int) $id] ?? null;
                      if (!$behavior) {
                        return null;
                      }

                      $iconMarkup = $behavior->icon->icon ?? '';
                      if (!empty($iconMarkup) && str_contains($iconMarkup, '&lt;')) {
                        $iconMarkup = html_entity_decode($iconMarkup, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                      }

                      if (!empty($iconMarkup) && !str_starts_with(ltrim($iconMarkup), '<') && str_contains($iconMarkup, 'iconify')) {
                        $iconMarkup = '<span class="' . e($iconMarkup) . '"></span>';
                      }

                      return [
                        'description' => $behavior->description,
                        'icon' => $iconMarkup,
                      ];
                    })
                    ->filter()
                    ->values();
                @endphp

                @if(!($behaviorItems->isEmpty()))
                  <div class="flex flex-wrap gap-1">
                    @foreach($behaviorItems as $behavior)
                      <span class="pet-behavior-tooltip-wrap">
                        <button type="button" class="pet-behavior-icon" onclick="toggleBehaviorTooltip(event, this)" aria-label="Show behavior description">
                          {!! !empty($behavior['icon']) ? $behavior['icon'] : '<span class="iconify lucide--circle-help"></span>' !!}
                        </button>
                        <span class="pet-behavior-tooltip hidden">{{ $behavior['description'] }}</span>
                      </span>
                    @endforeach
                  </div>
                @endif
              </td>
              <td>
                <div class="inline-flex w-fit">
                  @if (hasPermission(2, 'can_update'))
                  <a aria-label="Edit seller link" class="btn btn-square btn-ghost btn-sm" href="{{ route('edit-pet', ['id' => $pet->id]) }}">
                    <span class="iconify lucide--pencil text-base-content/80 size-4"></span>
                  </a>
                  @endif
                  @if (hasPermission(2, 'can_delete'))
                  <button aria-label="Dummy delete seller" onclick="confirmDelete({{ $pet }})" class="btn btn-square btn-error btn-outline btn-sm border-transparent">
                    <span class="iconify lucide--trash size-4"></span>
                  </button>
                  @endif
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      {{ $pets->links('layouts.pagination', ['items' => $pets]) }}
    </div>
  </div>
</div>
<dialog id="delete_modal" class="modal">
  <div class="modal-box">
    <div class="flex items-center justify-between text-lg font-medium">
      Confirm Delete
      <form method="dialog">
        <button class="btn btn-sm btn-ghost btn-circle" aria-label="Close modal">
          <span class="iconify lucide--x size-4"></span>
        </button>
      </form>
    </div>
    <p class="py-4" id="delete_modal_message"></p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost">No</button>
      </form>
      <form id="delete_form" method="POST" action="{{ route('delete-pet') }}">
        @csrf
        <input type="hidden" name="pet_id" value="" />
        <button class="btn btn-error">Delete</button>
      </form>
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>
@endsection

@section('page-js')
<script>
  function closeBehaviorTooltips() {
    document.querySelectorAll('.pet-behavior-tooltip').forEach(function (tooltip) {
      tooltip.classList.add('hidden');
    });
  }

  function toggleBehaviorTooltip(event, buttonEl) {
    event.stopPropagation();
    const tooltip = buttonEl.parentElement.querySelector('.pet-behavior-tooltip');
    if (!tooltip) {
      return;
    }

    const isHidden = tooltip.classList.contains('hidden');
    closeBehaviorTooltips();
    if (isHidden) {
      tooltip.classList.remove('hidden');
    }
  }

  document.addEventListener('click', function () {
    closeBehaviorTooltips();
  });

  function handleSearch(event) {
    if (event.key === 'Enter') {
      const searchValue = event.target.value;
      const url = `/pets?search=${encodeURIComponent(searchValue)}`;
      window.location.href = url;
    }
  }
  function confirmDelete(pet) {
    const message = `You are about to delete the pet ${pet.name}. Would you like to proceed?`;
    $('#delete_modal_message').text(message);
    $('#delete_form input[name=pet_id]').val(pet.id);
    delete_modal.showModal();
  }
</script>
@endsection