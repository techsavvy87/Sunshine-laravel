<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CreditType;

class CreditTypeController extends Controller
{
    public function list(Request $request)
    {
        $creditTypes = CreditType::all();

        return view('credittypes.index', compact('creditTypes'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'num' => 'required|integer|min:0',
            'credit_card_cost' => 'required|numeric',
            'cash_cost' => 'required|numeric',
            'expiration_days' => 'nullable|integer|min:0',
            'multiple_discount' => 'required|numeric|min:0|max:100'
        ]);

        $creditType = new CreditType;
        $creditType->name = $request->name;
        $creditType->num_credits = $request->num;
        $creditType->credit_card_cost = $request->credit_card_cost;
        $creditType->cash_cost = $request->cash_cost;
        $creditType->expiration_days = $request->expiration_days;
        $creditType->multiple_discount = $request->multiple_discount;
        $creditType->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Credit type created successfully!',
            'result' => CreditType::all()
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'credit_id' => 'required|exists:credit_types,id',
            'name' => 'required|string|max:255',
            'num' => 'required|integer|min:0',
            'credit_card_cost' => 'required|numeric',
            'cash_cost' => 'required|numeric',
            'expiration_days' => 'nullable|integer|min:0',
            'multiple_discount' => 'required|numeric|min:0|max:100'
        ]);

        $creditType = CreditType::findOrFail($request->credit_id);
        $creditType->name = $request->name;
        $creditType->num_credits = $request->num;
        $creditType->credit_card_cost = $request->credit_card_cost;
        $creditType->cash_cost = $request->cash_cost;
        $creditType->expiration_days = $request->expiration_days;
        $creditType->multiple_discount = $request->multiple_discount;
        $creditType->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Credit type updated successfully!',
            'result' => CreditType::all()
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:credit_types,id'
        ]);

        $creditType = CreditType::findOrFail($request->id);
        $creditType->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Credit type deleted successfully!',
            'result' => CreditType::all()
        ]);
    }
}
