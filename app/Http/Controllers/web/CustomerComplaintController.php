<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerComplaint;
use App\Models\User;

class CustomerComplaintController extends Controller
{
    public function listComplaints(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $complaints = CustomerComplaint::with('customer.profile')
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
        $customers = User::whereHas('roles', function ($query) {
            $query->where('title', 'customer');
        })->with('profile')->orderBy('name')->get();
        return view('complaints.index', compact('complaints', 'customers'));
    }

    public function createComplaint(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'description' => 'required|string|max:65535',
            'date' => 'required|date',
        ]);
        CustomerComplaint::create($request->only('customer_id', 'description', 'date'));
        return redirect()->route('complaints')->with('message', 'Complaint/Issue added successfully.');
    }

    public function updateComplaint(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:customer_complaints,id',
            'customer_id' => 'required|exists:users,id',
            'description' => 'required|string|max:65535',
            'date' => 'required|date',
        ]);
        $complaint = CustomerComplaint::findOrFail($request->id);
        $complaint->update($request->only('customer_id', 'description', 'date'));
        return redirect()->route('complaints')->with('message', 'Complaint/Issue updated successfully.');
    }

    public function deleteComplaint(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:customer_complaints,id',
        ]);
        CustomerComplaint::findOrFail($request->id)->delete();
        return redirect()->route('complaints')->with('message', 'Complaint/Issue deleted successfully.');
    }
}
