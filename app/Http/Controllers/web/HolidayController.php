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
            'percent_increase' => 'integer|min:0|max:100',
            'restrict_bookings' => 'nullable|in:yes,no',
            'services' => 'nullable|array',
            'services.*.service_id' => 'required|integer|exists:services,id',
            'services.*.max_value' => 'nullable|integer|min:0',
        ]);

        $holiday = new Holiday;
        $holiday->name = $request->holiday_name;
        $holiday->date = Carbon::parse($request->holiday_date);
        $holiday->percent_increase = $request->percent_increase;
        $holiday->restrict_bookings = $request->restrict_bookings ?? 'no';
        $holiday->save();

        if ($request->has('services') && is_array($request->services)) {
            foreach ($request->services as $serviceData) {
                if (isset($serviceData['service_id'])) {
                    $service = Service::find($serviceData['service_id']);
                    if ($service && !isBoardingService($service) && !isAlaCarteService($service) && !isGroupClassService($service) && !isPackageService($service)) {
                        HolidayService::create([
                            'holiday_id' => $holiday->id,
                            'service_id' => $serviceData['service_id'],
                            'max_value' => isset($serviceData['max_value']) && $serviceData['max_value'] !== '' ? $serviceData['max_value'] : null,
                        ]);
                    }
                }
            }
        }

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
            'percent_increase' => 'integer|min:0|max:100',
            'restrict_bookings' => 'nullable|in:yes,no',
            'services' => 'nullable|array',
            'services.*.service_id' => 'required|integer|exists:services,id',
            'services.*.max_value' => 'nullable|integer|min:0',
        ]);

        $holiday = Holiday::find($request->holiday_id);
        $holiday->name = $request->holiday_name;
        $holiday->date = Carbon::parse($request->holiday_date);
        $holiday->percent_increase = $request->percent_increase;
        $holiday->restrict_bookings = $request->restrict_bookings ?? 'no';
        $holiday->save();

        HolidayService::where('holiday_id', $holiday->id)->delete();

        if ($request->has('services') && is_array($request->services)) {
            foreach ($request->services as $serviceData) {
                if (isset($serviceData['service_id'])) {
                    $service = Service::find($serviceData['service_id']);
                    if ($service && !isBoardingService($service) && !isAlaCarteService($service) && !isGroupClassService($service) && !isPackageService($service)) {
                        HolidayService::create([
                            'holiday_id' => $holiday->id,
                            'service_id' => $serviceData['service_id'],
                            'max_value' => isset($serviceData['max_value']) && $serviceData['max_value'] !== '' ? $serviceData['max_value'] : null,
                        ]);
                    }
                }
            }
        }

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
