<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Models\PetProfile;
use App\Models\TimeSlot;
use App\Models\Appointment;
use App\Models\GroupClass;
use App\Models\Package;
use App\Models\FacilityAddress;
use App\Models\User;
use Carbon\Carbon;

class ServiceController extends Controller
{
    public function detail($id)
    {
        $service = Service::with(['category'])->find($id);
        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found',
                'result' => NULL
            ], 200);
        }

        $service->avatar_img_url = empty($service->avatar_img) ? '' : asset('storage/services/' . $service->avatar_img);
        $service->icon_url = empty($service->icon) ? '' : asset('storage/services/' . $service->icon);

        // additional services
        $service->additional_services = Service::whereNot('id', $id)->get();

        $service->additional_services_grooming = $service->additional_services->filter(function ($item) {
            return str_contains(strtolower($item->category->name), 'groom') && $item->level === 'secondary';
        })->values();
        $service->additional_services_training = $service->additional_services->filter(function ($item) {
            return str_contains(strtolower($item->category->name), 'training');
        })->values();

        $pets = PetProfile::with('coatType')->where('user_id', Auth::id())->get();
        $service->pets = $pets;

        // Get all time slots for the service for today
        $today = \Carbon\Carbon::today()->toDateString();
        $timeSlots = TimeSlot::where('service_id', $service->id)
            ->where('date', $today)
            ->get();
        // update the status of time slots with capacity and booked_count
        foreach ($timeSlots as $timeSlot) {
            if ($timeSlot->capacity <= $timeSlot->booked_count) {
                $timeSlot->status = 'full';
                $timeSlot->save();
            }
        }
        $service->time_slots = $timeSlots;

        // Get all appointments for the service for current user
        $appointments = Appointment::where('service_id', $service->id)
            ->where('customer_id', Auth::id())
            ->with('pet', 'staff')
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        if (isGroupClassService($service)) {
            foreach ($appointments as $appointment) {
                $appointment->class_name = optional(GroupClass::find($appointment->metadata['group_class_ids'] ?? null))->name ?? '';
            }
            $groupClasses = GroupClass::where('status', 'active')->orderBy('started_at', 'desc')->get();
            $service->group_classes = $groupClasses;
        }
        $service->appointments = $appointments;

        return response()->json([
            'status' => true,
            'message' => 'Service details retrieved successfully',
            'result' => $service
        ], 200);
    }

    public function getTimeSlots(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
            'pet_size' => 'nullable|in:small,medium,large,xlarge', // For grooming services
            'daycare_type' => 'nullable|in:half,full', // For daycare services
            'private_training_type' => 'nullable|in:half,one', // For private training services
            'pet_id' => 'nullable|exists:pet_profiles,id', // For ala carte service
            'secondary_service_ids' => 'nullable|array', // For ala carte service
            'secondary_service_ids.*' => 'exists:services,id'
        ]);

        $serviceId = $request->service_id;
        $date = $request->date;
        $petSize = $request->pet_size;
        $dayCareType = $request->daycare_type;
        $privateTrainingType = $request->private_training_type;
        $secondaryServiceIds = $request->secondary_service_ids ?? [];

        $service = Service::with('category')->find($serviceId);
        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found',
                'slots' => []
            ], 200);
        }

        if (isAlaCarteService($service) && !empty($secondaryServiceIds)) {
            $availableTimeSlots = $this->getAlaCarteTimeSlots($serviceId, $date, $petSize, $secondaryServiceIds);
        } else {
            $query = TimeSlot::where('service_id', $serviceId)->whereDate('date', $date);

            if (isGroomingService($service)) {
                if ($petSize) {
                    $query->where('pet_size', $petSize);
                }
            } else if (isDaycareService($service)) {
                $query->where('daycare_type', $dayCareType);
            } else if (isPrivateTrainingService($service)) {
                $query->where('private_training_type', $privateTrainingType);
            } else {
                $query->whereNull('pet_size');
            }

            $availableTimeSlots = $query->orderBy('start_time')->get();

            foreach ($availableTimeSlots as $timeSlot) {
                if ($timeSlot->booked_count >= $timeSlot->capacity) {
                    $timeSlot->update(['status' => 'full']);
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Time slots retrieved successfully',
            'result' => $availableTimeSlots->values(),
        ], 200);
    }

    private function getAlaCarteTimeSlots($serviceId, $date, $petSize, $secondaryServiceIds)
    {
        $services = Service::whereIn('id', $secondaryServiceIds)->get();
        $serviceDurations = [];

        foreach ($services as $service) {
            $duration = $this->getServiceDuration($service, $petSize);
            $serviceDurations[$service->id] = [
                'service' => $service,
                'duration_hours' => $duration,
                'duration_minutes' => (int) round($duration * 60),
            ];
        }

        if (empty($serviceDurations)) {
            return collect([]);
        }

        $serviceTimeslots = [];
        foreach ($serviceDurations as $serviceId => $serviceData) {
            $service = $serviceData['service'];

            $query = TimeSlot::where('service_id', $serviceId)
                ->whereDate('date', $date)
                ->where('status', 'available')
                ->whereRaw('booked_count < capacity');

            if (serviceCaresAboutPetSize($service)) {
                $query->where('pet_size', $petSize);
            }

            $serviceTimeslots[$serviceId] = $query->orderBy('start_time')->get();
        }

        $solutions = $this->findSchedulingSolutionsByNearestSlot($serviceTimeslots, $serviceDurations, $date);

        $solutions = $solutions->map(function($solution, $index) {
            $solution->id = $index + 1;
            return $solution;
        });

        return $solutions;
    }

    private function calculateTotalDurationForServices($serviceIds, $petSize)
    {
        $totalDuration = 0;

        $services = Service::whereIn('id', $serviceIds)->get();

        foreach ($services as $service) {
            $duration = $this->getServiceDuration($service, $petSize);
            $totalDuration += $duration;
        }

        return $totalDuration;
    }

    private function getServiceDuration($service, $petSize)
    {
        switch ($petSize) {
            case 'small':
                return $service->duration_small ?? $service->duration ?? 0;
            case 'medium':
                return $service->duration_medium ?? $service->duration ?? 0;
            case 'large':
                return $service->duration_large ?? $service->duration ?? 0;
            case 'xlarge':
                return $service->duration_xlarge ?? $service->duration ?? 0;
            default:
                return $service->duration ?? 0;
        }
    }

    private function findSchedulingSolutionsByNearestSlot($serviceTimeslots, $serviceDurations, $date)
    {
        $solutions = collect([]);

        if (empty($serviceTimeslots)) {
            return $solutions;
        }

        $serviceIds = array_keys($serviceTimeslots);
        $firstServiceId = $serviceIds[0];
        $remainingServiceIds = array_slice($serviceIds, 1);

        $firstServiceSlots = $serviceTimeslots[$firstServiceId];

        foreach ($firstServiceSlots as $firstSlot) {
            if ($firstSlot->booked_count >= $firstSlot->capacity) {
                continue;
            }

            $solution = $this->tryScheduleFromFirstSlot(
                $firstSlot,
                $remainingServiceIds,
                $serviceTimeslots,
                $serviceDurations,
                $date
            );

            if ($solution) {
                $solutions->push($solution);
            }
        }

        return $solutions->sortBy('start_time')->values();
    }

    private function tryScheduleFromFirstSlot($firstSlot, $remainingServiceIds, $serviceTimeslots, $serviceDurations, $date)
    {
        $scheduledServices = [];
        $allUsedSlots = [];
        $slotUsageCount = [];

        $allSlotIds = [];
        foreach ($serviceTimeslots as $slots) {
            foreach ($slots as $slot) {
                $allSlotIds[] = $slot->id;
            }
        }
        $allSlotIds = array_unique($allSlotIds);

        $slotCapacities = [];
        if (!empty($allSlotIds)) {
            $slots = TimeSlot::whereIn('id', $allSlotIds)->get(['id', 'capacity', 'booked_count']);
            foreach ($slots as $slot) {
                $slotCapacities[$slot->id] = [
                    'capacity' => $slot->capacity,
                    'booked_count' => $slot->booked_count,
                ];
            }
        }

        $firstServiceId = $firstSlot->service_id;
        $firstServiceDuration = $serviceDurations[$firstServiceId]['duration_minutes'];
        $firstServiceSlots = $serviceTimeslots[$firstServiceId];

        $firstServiceSlotResult = $this->findSlotsForService(
            $firstSlot,
            $firstServiceSlots,
            $firstServiceDuration,
            $date,
            $slotCapacities,
            $slotUsageCount
        );

        if (!$firstServiceSlotResult) {
            return null;
        }

        $firstStartTime = $firstServiceSlotResult['start_time'];
        $firstEndTime = $firstServiceSlotResult['end_time'];
        $firstUsedSlotIds = $firstServiceSlotResult['used_slot_ids'];

        foreach ($firstUsedSlotIds as $slotId) {
            $slotUsageCount[$slotId] = ($slotUsageCount[$slotId] ?? 0) + 1;
            $allUsedSlots[] = $slotId;
        }

        $scheduledServices[] = [
            'service_id' => $firstServiceId,
            'service_name' => $serviceDurations[$firstServiceId]['service']->name,
            'start_time' => $firstStartTime->toTimeString(),
            'end_time' => $firstEndTime->toTimeString(),
            'duration_minutes' => $firstServiceDuration,
            'used_slot_ids' => $firstUsedSlotIds,
        ];

        $earliestStart = $firstStartTime;
        $latestEnd = $firstEndTime;
        $lastServiceEndTime = $firstEndTime;

        foreach ($remainingServiceIds as $serviceId) {
            if (!isset($serviceTimeslots[$serviceId]) || $serviceTimeslots[$serviceId]->isEmpty()) {
                return null;
            }

            $requiredMinutes = $serviceDurations[$serviceId]['duration_minutes'];
            $serviceSlots = $serviceTimeslots[$serviceId];

            $nearestSlot = $this->findNearestSlot($serviceSlots, $lastServiceEndTime, $date, $slotCapacities, $slotUsageCount);

            if (!$nearestSlot) {
                return null;
            }

            $serviceSlotResult = $this->findSlotsForService(
                $nearestSlot,
                $serviceSlots,
                $requiredMinutes,
                $date,
                $slotCapacities,
                $slotUsageCount,
                $lastServiceEndTime
            );

            if (!$serviceSlotResult) {
                return null;
            }

            $actualStartTime = $serviceSlotResult['start_time'];
            $actualEndTime = $serviceSlotResult['end_time'];
            $usedSlotIds = $serviceSlotResult['used_slot_ids'];

            foreach ($usedSlotIds as $slotId) {
                $slotUsageCount[$slotId] = ($slotUsageCount[$slotId] ?? 0) + 1;
                $allUsedSlots[] = $slotId;
            }

            $scheduledServices[] = [
                'service_id' => $serviceId,
                'service_name' => $serviceDurations[$serviceId]['service']->name,
                'start_time' => $actualStartTime->toTimeString(),
                'end_time' => $actualEndTime->toTimeString(),
                'duration_minutes' => $requiredMinutes,
                'used_slot_ids' => $usedSlotIds,
            ];

            if ($actualStartTime->lt($earliestStart)) {
                $earliestStart = $actualStartTime;
            }
            if ($actualEndTime->gt($latestEnd)) {
                $latestEnd = $actualEndTime;
            }

            $lastServiceEndTime = $actualEndTime;
        }

        return (object)[
            'id' => null,
            'service_id' => null,
            'start_time' => $earliestStart->toTimeString(),
            'end_time' => $latestEnd->toTimeString(),
            'date' => $date,
            'capacity' => 1,
            'booked_count' => 0,
            'status' => 'available',
            'is_virtual' => true,
            'optimized_service_order' => $scheduledServices,
            'used_slot_ids' => array_unique($allUsedSlots),
        ];
    }

    private function findNearestSlot($serviceSlots, $afterTime, $date, $slotCapacities, $slotUsageCount)
    {
        foreach ($serviceSlots as $slot) {
            if ($slot->booked_count >= $slot->capacity) {
                continue;
            }

            $slotStartTime = Carbon::parse($date . ' ' . $slot->start_time);

            if ($slotStartTime->gte($afterTime)) {
                $currentUsage = $slotUsageCount[$slot->id] ?? 0;
                $availableCapacity = $slotCapacities[$slot->id]['capacity'] - $slotCapacities[$slot->id]['booked_count'] - $currentUsage;

                if ($availableCapacity > 0) {
                    return $slot;
                }
            }
        }

        return null;
    }

    private function findSlotsForService($startSlot, $serviceSlots, $requiredMinutes, $date, $slotCapacities, $slotUsageCount, $minStartTime = null)
    {
        $startTime = Carbon::parse($date . ' ' . $startSlot->start_time);
        $endTime = Carbon::parse($date . ' ' . $startSlot->end_time);

        if ($minStartTime && $minStartTime->gt($startTime)) {
            $startTime = $minStartTime;
        }

        $slotDuration = $startTime->diffInMinutes($endTime);
        $usedSlots = collect([$startSlot]);

        if ($slotDuration >= $requiredMinutes) {
            $canUse = true;
            foreach ($usedSlots as $slot) {
                $currentUsage = $slotUsageCount[$slot->id] ?? 0;
                $availableCapacity = $slotCapacities[$slot->id]['capacity'] - $slotCapacities[$slot->id]['booked_count'] - $currentUsage;
                if ($availableCapacity <= 0) {
                    $canUse = false;
                    break;
                }
            }

            if ($canUse) {
                $actualEndTime = $startTime->copy()->addMinutes($requiredMinutes);
                return [
                    'start_time' => $startTime,
                    'end_time' => $actualEndTime,
                    'used_slot_ids' => $usedSlots->pluck('id')->toArray(),
                ];
            }
        }

        $currentEndTime = $endTime;
        $slotIndex = $serviceSlots->search(function($slot) use ($startSlot) {
            return $slot->id === $startSlot->id;
        });

        for ($i = $slotIndex + 1; $i < $serviceSlots->count(); $i++) {
            $nextSlot = $serviceSlots[$i];

            if ($nextSlot->booked_count >= $nextSlot->capacity) {
                continue;
            }

            $nextStart = Carbon::parse($date . ' ' . $nextSlot->start_time);
            $nextEnd = Carbon::parse($date . ' ' . $nextSlot->end_time);

            $gap = $nextStart->diffInMinutes($currentEndTime);

            if ($gap == 0) {
                $currentUsage = $slotUsageCount[$nextSlot->id] ?? 0;
                $availableCapacity = $slotCapacities[$nextSlot->id]['capacity'] - $slotCapacities[$nextSlot->id]['booked_count'] - $currentUsage;

                if ($availableCapacity > 0) {
                    $usedSlots->push($nextSlot);
                    if ($nextEnd->gt($currentEndTime)) {
                        $currentEndTime = $nextEnd;
                    }

                    $totalDuration = $startTime->diffInMinutes($currentEndTime);

                    if ($totalDuration >= $requiredMinutes) {
                        $actualEndTime = $startTime->copy()->addMinutes($requiredMinutes);
                        return [
                            'start_time' => $startTime,
                            'end_time' => $actualEndTime,
                            'used_slot_ids' => $usedSlots->pluck('id')->toArray(),
                        ];
                    }
                }
            } else {
                break;
            }
        }

        return null;
    }

    private function optimizeServiceOrder($serviceDurations, $slot, $date)
    {
        $slotStart = Carbon::parse($date . ' ' . $slot->start_time);
        $slotEnd = Carbon::parse($date . ' ' . $slot->end_time);
        $slotDuration = $slotStart->diffInMinutes($slotEnd);

        $sortedServices = collect($serviceDurations)->sortByDesc('duration_minutes')->values();

        $optimizedOrder = [];
        $currentTime = $slotStart->copy();
        $remainingServices = $sortedServices->toArray();

        while (!empty($remainingServices) && $currentTime->lt($slotEnd)) {
            $fitted = false;

            foreach ($remainingServices as $key => $serviceData) {
                $serviceDuration = $serviceData['duration_minutes'];
                $serviceEnd = $currentTime->copy()->addMinutes($serviceDuration);

                if ($serviceEnd->lte($slotEnd)) {
                    $optimizedOrder[] = [
                        'service_id' => $serviceData['service']->id,
                        'service_name' => $serviceData['service']->name,
                        'start_time' => $currentTime->toTimeString(),
                        'end_time' => $serviceEnd->toTimeString(),
                        'duration_minutes' => $serviceDuration,
                    ];
                    $currentTime = $serviceEnd;
                    unset($remainingServices[$key]);
                    $remainingServices = array_values($remainingServices);
                    $fitted = true;
                    break;
                }
            }

            if (!$fitted) {
                $sortedByShortest = collect($remainingServices)->sortBy('duration_minutes')->values();
                foreach ($sortedByShortest as $key => $serviceData) {
                    $serviceDuration = $serviceData['duration_minutes'];
                    $serviceEnd = $currentTime->copy()->addMinutes($serviceDuration);

                    if ($serviceEnd->lte($slotEnd)) {
                        $optimizedOrder[] = [
                            'service_id' => $serviceData['service']->id,
                            'service_name' => $serviceData['service']->name,
                            'start_time' => $currentTime->toTimeString(),
                            'end_time' => $serviceEnd->toTimeString(),
                            'duration_minutes' => $serviceDuration,
                        ];
                        $currentTime = $serviceEnd;
                        foreach ($remainingServices as $origKey => $origService) {
                            if ($origService['service']->id === $serviceData['service']->id) {
                                unset($remainingServices[$origKey]);
                                break;
                            }
                        }
                        $remainingServices = array_values($remainingServices);
                        $fitted = true;
                        break;
                    }
                }
            }

            if (!$fitted) {
                break;
            }
        }

        return $optimizedOrder;
    }

    private function findConsecutiveTimeWindows($timeSlots, $serviceDurations, $requiredDurationMinutes, $date)
    {
        $availableWindows = collect([]);

        $sortedSlots = $timeSlots->filter(function($slot) {
            return $slot->booked_count < $slot->capacity;
        })->sortBy('start_time')->values();

        if ($sortedSlots->isEmpty()) {
            return $availableWindows;
        }

        for ($i = 0; $i < $sortedSlots->count(); $i++) {
            $startSlot = $sortedSlots[$i];
            $startTime = Carbon::parse($date . ' ' . $startSlot->start_time);
            $endTime = Carbon::parse($date . ' ' . $startSlot->end_time);
            $accumulatedDuration = $startTime->diffInMinutes($endTime);

            $usedSlots = collect([$startSlot]);

            for ($j = $i + 1; $j < $sortedSlots->count(); $j++) {
                $nextSlot = $sortedSlots[$j];

                if ($usedSlots->contains('id', $nextSlot->id)) {
                    continue;
                }

                if ($nextSlot->booked_count >= $nextSlot->capacity) {
                    continue;
                }

                $nextStart = Carbon::parse($date . ' ' . $nextSlot->start_time);
                $nextEnd = Carbon::parse($date . ' ' . $nextSlot->end_time);

                $gap = $nextStart->diffInMinutes($endTime);

                if ($gap <= 5 || $nextStart->lte($endTime)) {
                    if ($nextEnd->gt($endTime)) {
                        $endTime = $nextEnd;
                    }
                    $accumulatedDuration = $startTime->diffInMinutes($endTime);
                    $usedSlots->push($nextSlot);

                    if ($accumulatedDuration >= $requiredDurationMinutes) {
                        $window = (object)[
                            'id' => null,
                            'service_id' => null,
                            'start_time' => $startTime->toTimeString(),
                            'end_time' => $endTime->toTimeString(),
                            'date' => $date,
                            'capacity' => 1,
                            'booked_count' => 0,
                            'status' => 'available',
                            'pet_size' => $startSlot->pet_size,
                            'is_virtual' => true,
                            'used_slot_ids' => $usedSlots->pluck('id')->toArray(),
                        ];

                        $optimizedOrder = $this->optimizeServiceOrderForConsecutiveSlots(
                            $serviceDurations,
                            $usedSlots,
                            $startTime,
                            $endTime,
                            $date
                        );
                        $window->optimized_service_order = $optimizedOrder;

                        $availableWindows->push($window);
                        break;
                    }
                } elseif ($nextStart->gt($endTime)) {
                    break;
                }
            }
        }

        return $availableWindows->unique(function($window) {
            return $window->start_time . '-' . $window->end_time;
        })->values();
    }

    private function optimizeServiceOrderForConsecutiveSlots($serviceDurations, $usedSlots, $windowStart, $windowEnd, $date)
    {
        $optimizedOrder = [];
        $currentTime = $windowStart->copy();
        $remainingServices = collect($serviceDurations)->toArray();

        $sortedUsedSlots = $usedSlots->sortBy('start_time')->values();

        while (!empty($remainingServices) && $currentTime->lt($windowEnd)) {
            $fitted = false;

            $nextBoundary = $windowEnd->copy();
            foreach ($sortedUsedSlots as $slot) {
                $slotStart = Carbon::parse($date . ' ' . $slot->start_time);
                $slotEnd = Carbon::parse($date . ' ' . $slot->end_time);

                if ($slotStart->gt($currentTime) && $slotStart->lt($nextBoundary)) {
                    $nextBoundary = $slotStart->copy();
                }
                if ($slotEnd->gt($currentTime) && $slotEnd->lt($nextBoundary)) {
                    $nextBoundary = $slotEnd->copy();
                }
            }

            $availableTime = $currentTime->diffInMinutes($nextBoundary);

            $sortedByLongest = collect($remainingServices)->sortByDesc('duration_minutes')->values();
            $sortedByShortest = collect($remainingServices)->sortBy('duration_minutes')->values();

            foreach ([$sortedByLongest, $sortedByShortest] as $sortedServices) {
                foreach ($sortedServices as $serviceData) {
                    $serviceDuration = $serviceData['duration_minutes'];

                    if ($serviceDuration <= $availableTime) {
                        $serviceEnd = $currentTime->copy()->addMinutes($serviceDuration);

                        if ($serviceEnd->lte($windowEnd)) {
                            $optimizedOrder[] = [
                                'service_id' => $serviceData['service']->id,
                                'service_name' => $serviceData['service']->name,
                                'start_time' => $currentTime->toTimeString(),
                                'end_time' => $serviceEnd->toTimeString(),
                                'duration_minutes' => $serviceDuration,
                            ];
                            $currentTime = $serviceEnd;

                            foreach ($remainingServices as $key => $origService) {
                                if ($origService['service']->id === $serviceData['service']->id) {
                                    unset($remainingServices[$key]);
                                    break;
                                }
                            }
                            $remainingServices = array_values($remainingServices);
                            $fitted = true;
                            break;
                        }
                    }
                }

                if ($fitted) break;
            }

            if (!$fitted) {
                $currentTime = $nextBoundary->copy();
            }
        }

        return $optimizedOrder;
    }

    public function package($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found',
                'result' => NULL
            ], 200);
        }

        $appointments = Appointment::where('service_id', $id)->where('customer_id', Auth::id())->with(['pet'])->get();
        foreach($appointments as $appointment) {
            // get package id from the metadata field
            $packageId = $appointment->metadata['package_id'];
            $package = Package::find($packageId);
            if ($package) {
                $package->image_url = empty($package->image) ? '' : asset('storage/services/' . $package->image);
                $appointment->package = $package;
            } else {
                $appointment->package = null;
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Appointments retrieved successfully',
            'result' => $appointments
        ], 200);
    }

    public function calculateDistance($serviceId, Request $request)
    {
        $service = Service::find($serviceId);
        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found',
                'result' => null,
            ], 200);
        }

        $pricePerMile = floatval($service->price_per_mile ?? 0);

        $userId = $request->query('user_id');
        $user = User::find($userId);
        $ownerAddress = buildOwnerAddressFromProfile($user?->profile);

        $facility = FacilityAddress::query()->first();
        $facilityAddress = buildFacilityAddressFromModel($facility);

        $errors = [];

        if (!$ownerAddress) {
            $errors[] = 'Sorry, your address is invalid. Pleae check the address';
        }

        if (!$facilityAddress) {
            $errors[] = 'Something went wrong. Please contact support.';
        }

        $ownerCoords = null;
        $facilityCoords = null;

        if ($ownerAddress) {
            $ownerCoords = geocodeChauffeurAddress($ownerAddress);
            if (!$ownerCoords) {
                $errors[] = 'Sorry, your address is invalid. Please check the address.';
            }
        }

        if ($facilityAddress) {
            $facilityCoords = geocodeChauffeurAddress($facilityAddress);
            if (!$facilityCoords) {
                $errors[] = 'Something went wrong. Please contact support.';
            }
        }

        $errors = array_values(array_unique($errors));

        $ownerLocationValid = !in_array('Sorry, your address is invalid. Please check the address.', $errors, true);
        $facilityLocationValid = !in_array('Something went wrong. Please contact support.', $errors, true);

        $price = 0;
        if ($ownerLocationValid && $facilityLocationValid) {
            $distanceMiles = getDrivingDistanceMiles($ownerCoords, $facilityCoords);
            if ($distanceMiles !== null) {
                $price = round($distanceMiles * $pricePerMile, 2);
            }
        }

        return response()->json([
            'status' => true,
            'message' => empty($errors) ? 'Distance calculated successfully' : 'Address validation failed',
            'result' => [
                'price_per_mile' => $pricePerMile,
                'price' => $price,
                'owner_location_valid' => $ownerLocationValid,
                'facility_location_valid' => $facilityLocationValid,
                'errors' => $errors,
            ],
        ], 200);
    }
}