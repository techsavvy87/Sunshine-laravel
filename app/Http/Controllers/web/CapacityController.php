<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Capacity;
use App\Models\Service;

class CapacityController extends Controller
{
    public function list()
    {
        $allServices = Service::where('status', 'active')->get();
        $services = $allServices->filter(function ($service) {
            return !isBoardingService($service) && !isAlaCarteService($service) && !isGroupClassService($service);
        });
        $capacities = Capacity::all();
        return view('capacities.index', compact('capacities', 'services'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'capacity' => 'required|numeric',
            'notes' => 'nullable|string'
        ]);

        $capacity = new Capacity;
        $capacity->service_id = $request->service_id;
        $capacity->capacity = $request->capacity;
        $capacity->notes = $request->notes;
        $capacity->save();

        return response()->json([
            'status' => true,
            'message' => 'Capacity created successfully!',
            'result' => Capacity::with('service')->get()
        ], 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            'capacity_id' => 'required|exists:capacities,id',
            'service_id' => 'required|exists:services,id',
            'capacity' => 'required|numeric',
            'notes' => 'nullable|string'
        ]);

        $id = $request->capacity_id;
        $capacity = Capacity::findOrFail($id);
        $capacity->service_id = $request->service_id;
        $capacity->capacity = $request->capacity;
        $capacity->notes = $request->notes;
        $capacity->save();

        return response()->json([
            'status' => true,
            'message' => 'Capacity updated successfully!',
            'result' => Capacity::with('service')->get()
        ], 200);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:capacities,id'
        ]);

        $capacity = Capacity::findOrFail($request->id);
        $capacity->delete();
        return response()->json([
            'status' => true,
            'message' => 'Capacity deleted successfully!',
            'result' => Capacity::with('service')->get()
        ], 200);
    }
}
