<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\TimeSlot;
use App\Models\Checkin;
use App\Models\Process;
use App\Models\PetProfile;
use App\Models\Questionnaire;
use App\Models\ServiceCategory;
use App\Models\CustomerPackage;
use Carbon\Carbon;

class PackageController extends Controller
{
    public function list(Request $request)
    {
        $status = $request->get('status', 'active'); // Default to active packages

        $packages = Package::when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Add image URL and service details to each package
        $packages->transform(function ($package) {
            $package->image_url = empty($package->image) ? '' : asset('storage/services/' . $package->image);

            // Get services associated with this package
            $serviceIds = $package->service_ids ? explode(',', $package->service_ids) : [];
            if (!empty($serviceIds)) {
                $services = Service::whereIn('id', $serviceIds)
                    ->where('status', 'active')
                    ->with('category')
                    ->get();

                // Add image URLs to services
                $services->transform(function ($service) {
                    $service->avatar_img_url = empty($service->avatar_img) ? '' : asset('storage/services/' . $service->avatar_img);
                    $service->icon_url = empty($service->icon) ? '' : asset('storage/services/' . $service->icon);
                    return $service;
                });

                $package->services = $services;
            } else {
                $package->services = collect([]);
            }

            return $package;
        });

        return response()->json([
            'status' => true,
            'message' => 'Packages retrieved successfully',
            'result' => $packages
        ], 200);
    }

    public function detail($id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found',
                'result' => null
            ], 200);
        }

        // Add image URL
        $package->image_url = empty($package->image) ? '' : asset('storage/services/' . $package->image);

        // Get services associated with this package
        $serviceIds = $package->service_ids ? explode(',', $package->service_ids) : [];
        if (!empty($serviceIds)) {
            $services = Service::whereIn('id', $serviceIds)
                ->where('status', 'active')
                ->with('category')
                ->get();

            // Add image URLs to services
            $services->transform(function ($service) {
                $service->avatar_img_url = empty($service->avatar_img) ? '' : asset('storage/services/' . $service->avatar_img);
                $service->icon_url = empty($service->icon) ? '' : asset('storage/services/' . $service->icon);
                return $service;
            });

            $package->services = $services;
        } else {
            $package->services = collect([]);
        }

        $pets = PetProfile::where('user_id', Auth::id())->get();
        $package->pets = $pets;

        // get service as package
        $packageService = Service::whereHas('category', function ($query) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%package%']);
        })->with('category')->first();
        $package->service_id = $packageService->id ?? null;

        return response()->json([
            'status' => true,
            'message' => 'Package details retrieved successfully',
            'result' => $package
        ], 200);
    }

    public function listCustomerPackages(Request $request)
    {
        $customerPackages = CustomerPackage::with(['package'])
            ->where('customer_id', Auth::id())
            ->get();

        foreach ($customerPackages as $customerPackage) {
            $customerPackage->package->image_url = empty($customerPackage->package->image) ? '' : asset('storage/services/' . $customerPackage->package->image);
            $customerPackage->package->services = Service::whereIn('id', explode(',', $customerPackage->package->service_ids ?? ''))
                ->where('status', 'active')
                ->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'Customer packages retrieved successfully',
            'result' => $customerPackages
        ], 200);
    }
}

