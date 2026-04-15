@extends('layouts.main')
@section('title', 'Discounts')

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
    <h3 class="text-lg font-medium">Discounts Overview</h3>
    <div class="breadcrumbs hidden p-0 text-sm sm:inline">
        <ul>
            <li><a href="{{ route('dashboard') }}">PawPrints</a></li>
            <li>Discounts</li>
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
                        <input class="w-24 sm:w-36" placeholder="Search discounts" aria-label="Search discounts"
                            type="search" onkeydown="handleSearch(event)" value="{{ $search }}" />
                    </label>
                </div>
                @if (hasPermission(30, 'can_create'))
                <a aria-label="Create seller link" class="btn btn-primary btn-sm max-sm:btn-square"
                    href="{{ route('add-discount') }}">
                    <span class="iconify lucide--plus size-4"></span>
                    <span class="hidden sm:inline">New Discount</span>
                </a>
                @endif
            </div>
            <div class="mt-4 overflow-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($discounts as $discount)
                        @php
                        $serviceIds = $discount->service_ids ?? [];
                        $customerIds = $discount->customer_ids ?? [];
                        $serviceNames = collect($serviceIds)->map(fn($id) => $serviceNameMap[$id] ?? ('#' .
                        $id))->implode(', ');
                        $customerNames = collect($customerIds)->map(fn($id) => $customerNameMap[$id] ?? ('#' .
                        $id))->implode(', ');
                        $customerItems = collect($customerIds)->map(function ($id) use ($customerDataMap,
                        $customerNameMap) {
                        $item = $customerDataMap[$id] ?? null;
                        $name = $item['name'] ?? ($customerNameMap[$id] ?? ('#' . $id));
                        $initials = strtoupper(substr(trim($name), 0, 1));

                        return [
                        'name' => $name,
                        'avatar_url' => $item['avatar_url'] ?? null,
                        'initials' => $initials,
                        ];
                        });
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration + ($discounts->currentPage() - 1) * $discounts->perPage() }}</td>
                            <td>{{ $discount->title }}</td>
                            <td>{{ ucfirst($discount->type) }}</td>
                            <td>
                                @if ($discount->type === 'percent')
                                {{ number_format((float) $discount->amount, 2) }}%
                                @else
                                ${{ number_format((float) $discount->amount, 2) }}
                                @endif
                            </td>
                            <td>
                                @if (empty($customerIds))
                                All Customers
                                @else
                                @php
                                $displayCustomers = $customerItems;
                                $remainingCount = 0;
                                if ($customerItems->count() > 3) {
                                $displayCustomers = $customerItems->take(3);
                                $remainingCount = $customerItems->count() - 3;
                                }
                                @endphp

                                <div class="avatar-group -space-x-5">
                                    @foreach ($displayCustomers as $customer)
                                    <div class="avatar" title="{{ $customer['name'] }}">
                                        @if (!empty($customer['avatar_url']))
                                        <div class="bg-base-200 w-8 rounded-full">
                                            <img alt="{{ $customer['name'] }}" src="{{ $customer['avatar_url'] }}" />
                                        </div>
                                        @else
                                        <div class="bg-base-300 w-8 rounded-full flex items-center justify-center"
                                            style="display: flex !important;">
                                            {{ $customer['initials'] }}
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach

                                    @if ($remainingCount > 0)
                                    <div class="avatar avatar-placeholder">
                                        <div class="bg-base-300 w-8 rounded-full">+{{ $remainingCount }}</div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </td>
                            <td>{{ empty($serviceIds) ? 'All Services' : $serviceNames }}</td>
                            <td>{{ $discount->start_date ? $discount->start_date->format('M d, Y h:i A') : '' }}</td>
                            <td>{{ $discount->end_date ? $discount->end_date->format('M d, Y h:i A') : '' }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if (hasPermission(30, 'can_update'))
                                    <a aria-label="Edit discount"
                                        class="btn btn-square btn-primary btn-outline btn-xs border-transparent"
                                        href="{{ route('edit-discount', ['id' => $discount->id]) }}">
                                        <span class="iconify lucide--pencil" style="font-size: 0.875rem;"></span>
                                    </a>
                                    @endif
                                    @if (hasPermission(30, 'can_delete'))

                                    <button aria-label="Dummy delete seller" onclick="confirmDelete({{ $discount }})"
                                        class="btn btn-square btn-error btn-outline btn-sm border-transparent">
                                        <span class="iconify lucide--trash size-4"></span>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-base-content/60 py-6">No discounts yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $discounts->links('layouts.pagination', ['items' => $discounts]) }}
        </div>
    </div>
</div>
{{-- Delete confirmation dialog --}}
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
        <p class="py-4" id="delete_modal_message">You are about to delete this discount. Would you like to proceed?</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-ghost">No</button>
            </form>
            <form id="delete_form" method="POST" action="{{ route('delete-discount') }}">
                @csrf
                <input type="hidden" name="discount_id" value="" />
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
function handleSearch(event) {
    if (event.key === 'Enter') {
        const searchValue = event.target.value;
        const url = `/discounts?search=${encodeURIComponent(searchValue)}`;
        window.location.href = url;
    }
}

function confirmDelete(discount) {
    const message = `You are about to delete the discount ${discount.title}. Would you like to proceed?`;
    $('#delete_modal_message').text(message);
    $('#delete_form input[name=discount_id]').val(discount.id);
    delete_modal.showModal();
}
</script>
@endsection