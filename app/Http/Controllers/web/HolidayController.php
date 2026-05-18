<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\HolidayService;

class HolidayController extends Controller
{
    public function list(Request $request)
    {
        $currentYear = Carbon::now()->year;
        $holidays = Holiday::with('holidayServices.service')->whereYear('date', $currentYear)->get()->groupBy(function($holiday) {
            return Carbon::parse($holiday->date)->format('F');
        });

        $allServices = Service::where('status', 'active')->with('category')->get();
        $services = $allServices->filter(function ($service) {
            return !isBoardingService($service) && !isAlaCarteService($service) && !isGroupClassService($service) && !isPackageService($service);
        });

        return view('holidays.index', compact('holidays', 'services'));
    }

    public function add(Request $request)
    {
        $allServices = Service::where('status', 'active')->with('category')->get();
        $services = $allServices->filter(function ($service) {
            return !isBoardingService($service) && !isAlaCarteService($service) && !isGroupClassService($service) && !isPackageService($service);
        });
        return view('holidays.create', compact('services'));
    }

    public function edit(Request $request, $id)
    {
        $holiday = Holiday::with('holidayServices.service')->findOrFail($id);
        $allServices = Service::where('status', 'active')->with('category')->get();
        $services = $allServices->filter(function ($service) {
            return !isBoardingService($service) && !isAlaCarteService($service) && !isGroupClassService($service) && !isPackageService($service);
        });
        return view('holidays.update', compact('holiday', 'services'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'holiday_name' => 'required|string',
            'holiday_date' => 'required|string',
            'fixed_price' => 'required|numeric|min:0',
            'application_type' => 'required|in:one_day,period_days',
            'end_date' => 'nullable|date|after:holiday_date',
            'restrict_bookings' => 'nullable|in:yes,no',
        ]);

        // Validate that end_date is provided when application_type is period_days
        if ($request->application_type === 'period_days' && !$request->end_date) {
            return back()->withErrors(['end_date' => 'End date is required when using Period Days']);
        }

        $holiday = new Holiday;
        $holiday->name = $request->holiday_name;
        $holiday->date = Carbon::parse($request->holiday_date);
        $holiday->fixed_price = $request->fixed_price;
        $holiday->application_type = $request->application_type;
        $holiday->end_date = $request->application_type === 'period_days' ? Carbon::parse($request->end_date) : null;
        $holiday->restrict_bookings = $request->restrict_bookings ?? 'no';
        $holiday->save();

        return redirect()->route('holidays')->with([
            'message' => 'Holiday created successfully!',
            'status' => 'success'
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'holiday_id' => 'required|integer|exists:holidays,id',
            'holiday_name' => 'required|string',
            'holiday_date' => 'required|string',
            'fixed_price' => 'required|numeric|min:0',
            'application_type' => 'required|in:one_day,period_days',
            'end_date' => 'nullable|date|after:holiday_date',
            'restrict_bookings' => 'nullable|in:yes,no',
        ]);

        // Validate that end_date is provided when application_type is period_days
        if ($request->application_type === 'period_days' && !$request->end_date) {
            return back()->withErrors(['end_date' => 'End date is required when using Period Days']);
        }

        $holiday = Holiday::find($request->holiday_id);
        $holiday->name = $request->holiday_name;
        $holiday->date = Carbon::parse($request->holiday_date);
        $holiday->fixed_price = $request->fixed_price;
        $holiday->application_type = $request->application_type;
        $holiday->end_date = $request->application_type === 'period_days' ? Carbon::parse($request->end_date) : null;
        $holiday->restrict_bookings = $request->restrict_bookings ?? 'no';
        $holiday->save();

        return redirect()->route('holidays')->with([
            'message' => 'Holiday updated successfully!',
            'status' => 'success'
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:holidays,id'
        ]);

        $holiday = Holiday::find($request->id);
        $holiday->delete();

        return redirect()->route('holidays')->with([
            'message' => 'Holiday deleted successfully!',
            'status' => 'success'
        ]);
    }
}
