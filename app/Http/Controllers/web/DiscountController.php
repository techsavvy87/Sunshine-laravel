<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class DiscountController extends Controller
{
    public function listDiscounts(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $search = $request->get('search');

        $services = Service::orderBy('name')->get(['id', 'name']);

        $customers = User::whereHas('roles', function ($query) {
                $query->whereRaw('LOWER(title) = ?', ['customer']);
            })
            ->with('profile:user_id,avatar_img,first_name,last_name')
            ->orderBy('name')
            ->get(['id', 'name']);

        $discounts = Discount::query()
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $serviceNameMap = $services->pluck('name', 'id')->toArray();
        $customerNameMap = $customers->pluck('name', 'id')->toArray();
        $customerDataMap = $customers->mapWithKeys(function ($customer) {
            $fullName = trim((optional($customer->profile)->first_name ?? '') . ' ' . (optional($customer->profile)->last_name ?? ''));
            $displayName = $fullName !== '' ? $fullName : $customer->name;
            $avatarImg = optional($customer->profile)->avatar_img;

            return [
                $customer->id => [
                    'name' => $displayName,
                    'avatar_url' => $avatarImg ? asset('storage/profiles/' . $avatarImg) : null,
                ]
            ];
        })->toArray();

        return view('discounts.index', compact(
            'perPage',
            'search',
            'services',
            'customers',
            'discounts',
            'serviceNameMap',
            'customerNameMap',
            'customerDataMap'
        ));
    }

    public function addDiscount()
    {
        $services = Service::orderBy('name')->get(['id', 'name']);
        $customers = User::whereHas('roles', function ($query) {
                $query->whereRaw('LOWER(title) = ?', ['customer']);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('discounts.create', compact('services', 'customers'));
    }

    public function editDiscount($id)
    {
        $discount = Discount::findOrFail($id);
        $services = Service::orderBy('name')->get(['id', 'name']);
        $customers = User::whereHas('roles', function ($query) {
                $query->whereRaw('LOWER(title) = ?', ['customer']);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('discounts.update', compact('discount', 'services', 'customers'));
    }

    public function createDiscount(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percent,amount,fixed',
            'amount' => 'required|numeric|min:0',
            'apply_services' => 'required|in:all,specific',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'integer|exists:services,id',
            'apply_customers' => 'required|in:all,specific',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'integer|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validated['apply_services'] === 'specific' && empty($validated['service_ids'])) {
            throw ValidationException::withMessages([
                'service_ids' => 'Please select at least one service.',
            ]);
        }

        if ($validated['apply_customers'] === 'specific' && empty($validated['customer_ids'])) {
            throw ValidationException::withMessages([
                'customer_ids' => 'Please select at least one customer.',
            ]);
        }

        $normalizedType = $validated['type'] === 'amount' ? 'fixed' : $validated['type'];

        if ($normalizedType === 'percent' && (float) $validated['amount'] > 100) {
            throw ValidationException::withMessages([
                'amount' => 'Percent discount cannot be greater than 100.',
            ]);
        }

        Discount::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $normalizedType,
            'amount' => $validated['amount'],
            'service_ids' => $validated['apply_services'] === 'all' ? null : array_values(array_unique($validated['service_ids'] ?? [])),
            'customer_ids' => $validated['apply_customers'] === 'all' ? null : array_values(array_unique($validated['customer_ids'] ?? [])),
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return redirect()->route('discounts')->with(['status' => 'success', 'message' => 'Discount created successfully.']);

    }

    public function updateDiscount(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:discounts,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percent,amount,fixed',
            'amount' => 'required|numeric|min:0',
            'apply_services' => 'required|in:all,specific',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'integer|exists:services,id',
            'apply_customers' => 'required|in:all,specific',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'integer|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validated['apply_services'] === 'specific' && empty($validated['service_ids'])) {
            throw ValidationException::withMessages([
                'service_ids' => 'Please select at least one service.',
            ]);
        }

        if ($validated['apply_customers'] === 'specific' && empty($validated['customer_ids'])) {
            throw ValidationException::withMessages([
                'customer_ids' => 'Please select at least one customer.',
            ]);
        }

       if (!empty($validated['start_date']) && !empty($validated['end_date'])) {

            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            if ($endDate->lt($startDate)) {
                throw ValidationException::withMessages([
                    'end_date' => 'End Date must be after or equal to Start Date.',
                ]);
            }
        }

        if (
            (!empty($validated['start_date']) && empty($validated['end_date'])) || (empty($validated['start_date']) && !empty($validated['end_date']))
        ) {
            throw ValidationException::withMessages([
                'end_date' => 'To set a discount period, please select both Start Date and End Date. Leave both fields empty if no period is required.',
            ]);
        }

        $normalizedType = $validated['type'] === 'amount' ? 'fixed' : $validated['type'];

        if ($normalizedType === 'percent' && (float) $validated['amount'] > 100) {
            throw ValidationException::withMessages([
                'amount' => 'Percent discount cannot be greater than 100.',
            ]);
        }

        $discount = Discount::findOrFail($request->id);
        $discount->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $normalizedType,
            'amount' => $validated['amount'],
            'service_ids' => $validated['apply_services'] === 'all' ? null : array_values(array_unique($validated['service_ids'] ?? [])),
            'customer_ids' => $validated['apply_customers'] === 'all' ? null : array_values(array_unique($validated['customer_ids'] ?? [])),
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return redirect()->route('discounts')->with(['status' => 'success', 'message' => 'Discount updated successfully.']);
    }

    public function deleteDiscount(Request $request)
    {
        $request->validate([
            'discount_id' => 'required|exists:discounts,id',
        ]);

        Discount::findOrFail($request->discount_id)->delete();

        return redirect()->route('discounts')->with(['status' => 'success', 'message' => 'Discount deleted successfully.']);
    }
}