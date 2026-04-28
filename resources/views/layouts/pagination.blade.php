@php
  $perPageOptions = $per_page_options ?? [10, 20, 50, 100];
  $defaultPerPage = $default_per_page ?? 20;
  $currentPerPage = request('per_page') !== null && request('per_page') !== '' ? (int) request('per_page') : null;
@endphp

<div class="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">

  {{-- Per Page --}}
  <div class="flex items-center gap-3 text-sm text-base-content/80">
    <span class="hidden sm:inline">Per page</span>

    <select class="select select-xs w-20" onchange="changePerPage(this.value)">
      @foreach($perPageOptions as $opt)
        <option value="{{ $opt }}"
          {{ $currentPerPage === $opt || ($currentPerPage === null && $opt == $defaultPerPage) ? 'selected' : '' }}>
          {{ $opt }}
        </option>
      @endforeach
    </select>

    <span class="hidden lg:inline">
      Showing
      <span class="font-medium text-base-content">
        {{ $items->firstItem() }} to {{ $items->lastItem() }}
      </span>
      of {{ $items->total() }} items
    </span>
  </div>

  {{-- Pagination --}}
  <div class="flex items-center gap-1 overflow-x-auto">

    {{-- Previous --}}
    @if ($items->onFirstPage())
      <button class="btn btn-circle btn-xs btn-ghost" disabled>
        <span class="iconify lucide--chevron-left"></span>
      </button>
    @else
      <a href="{{ $items->previousPageUrl() }}" class="btn btn-circle btn-xs btn-ghost">
        <span class="iconify lucide--chevron-left"></span>
      </a>
    @endif

    {{-- Page Numbers --}}
    @foreach ($items->onEachSide(1)->links()->elements as $element)

      {{-- "..." --}}
      @if (is_string($element))
        <span class="px-2 text-sm text-base-content/50">
          {{ $element }}
        </span>
      @endif

      {{-- Pages --}}
      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $items->currentPage())
            <button class="btn btn-primary btn-circle btn-xs">
              {{ $page }}
            </button>
          @else
            <a href="{{ $url }}" class="btn btn-ghost btn-circle btn-xs">
              {{ $page }}
            </a>
          @endif
        @endforeach
      @endif

    @endforeach

    {{-- Next --}}
    @if ($items->hasMorePages())
      <a href="{{ $items->nextPageUrl() }}" class="btn btn-circle btn-xs btn-ghost">
        <span class="iconify lucide--chevron-right"></span>
      </a>
    @else
      <button class="btn btn-circle btn-xs btn-ghost" disabled>
        <span class="iconify lucide--chevron-right"></span>
      </button>
    @endif

  </div>
</div>

<script>
  function changePerPage(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // reset page
    window.location.href = url.toString();
  }
</script>