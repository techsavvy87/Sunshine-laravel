<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\Service;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use Carbon\Carbon;

class CustomerPackageController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $search = $request->input('search');
        $packageId = $request->input('package_id');

        $packages = Package::where('status', 'active')->orderBy('name', 'asc')->get();

        $customerPackages = CustomerPackage::with(['customer.profile', 'package'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($profileQuery) use ($search) {
                            $profileQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                })->orWhereHas('package', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->when($packageId, function ($query) use ($packageId) {
                $query->where('package_id', $packageId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('customer-packages.index', compact('customerPackages', 'search', 'packages', 'packageId'));
    }

    public function add()
    {
        $packages = Package::where('status', 'active')->orderBy('created_at', 'desc')->get();
        $services = Service::where('status', 'active')->get();
        return view('customer-packages.create', compact('packages', 'services'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'issued_at' => 'required|date',
            'due_date' => 'nullable|date',
            'paid_at' => 'nullable|date',
            'status' => 'required|in:draft,sent,paid,void',
            'notes' => 'nullable|string',
            'payment_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,check,cc',
            'payment_notes' => 'nullable|string',
        ]);

        $package = Package::findOrFail($request->package_id);
        $days = intval($package->days ?? 0);

        $customerPackage = CustomerPackage::where('customer_id', $request->customer_id)
            ->where('package_id', $request->package_id)
            ->first();

        if ($customerPackage) {
            $customerPackage->original_days += $days;
            $customerPackage->remaining_days += $days;
            $customerPackage->save();
        } else {
            // Create new customer package
            $customerPackage = new CustomerPackage();
            $customerPackage->customer_id = $request->customer_id;
            $customerPackage->package_id = $request->package_id;
            $customerPackage->remaining_days = $days;
            $customerPackage->original_days = $days;
            $customerPackage->save();
        }

        // Create invoice
        $invoice = new Invoice();
        $invoice->customer_id = $request->customer_id;
        $invoice->invoice_number = $request->invoice_number;
        $invoice->first_name = $request->first_name;
        $invoice->last_name = $request->last_name;
        $invoice->email = $request->email;
        $invoice->issued_at = Carbon::parse($request->issued_at);
        $invoice->due_date = $request->due_date ? Carbon::parse($request->due_date) : null;

        if ($request->status === 'paid' && !$request->paid_at) {
            $invoice->paid_at = Carbon::now();
        } else {
            $invoice->paid_at = $request->paid_at ? Carbon::parse($request->paid_at) : null;
        }

        $invoice->status = $request->status;
        $invoice->notes = $request->notes;
        $invoice->save();

        // Create invoice item
        $item = new InvoiceItem();
        $item->invoice_id = $invoice->id;
        $item->item_name = $package->name;
        $item->price = $package->price;
        $item->item_type = 'service';
        $item->save();

        // Create transaction if paid
        if ($request->status === 'paid' && $request->payment_amount && $request->payment_method) {
            $transaction = new Transaction();
            $transaction->invoice_id = $invoice->id;
            $transaction->user_id = $request->customer_id;
            $transaction->tran_date = $invoice->paid_at ?: Carbon::now();
            $transaction->amount = $request->payment_amount;
            $transaction->payment_method = $request->payment_method;
            $transaction->notes = $request->payment_notes ?? 'Payment completed for the purchased package.';
            $transaction->save();
        }

        return redirect()->route('customer-packages')->with([
            'status' => 'success',
            'message' => 'Customer package created successfully with invoice!'
        ]);
    }

    public function edit($id)
    {
        $customerPackage = CustomerPackage::with(['customer.profile', 'package'])->findOrFail($id);
        $packages = Package::where('status', 'active')->orderBy('created_at', 'desc')->get();
        return view('customer-packages.update', compact('customerPackage', 'packages'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'customer_package_id' => 'required|exists:customer_packages,id',
            'customer_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'remaining_days' => 'required|integer|min:0',
            'original_days' => 'required|integer|min:0',
        ]);

        $customerPackage = CustomerPackage::findOrFail($request->customer_package_id);
        $customerPackage->customer_id = $request->customer_id;
        $customerPackage->package_id = $request->package_id;
        $customerPackage->remaining_days = $request->remaining_days;
        $customerPackage->original_days = $request->original_days;
        $customerPackage->save();

        return redirect()->route('customer-packages')->with([
            'status' => 'success',
            'message' => 'Customer package updated successfully!'
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'customer_package_id' => 'required|exists:customer_packages,id',
        ]);

        $customerPackage = CustomerPackage::findOrFail($request->customer_package_id);
        $customerPackage->delete();

        return redirect()->route('customer-packages')->with([
            'status' => 'success',
            'message' => 'Customer package deleted successfully!'
        ]);
    }


}
