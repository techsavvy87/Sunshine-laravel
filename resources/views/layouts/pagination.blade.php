@php
  $perPageOptions = $per_page_options ?? [10, 20, 50, 100];
  $defaultPerPage = $default_per_page ?? 20;
  $currentPerPage = request('per_page') !== null && request('per_page') !== '' ? (int) request('per_page') : null;
@endphp
<div class="flex items-center justify-between p-6">
  <div class="text-base-content/80 hover:text-base-content flex gap-2 text-sm">
    <span class="hidden sm:inline">Per page</span>
    <select class="select select-xs w-18" aria-label="Per page" onchange="changePerPage(this.value)">
      @foreach($perPageOptions as $opt)
        <option value="{{ $opt }}" {{ $currentPerPage === $opt || ($currentPerPage === null && $opt == $defaultPerPage) ? 'selected' : '' }}>{{ $opt }}</option>
      @endforeach
    </select>
  </div>
  <span class="text-base-content/80 hidden text-sm lg:inline">
    Showing
    <span class="text-base-content font-medium">
      {{ $items->firstItem() }} to {{ $items->lastItem() }}
    </span>
    of {{ $items->total() }} items
  </span>
  <div class="inline-flex items-center gap-1">
    {{-- Previous Page Link --}}
    @if($items->onFirstPage())
      <button class="btn btn-circle sm:btn-sm btn-xs btn-ghost" aria-label="Prev" disabled>
        <span class="iconify lucide--chevron-left"></span>
      </button>
    @else
      <a href="{{ $items->previousPageUrl() }}" class="btn btn-circle sm:btn-sm btn-xs btn-ghost" aria-label="Prev">
        <span class="iconify lucide--chevron-left"></span>
      </a>
    @endif
    {{-- Pagination Elements --}}
    @foreach($items->getUrlRange(1, $items->lastPage()) as $page => $url)
      @if($page == $items->currentPage())
        <button class="btn btn-primary btn-circle sm:btn-sm btn-xs">
          {{ $page }}
        </button>
      @else
        <a href="{{ $url }}" class="btn btn-ghost btn-circle sm:btn-sm btn-xs">
          {{ $page }}
        </a>
      @endif
    @endforeach
    {{-- Next Page Link --}}
    @if($items->hasMorePages())
      <a href="{{ $items->nextPageUrl() }}" class="btn btn-circle sm:btn-sm btn-xs btn-ghost" aria-label="Next">
        <span class="iconify lucide--chevron-right"></span>
      </a>
    @else
      <button class="btn btn-circle sm:btn-sm btn-xs btn-ghost" aria-label="Next" disabled>
        <span class="iconify lucide--chevron-right"></span>
      </button>
    @endif
  </div>
</div>

<script>
  function changePerPage(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page when changing per_page
    window.location.href = url.toString();
  }
</script>