<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Service;
use App\Models\Checkin;
use App\Models\Process;
use App\Models\Checkout;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Models\PetProfile;
use App\Models\PetInitialTemperament;
use App\Models\GroupClass;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\Discount;
use App\Models\PetBehavior;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $active = 'dashboard';
        
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        
        $todayRevenue = InvoiceItem::whereHas('invoice', function ($query) use ($today) {
                $query->where('status', 'paid')
                    ->whereDate('paid_at', $today);
            })
            ->sum('price');
        
        $yesterdayRevenue = InvoiceItem::whereHas('invoice', function ($query) use ($yesterday) {
                $query->where('status', 'paid')
                    ->whereDate('paid_at', $yesterday);
            })
            ->sum('price');
        
        $percentageChange = 0;
        if ($yesterdayRevenue > 0) {
            $percentageChange = (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100;
        } elseif ($todayRevenue > 0) {
            $percentageChange = 100;
        }
        
        $percentageChange = round($percentageChange, 1);
        
        $totalCustomers = User::whereHas('roles', function ($query) {
            $query->where('title', 'customer');
        })->count();
        
        $yesterdayCustomers = User::whereHas('roles', function ($query) {
            $query->where('title', 'customer');
        })->whereDate('created_at', $yesterday)->count();
        
        $todayNewCustomers = User::whereHas('roles', function ($query) {
            $query->where('title', 'customer');
        })->whereDate('created_at', $today)->count();
        
        $customerPercentageChange = 0;
        if ($yesterdayCustomers > 0) {
            $customerPercentageChange = (($todayNewCustomers - $yesterdayCustomers) / $yesterdayCustomers) * 100;
        } elseif ($todayNewCustomers > 0) {
            $customerPercentageChange = 100;
        }
        
        $customerPercentageChange = round($customerPercentageChange, 1);
        
        $totalPets = PetProfile::count();
        
        $todayNewPets = PetProfile::whereDate('created_at', $today)->count();
        $yesterdayNewPets = PetProfile::whereDate('created_at', $yesterday)->count();
        
        $petPercentageChange = 0;
        if ($yesterdayNewPets > 0) {
            $petPercentageChange = (($todayNewPets - $yesterdayNewPets) / $yesterdayNewPets) * 100;
        } elseif ($todayNewPets > 0) {
            $petPercentageChange = 100;
        }
        
        $petPercentageChange = round($petPercentageChange, 1);
        
        $todayAppointments = Appointment::whereDate('date', $today)->count();
        
        $yesterdayAppointments = Appointment::whereDate('date', $yesterday)->count();
        
        $appointmentPercentageChange = 0;
        if ($yesterdayAppointments > 0) {
            $appointmentPercentageChange = (($todayAppointments - $yesterdayAppointments) / $yesterdayAppointments) * 100;
        } elseif ($todayAppointments > 0) {
            $appointmentPercentageChange = 100;
        }
        
        $appointmentPercentageChange = round($appointmentPercentageChange, 1);
        
        $recentAppointments = Appointment::with(['pet', 'customer.profile', 'service', 'invoice.items'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($recentAppointments as $appointment) {
            if ($appointment->invoice && $appointment->invoice->items && $appointment->invoice->items->count() > 0) {
                $appointment->total_price = $appointment->invoice->items->sum('price');
            } else {
                $appointment->total_price = $appointment->estimated_price ?? 0;
            }
        }
        
        $period = $request->get('period', 'month');
        
        $revenueData = $this->getRevenueStatistics($period);
        
        return view('dashboard.index', compact('active', 'todayRevenue', 'yesterdayRevenue', 'percentageChange', 'totalCustomers', 'customerPercentageChange', 'todayNewCustomers', 'yesterdayCustomers', 'totalPets', 'petPercentageChange', 'todayNewPets', 'yesterdayNewPets', 'todayAppointments', 'yesterdayAppointments', 'appointmentPercentageChange', 'recentAppointments', 'revenueData', 'period'));
    }

    public function serviceDashboard(Request $request, $id)
    {
        $customerPet = $request->get('customer');
        $staffId = $request->get('staff');
        $service = Service::find($id);

        if ($customerPet) {
            $appointments = Appointment::where('service_id', $id)->where(function ($query) use ($customerPet) {
                $query->whereHas('customer', function ($q) use ($customerPet) {
                    $q->where('email', 'like', "%{$customerPet}%")
                        ->orWhereHas('profile', function ($q2) use ($customerPet) {
                            $q2->where('first_name', 'like', "%{$customerPet}%")
                                ->orWhere('last_name', 'like', "%{$customerPet}%");
                        });
                })->orWhereHas('pet', function ($q) use ($customerPet) {
                    $q->where('name', 'like', "%{$customerPet}%");
                });
            });
        } else {
            $appointments = Appointment::where('service_id', $id);
        }

        if ($staffId)
            $appointments = $appointments->where('staff_id', $staffId);

        $infoMessage = null;
        if (isGroupClassService($service)) {
            // Get only the first upcoming appointment per pet and all non-checked_in appointments for group classes
            $now = Carbon::now();

            // Get appointments with status != 'checked_in' (all statuses)
            $tempAppointments = clone $appointments;
            $nonCheckedInAppointments = $tempAppointments
                ->where('status', '!=', 'checked_in')
                ->orderBy('date')
                ->orderBy('start_time')
                ->get();

            // Get future checked_in appointments
            $checkedInAppointments = $appointments
                ->where('status', 'checked_in')
                ->where('date', '>=', $now->toDateString())
                ->orderBy('date')
                ->orderBy('start_time')
                ->get();

            // Group checked_in appointments by pet_id and take only the first (upcoming) per pet
            $firstCheckedInPerPet = $checkedInAppointments->groupBy('pet_id')->map(function ($petAppointments) {
                return $petAppointments->first();
            })->values();

            // Merge: all non-checked_in + first checked_in per pet
            $appointments = $nonCheckedInAppointments->merge($firstCheckedInPerPet)
                ->sortBy([
                    ['date', 'asc'],
                    ['start_time', 'asc']
                ])
                ->values();
            $infoMessage = 'Showing only the next upcoming appointment per pet in Scheduled. Additional future sessions exist and will appear at the completion of the current session.';
        } else {
            $appointments = $appointments->orderBy('date', 'desc')->orderBy('start_time', 'desc')->get();
        }

        $staffs = User::whereHas('roles', function ($query) {
            $query->whereNot('title', 'customer');
        })->get();

        return view('dashboard.kanban-service', compact('appointments', 'customerPet', 'staffId', 'staffs', 'id', 'service', 'infoMessage'));
    }

    public function appointmentDetail($id)
    {
        $appointment = Appointment::with(['customer.profile', 'customer.appointmentCancellations.service', 'customer.appointmentCancellations.cancelledBy', 'pet.breed', 'pet.color', 'pet.coatType', 'pet.vaccinations', 'pet.certificates', 'service.category', 'staff.profile'])
            ->find($id);
        $dbEstimatedPrice = $appointment->estimated_price ?? 0;

        // Check if this is a package appointment - service_id refers to a service with package category
        $isPackageAppointment = $appointment->service && isPackageService($appointment->service);
        $packageServiceIds = [];
        
        // For package appointments, load services from additional_service_ids
        if ($isPackageAppointment && $appointment->additional_service_ids) {
            $packageServiceIds = explode(',', $appointment->additional_service_ids);
            $packageServices = Service::whereIn('id', $packageServiceIds)->get();
            $appointment->package_services = $packageServices;
            if ($appointment->metadata && isset($appointment->metadata['package_name'])) {
                $appointment->package_name = $appointment->metadata['package_name'];
            } else {
                if ($appointment->metadata && isset($appointment->metadata['package_id'])) {
                    $package = Package::find($appointment->metadata['package_id']);
                    if ($package) {
                        $appointment->package_name = $package->name;
                    } else {
                        $appointment->package_name = $appointment->service ? $appointment->service->name : 'Package';
                    }
                } else {
                    $appointment->package_name = $appointment->service ? $appointment->service->name : 'Package';
                }
            }
        }

        // If group class appointment, then add the class name to the appointment object
        if (isGroupClassService($appointment->service)) {
            $appointment->class_name = optional(GroupClass::find($appointment->metadata['group_class_ids'] ?? null))->name ?? '';
        }
        // If ala carte appointment, then add the secondary service names to the appointment object
        if (isAlaCarteService($appointment->service)) {
            $secondaryServiceNames = [];
            if ($appointment->metadata && isset($appointment->metadata['secondary_service_ids'])) {
                $secondaryServiceIds = explode(',', $appointment->metadata['secondary_service_ids']);
                foreach ($secondaryServiceIds as $serviceId) {
                    $service = Service::find($serviceId);
                    if ($service) {
                        $secondaryServiceNames[] = $service->name;
                    }
                }
            }
            $appointment->secondary_service_names = implode(', ', $secondaryServiceNames);
        }

        $chauffeurPricingData = buildChauffeurPricingData($appointment);
        $chauffeurServicePrices = $chauffeurPricingData['service_prices'] ?? [];

        $resolveAppointmentServicePrice = function ($service, $petSize, $metadata = null) use ($chauffeurServicePrices) {
            if (!$service) {
                return 0;
            }

            if (array_key_exists($service->id, $chauffeurServicePrices)) {
                return floatval($chauffeurServicePrices[$service->id]);
            }

            return getServicePrice($service, $petSize, $metadata);
        };

        $isPetDoubleCoated = (bool) (optional(optional($appointment->pet)->coatType)->is_double_coated ?? false);

        $calculateCoatTypeExtraFee = function (array $serviceIds) use ($isPetDoubleCoated) {
            if (!$isPetDoubleCoated) {
                return 0;
            }

            $normalizedServiceIds = collect($serviceIds)
                ->filter(function ($serviceId) {
                    return !is_null($serviceId) && $serviceId !== '';
                })
                ->map(function ($serviceId) {
                    return (int) $serviceId;
                })
                ->filter(function ($serviceId) {
                    return $serviceId > 0;
                })
                ->unique()
                ->values();

            if ($normalizedServiceIds->isEmpty()) {
                return 0;
            }

            $services = Service::whereIn('id', $normalizedServiceIds)->get()->keyBy('id');
            $extraFee = 0;

            foreach ($normalizedServiceIds as $serviceId) {
                $service = $services->get($serviceId);
                if (!$service) {
                    continue;
                }

                if ((bool) $service->is_double_coated) {
                    $extraFee += floatval($service->coat_type_price ?? 0);
                }
            }

            return $extraFee;
        };

        // Set up the estimated price of the appointment
        $computedEstimatedPrice = 0;
        if ($isPackageAppointment && $appointment->pet && !empty($packageServiceIds)) {
            // For package appointments, sum up the prices of all services in the package
            $petSize = $appointment->pet->size ?? 'medium';
            foreach ($packageServiceIds as $serviceId) {
                if (!empty($serviceId)) {
                    $service = Service::find($serviceId);
                    if ($service) {
                        $computedEstimatedPrice += $resolveAppointmentServicePrice($service, $petSize);
                    }
                }
            }
        } else {
            // Check if this is a group classes appointment
            $isGroupClasses = isGroupClassService($appointment->service);
            $groupClassIds = [];
            if ($isGroupClasses && $appointment->metadata && isset($appointment->metadata['group_class_ids'])) {
                $groupClassIds = explode(',', $appointment->metadata['group_class_ids']);
            }
            // Check if this is an ala carte appointment
            $isAlaCarte = isAlaCarteService($appointment->service);
            $secondaryServiceIds = [];
            if ($isAlaCarte && $appointment->metadata && isset($appointment->metadata['secondary_service_ids'])) {
                $secondaryServiceIds = explode(',', $appointment->metadata['secondary_service_ids']);
            }

            if ($isGroupClasses && !empty($groupClassIds)) {
                // For group classes, sum up the prices of selected group classes
                foreach ($groupClassIds as $classId) {
                    $groupClass = GroupClass::find($classId);
                    if ($groupClass) {
                        $computedEstimatedPrice += $groupClass->price;
                    }
                }
            } elseif ($isAlaCarte && !empty($secondaryServiceIds) && $appointment->pet) {
                // For ala carte services, sum up the prices of selected secondary services
                $petSize = $appointment->pet->size ?? 'medium';
                foreach ($secondaryServiceIds as $serviceId) {
                    if (!empty($serviceId)) {
                        $service = Service::find($serviceId);
                        if ($service) {
                            $computedEstimatedPrice += $resolveAppointmentServicePrice($service, $petSize);
                        }
                    }
                }

                $secondaryServiceIdSet = collect($secondaryServiceIds)
                    ->map(fn ($serviceId) => (string) trim($serviceId))
                    ->filter()
                    ->values();

                $additionalServiceIds = collect(explode(',', $appointment->additional_service_ids ?? ''))
                    ->map(fn ($serviceId) => (string) trim($serviceId))
                    ->filter()
                    ->reject(fn ($serviceId) => $secondaryServiceIdSet->contains($serviceId))
                    ->values();

                foreach ($additionalServiceIds as $serviceId) {
                    $service = Service::find($serviceId);
                    if ($service) {
                        $computedEstimatedPrice += $resolveAppointmentServicePrice($service, $petSize);
                    }
                }
            } else {
                // For regular appointments, use service price (boarding uses appointment-based pricing)
                if (isBoardingService($appointment->service)) {
                    $boardingPrice = getBoardingServicePrice($appointment->service, $appointment);
                    $computedEstimatedPrice = $boardingPrice !== null
                        ? $boardingPrice
                        : $resolveAppointmentServicePrice($appointment->service, $appointment->pet->size, $appointment->metadata);
                } else {
                    $computedEstimatedPrice = $resolveAppointmentServicePrice($appointment->service, $appointment->pet->size, $appointment->metadata);
                }

                $additionalServiceIds = explode(',', $appointment->additional_service_ids ?? '');
                foreach ($additionalServiceIds as $serviceId) {
                    if (!empty($serviceId)) {
                        $service = Service::find($serviceId);
                        if ($service) {
                            $computedEstimatedPrice += $resolveAppointmentServicePrice($service, $appointment->pet->size);
                        }
                    }
                }
            }

            $coatPricingServiceIds = [];
            $coatPricingServiceIds[] = $appointment->service_id;

            if (!empty($appointment->second_service_id ?? null)) {
                $coatPricingServiceIds[] = $appointment->second_service_id;
            }

            if (!empty($appointment->additional_service_ids)) {
                $coatPricingServiceIds = array_merge($coatPricingServiceIds, explode(',', $appointment->additional_service_ids));
            }

            if (isAlaCarteService($appointment->service) && $appointment->metadata && isset($appointment->metadata['secondary_service_ids'])) {
                $coatPricingServiceIds = array_merge($coatPricingServiceIds, explode(',', $appointment->metadata['secondary_service_ids']));
            }

            $computedEstimatedPrice += $calculateCoatTypeExtraFee($coatPricingServiceIds);
        }

        $storedEstimatedPrice = floatval($appointment->estimated_price ?? 0);
        $appointment->estimated_price = $storedEstimatedPrice > 0
            ? $storedEstimatedPrice
            : $computedEstimatedPrice;
        $dbEstimatedPrice = $appointment->estimated_price;

        // Always compute coat extra fee for the line-item display row
        $coatFeeServiceIds = [$appointment->service_id];
        if (!empty($appointment->second_service_id ?? null)) {
            $coatFeeServiceIds[] = $appointment->second_service_id;
        }
        if (!empty($appointment->additional_service_ids)) {
            $coatFeeServiceIds = array_merge($coatFeeServiceIds, explode(',', $appointment->additional_service_ids));
        }
        if (isAlaCarteService($appointment->service) && $appointment->metadata && isset($appointment->metadata['secondary_service_ids'])) {
            $coatFeeServiceIds = array_merge($coatFeeServiceIds, explode(',', $appointment->metadata['secondary_service_ids']));
        }
        $coatExtraFee = $calculateCoatTypeExtraFee($coatFeeServiceIds);
        $appointment->coat_extra_fee = $coatExtraFee > 0 ? $coatExtraFee : null;

        $staffs = \App\Models\User::whereHas('roles', function ($query) {
            $query->whereNot('title', 'customer');
        })->with('profile')->get();

        $checkedIn = Checkin::where('appointment_id', $appointment->id)->first();

        if ($checkedIn && $checkedIn->flows) {
            $checkedIn->flows = json_decode($checkedIn->flows, true);
        }

        // For package appointments, we don't load a single process - each service has its own process
        // For boarding services, try to load process for the appointment date
        if ($isPackageAppointment) {
            $process = null; // Package appointments have multiple processes, one per service
        } elseif (isBoardingService($appointment->service)) {
            $process = Process::where('appointment_id', $appointment->id)
                ->where('date', $appointment->date)
                ->first();
        } else {
            $process = Process::where('appointment_id', $appointment->id)->first();
        }

        if ($process && $process->flows) {
            $process->flows = json_decode($process->flows, true);
        }

        $checkout = Checkout::where('appointment_id', $appointment->id)->first();

        if ($checkout && $checkout->flows) {
            $checkout->flows = json_decode($checkout->flows, true);
        }

        $invoice = Invoice::where('appointment_id', $appointment->id)->first();

        $initialTemperament = null;

        if ((isGroomingService($appointment->service) || isAlaCarteService($appointment->service)) && $appointment->pet) {
            $initialTemperament = PetInitialTemperament::where('pet_id', $appointment->pet->id)->first();
        }

        $additionalServices = Service::where('id', '!=', $appointment->service_id)->where('status', 'active')->get();

        $lastAppointmentRatings = [];
        if (isPrivateTrainingService($appointment->service)) {
            $allCompletedAppointments = Appointment::where('appointments.pet_id', $appointment->pet_id)
                ->where('appointments.id', '!=', $appointment->id)
                ->join('checkouts', 'appointments.id', '=', 'checkouts.appointment_id')
                ->with('service.category')
                ->orderBy('checkouts.updated_at', 'desc')
                ->orderBy('checkouts.created_at', 'desc')
                ->select('appointments.*')
                ->get();

            $lastAppointment = null;
            foreach ($allCompletedAppointments as $apt) {
                if (isPrivateTrainingService($apt->service)) {
                    $lastAppointment = $apt;
                    break;
                }
            }

            if ($lastAppointment) {
                try {
                    $lastCheckout = Checkout::where('appointment_id', $lastAppointment->id)->first();
                    if ($lastCheckout && $lastCheckout->flows) {
                        if (is_string($lastCheckout->flows)) {
                            $lastCheckoutFlows = json_decode($lastCheckout->flows, true);
                        } else {
                            $lastCheckoutFlows = $lastCheckout->flows;
                        }

                        if (is_array($lastCheckoutFlows) && isset($lastCheckoutFlows['obedience_ratings'])) {
                            if (is_array($lastCheckoutFlows['obedience_ratings'])) {
                                $lastAppointmentRatings = $lastCheckoutFlows['obedience_ratings'];
                            } elseif (is_string($lastCheckoutFlows['obedience_ratings'])) {
                                $decoded = json_decode($lastCheckoutFlows['obedience_ratings'], true);
                                if (is_array($decoded)) {
                                    $lastAppointmentRatings = $decoded;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $lastAppointmentRatings = [];
                }
            }
        }

        $customerPackage = null;
        if ($isPackageAppointment && $appointment->metadata && isset($appointment->metadata['customer_package_id'])) {
            $customerPackage = CustomerPackage::find($appointment->metadata['customer_package_id']);
        }

        // Check if this is a package appointment - service_id refers to a service with package category
        if ($isPackageAppointment) {
            return view('dashboard.appointment-package', compact('appointment', 'staffs', 'checkedIn', 'process', 'checkout', 'invoice', 'additionalServices', 'lastAppointmentRatings', 'customerPackage'));
        }

        $invoiceDiscountRules = getScopedDiscountsForCustomerAndService($appointment->customer_id, $appointment->service_id)
            ->map(function ($discount) {
                return [
                    'id' => $discount->id,
                    'title' => $discount->title,
                    'type' => $discount->type,
                    'amount' => floatval($discount->amount ?? 0),
                    'start_date' => $discount->start_date ? Carbon::parse($discount->start_date)->toIso8601String() : null,
                    'end_date' => $discount->end_date ? Carbon::parse($discount->end_date)->toIso8601String() : null,
                ];
            })
            ->values();

        $petBehaviors = PetBehavior::with('icon')->orderBy('description')->get();

        return view('dashboard.appointment', compact('appointment', 'staffs', 'checkedIn', 'process', 'checkout', 'invoice', 'additionalServices', 'lastAppointmentRatings', 'initialTemperament', 'invoiceDiscountRules', 'petBehaviors', 'chauffeurPricingData', 'dbEstimatedPrice'));
    }

    public function listDashboard(Request $request, $id)
    {
        $service = Service::find($id);
        $infoMessage = null;
        if (isGroupClassService($service)) {
            // Apply same logic as serviceDashboard for group classes
            $now = Carbon::now();

            // Get future checked_in appointments for group classes
            $allCheckedInAppointments = Appointment::where('service_id', $id)
                ->where('status', 'checked_in')
                ->where('date', '>=', $now->toDateString())
                ->orderBy('date')
                ->orderBy('start_time')
                ->get();

            // Group by pet_id and take only the first (upcoming) per pet
            $checkedInAppointments = $allCheckedInAppointments->groupBy('pet_id')->map(function ($petAppointments) {
                return $petAppointments->first();
            })->values();
            $infoMessage = 'Showing only the next upcoming appointment per pet in Scheduled. Additional future sessions exist and will appear at the completion of the current session.';
        } else {
            $checkedInAppointments = Appointment::where('service_id', $id)
                ->where('status', 'checked_in')
                ->orderBy('date', 'desc')
                ->orderBy('start_time', 'desc')
                ->get();
        }
        $inProgressAppointments = Appointment::where('service_id', $id)->where('status', 'in_progress')->orderBy('date', 'desc')->orderBy('start_time', 'desc')->get();
        $completedAppointments = Appointment::where('service_id', $id)->where('status', 'completed')->orderBy('date', 'desc')->orderBy('start_time', 'desc')->limit(5)->get();
        $completedCount = Appointment::where('service_id', $id)->where('status', 'completed')->count();

        $issuedAppointments = collect();
        if ($service->category && str_contains(strtolower($service->category->name), 'daycare')) {
            $issuedAppointments = Appointment::where('service_id', $id)
                ->where('status', 'issue')
                ->with(['pet', 'customer.profile', 'staff.profile'])
                ->orderBy('date', 'desc')
                ->orderBy('start_time', 'desc')
                ->get();
        }

        return view('dashboard.list-service', compact('checkedInAppointments', 'inProgressAppointments', 'completedAppointments', 'completedCount', 'id', 'service', 'issuedAppointments', 'infoMessage'));
    }


    public function completedAppointments(Request $request)
    {
        $completedAppointments = Appointment::where('status', 'completed')
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(20);

        return view('dashboard.completed', compact('completedAppointments'));
    }
    
    private function getRevenueStatistics($period = 'month')
    {
        $today = Carbon::today();
        $data = [];
        $categories = [];
        $totalRevenue = 0;
        $previousPeriodRevenue = 0;
        
        if ($period === 'day') {
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $revenue = InvoiceItem::whereHas('invoice', function ($query) use ($date) {
                    $query->where('status', 'paid')
                        ->whereDate('paid_at', $date);
                })->sum('price');
                
                $data[] = round($revenue, 2);
                $categories[] = $date->format('M j');
                $totalRevenue += $revenue;
            }
            
            $previousStart = $today->copy()->subDays(13);
            $previousEnd = $today->copy()->subDays(7);
            $previousPeriodRevenue = InvoiceItem::whereHas('invoice', function ($query) use ($previousStart, $previousEnd) {
                $query->where('status', 'paid')
                    ->whereBetween('paid_at', [$previousStart, $previousEnd]);
            })->sum('price');
            
        } elseif ($period === 'week') {
            for ($i = 7; $i >= 0; $i--) {
                $weekStart = $today->copy()->subWeeks($i)->startOfWeek();
                $weekEnd = $weekStart->copy()->endOfWeek();
                $revenue = InvoiceItem::whereHas('invoice', function ($query) use ($weekStart, $weekEnd) {
                    $query->where('status', 'paid')
                        ->whereBetween('paid_at', [$weekStart, $weekEnd]);
                })->sum('price');
                
                $data[] = round($revenue, 2);
                $categories[] = $weekStart->format('M j');
                $totalRevenue += $revenue;
            }
            
            $previousStart = $today->copy()->subWeeks(15)->startOfWeek();
            $previousEnd = $today->copy()->subWeeks(8)->endOfWeek();
            $previousPeriodRevenue = InvoiceItem::whereHas('invoice', function ($query) use ($previousStart, $previousEnd) {
                $query->where('status', 'paid')
                    ->whereBetween('paid_at', [$previousStart, $previousEnd]);
            })->sum('price');
            
        } else {
            for ($i = 11; $i >= 0; $i--) {
                $monthStart = $today->copy()->subMonths($i)->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();
                $revenue = InvoiceItem::whereHas('invoice', function ($query) use ($monthStart, $monthEnd) {
                    $query->where('status', 'paid')
                        ->whereBetween('paid_at', [$monthStart, $monthEnd]);
                })->sum('price');
                
                $data[] = round($revenue, 2);
                $categories[] = $monthStart->format('M Y');
                $totalRevenue += $revenue;
            }
            
            $previousStart = $today->copy()->subMonths(23)->startOfMonth();
            $previousEnd = $today->copy()->subMonths(12)->endOfMonth();
            $previousPeriodRevenue = InvoiceItem::whereHas('invoice', function ($query) use ($previousStart, $previousEnd) {
                $query->where('status', 'paid')
                    ->whereBetween('paid_at', [$previousStart, $previousEnd]);
            })->sum('price');
        }
        
        $percentageChange = 0;
        if ($previousPeriodRevenue > 0) {
            $percentageChange = (($totalRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100;
        } elseif ($totalRevenue > 0) {
            $percentageChange = 100;
        }
        
        return [
            'data' => $data,
            'categories' => $categories,
            'total' => $totalRevenue,
            'percentageChange' => round($percentageChange, 2),
            'period' => $period
        ];
    }

    public function boardingProcessLog(Request $request)
    {
        $search = $request->get('search', '');
        
        $boardingAppointments = Appointment::with(['service.category', 'pet', 'customer.profile'])
            ->whereHas('service', function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%boarding%']);
                });
            })
            ->get();
        $appointmentIds = $boardingAppointments->pluck('id');
        
        $processesQuery = Process::with(['appointment.pet', 'appointment.customer.profile'])
            ->whereIn('appointment_id', $appointmentIds)
            ->whereNotNull('date');
        
        if (!empty($search)) {
            $processesQuery->where(function ($q) use ($search) {
                $q->whereHas('appointment.pet', function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%');
                })->orWhereHas('appointment.customer.profile', function ($query) use ($search) {
                    $query->where('first_name', 'LIKE', '%' . $search . '%')
                          ->orWhere('last_name', 'LIKE', '%' . $search . '%');
                });
            });
        }
        
        $processes = $processesQuery
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();
        
        $groupedProcesses = $processes->groupBy(function ($process) {
            return $process->date;
        })->map(function ($group) {
            $firstProcess = $group->first();
            
            $allTimes = [];
            foreach ($group as $process) {
                $flows = $process->flows ? json_decode($process->flows, true) : [];
                if (is_array($flows)) {
                    $timeFields = [
                        'am_meal_prep_time',
                        'am_med_prep_time',
                        'nose_tail_time',
                        'rest_1200_time',
                        'am_meal_dispense_time',
                        'am_med_dispense_time',
                        'pm_meal_prep_time',
                        'pm_med_prep_time',
                        'pm_meal_dispense_time',
                        'pm_med_dispense_time'
                    ];
                    
                    foreach ($timeFields as $field) {
                        if (!empty($flows[$field])) {
                            $allTimes[] = $flows[$field];
                        }
                    }
                    
                    foreach ($flows as $key => $value) {
                        if (strpos($key, 'additional_service_') === 0 && (strpos($key, '_start_time') !== false || strpos($key, '_end_time') !== false)) {
                            if (!empty($value)) {
                                $allTimes[] = $value;
                            }
                        }
                    }
                }
            }
            
            $earliestTime = null;
            $latestTime = null;
            
            if (!empty($allTimes)) {
                $validTimes = [];
                foreach ($allTimes as $time) {
                    $timeStr = is_string($time) ? $time : '';
                    if (empty($timeStr)) continue;
                    
                    if (strlen($timeStr) >= 5) {
                        try {
                            if (strlen($timeStr) >= 8) {
                                $carbon = \Carbon\Carbon::createFromFormat('H:i:s', $timeStr);
                            } else {
                                $carbon = \Carbon\Carbon::createFromFormat('H:i', $timeStr);
                            }
                            $validTimes[] = $carbon;
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
                
                if (!empty($validTimes)) {
                    $earliestTime = collect($validTimes)->min();
                    $latestTime = collect($validTimes)->max();
                }
            }
            
            return [
                'staff_id' => $firstProcess->staff_id,
                'staff_name' => $firstProcess->staff ? 
                    ($firstProcess->staff->profile->first_name ?? '') . ' ' . ($firstProcess->staff->profile->last_name ?? '') : 
                    'Unknown Staff',
                'date' => $firstProcess->date,
                'earliest_time' => $earliestTime ? $earliestTime->format('H:i:s') : null,
                'latest_time' => $latestTime ? $latestTime->format('H:i:s') : null,
                'processes' => $group->map(function ($process) {
                    $appointment = $process->appointment;
                    $petName = 'N/A';
                    $customerName = 'N/A';
                    
                    if ($appointment) {
                        if ($appointment->pet) {
                            $petName = $appointment->pet->name ?? 'N/A';
                        }
                        if ($appointment->customer && $appointment->customer->profile) {
                            $firstName = $appointment->customer->profile->first_name ?? '';
                            $lastName = $appointment->customer->profile->last_name ?? '';
                            $customerName = trim($firstName . ' ' . $lastName) ?: 'N/A';
                        }
                    }
                    
                    return [
                        'id' => $process->id,
                        'appointment_id' => $process->appointment_id,
                        'pet_name' => $petName,
                        'customer_name' => $customerName,
                        'start_time' => $process->start_time,
                        'pickup_time' => $process->pickup_time,
                        'notes' => $process->notes,
                        'flows' => $process->flows ? json_decode($process->flows, true) : null,
                        'created_at' => $process->created_at,
                        'updated_at' => $process->updated_at,
                    ];
                })->values()
            ];
        })->values();
        
        return view('dashboard.boarding-process-log', compact('groupedProcesses'));
    }

    public function createBoardingProcessLog(Request $request)
    {
        $boardingAppointments = Appointment::with(['service.category', 'pet', 'customer.profile'])
            ->whereHas('service', function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%boarding%']);
                });
            })
            ->where('status', 'in_progress')
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
        
        $staffs = User::whereHas('roles', function ($query) {
            $query->whereNot('title', 'customer');
        })->with('profile')->get();
        
        return view('dashboard.boarding-process-log-create', compact('boardingAppointments', 'staffs'));
    }

    public function saveBoardingProcessLog(Request $request)
    {
        $request->validate([
            'staff_id' => 'nullable|exists:users,id',
            'date' => 'required|date',
            'appointment_ids' => 'required|array|min:1',
            'appointment_ids.*' => 'exists:appointments,id',
            'flows' => 'required|array'
        ]);

        $staffId = $request->input('staff_id');
        $date = $request->input('date');
        $appointmentIds = $request->input('appointment_ids');
        $flows = $request->input('flows');

        $savedCount = 0;
        $errors = [];

        foreach ($appointmentIds as $appointmentId) {
            try {
                $appointment = Appointment::with('service.category')->find($appointmentId);
                if (!$appointment || !isBoardingService($appointment->service)) {
                    $errors[] = "Appointment ID {$appointmentId} is not a boarding service.";
                    continue;
                }

                $process = Process::where('appointment_id', $appointmentId)
                    ->where('date', $date)
                    ->first();

                if (!$process) {
                    $process = new Process;
                    $process->appointment_id = $appointmentId;
                    $process->date = $date;
                }

                if ($staffId) {
                    $process->staff_id = $staffId;
                }
                
                $existingFlows = $process->flows ? json_decode($process->flows, true) : [];
                if (!is_array($existingFlows)) {
                    $existingFlows = [];
                }
                $mergedFlows = array_merge($existingFlows, $flows);
                $process->flows = json_encode($mergedFlows);
                
                $process->save();
                $savedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error saving process for appointment ID {$appointmentId}: " . $e->getMessage();
            }
        }

        if ($savedCount > 0) {
            $message = "Successfully saved process logs for {$savedCount} pet(s).";
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " error(s) occurred.";
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'saved_count' => $savedCount,
                'errors' => $errors
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save any process logs.',
                'errors' => $errors
            ], 400);
        }
    }

    public function getBoardingCheckinData(Request $request)
    {
        $request->validate([
            'appointment_ids' => 'required|array|min:1',
            'appointment_ids.*' => 'exists:appointments,id'
        ]);

        $appointmentIds = $request->input('appointment_ids');
        $data = [];

        foreach ($appointmentIds as $appointmentId) {
            $appointment = Appointment::with(['pet', 'customer.profile'])->find($appointmentId);
            if (!$appointment) {
                continue;
            }

            $checkin = Checkin::where('appointment_id', $appointmentId)->first();
            $flows = null;

            if ($checkin && $checkin->flows) {
                $flows = json_decode($checkin->flows, true);
            }

            $pet = $appointment->pet;
            $dryFood = $flows['dry_food'] ?? [];
            $wetFood = $flows['wet_food'] ?? [];
            $meds = $flows['meds'] ?? [];
            $lunchDry = ! empty($dryFood['dispense_lunch']) && ($dryFood['dispense_lunch'] === true || $dryFood['dispense_lunch'] === 'true');
            $lunchWet = ! empty($wetFood['dispense_lunch']) && ($wetFood['dispense_lunch'] === true || $wetFood['dispense_lunch'] === 'true');
            if (! empty($flows['scheduled_lunch']) && ($flows['scheduled_lunch'] === true || $flows['scheduled_lunch'] === 'true') && ! $lunchDry && ! $lunchWet) {
                $lunchDry = true;
                $lunchWet = true;
            }
            $scheduledRest = ! empty($meds['dispense_rest']) && ($meds['dispense_rest'] === true || $meds['dispense_rest'] === 'true');

            $data[] = [
                'appointment_id' => $appointmentId,
                'pet_name' => $pet ? $pet->name : 'N/A',
                'pet_img' => $pet ? $pet->pet_img : null,
                'lunch_dry' => $lunchDry,
                'lunch_wet' => $lunchWet,
                'scheduled_rest' => $scheduledRest,
                'customer_name' => $appointment->customer && $appointment->customer->profile
                    ? $appointment->customer->profile->first_name . ' ' . $appointment->customer->profile->last_name
                    : 'N/A',
                'customer_avatar' => $appointment->customer && $appointment->customer->profile
                    ? $appointment->customer->profile->avatar_img : null,
                'checkin' => [
                    'id' => $checkin ? $checkin->id : null,
                    'flows' => $flows
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getTreatmentListYesterdayPetIds(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'appointment_ids' => 'nullable|array',
            'appointment_ids.*' => 'exists:appointments,id',
        ]);

        $date = Carbon::parse($request->input('date'));
        $yesterday = $date->copy()->subDay()->format('Y-m-d');
        $appointmentIds = $request->input('appointment_ids', []);

        $process = Process::where('date', $yesterday)->first();
        if (!$process || !$process->flows) {
            return response()->json([
                'success' => true,
                'yesterday_pet_ids' => [],
                'yesterday_reports_pm_issues' => [],
            ]);
        }

        $flows = json_decode($process->flows, true);
        $nextDayData = $flows['next_day_treatment_list_tlr'] ?? [];
        $selectedIds = $nextDayData['selected_pet_ids'] ?? [];
        if (!is_array($selectedIds)) {
            $selectedIds = [];
        }
        $selectedIds = array_map('intval', $selectedIds);
        // $feedingPm = $flows['feeding_pm'] ?? [];
        // $feedingPmIds = isset($feedingPm['selected_pet_ids']) && is_array($feedingPm['selected_pet_ids'])
        //     ? array_map('intval', $feedingPm['selected_pet_ids']) : [];
        $reportsPm = $flows['reports_pm'] ?? [];
        $reportsPmIds = isset($reportsPm['selected_pet_ids']) && is_array($reportsPm['selected_pet_ids'])
            ? array_map('intval', $reportsPm['selected_pet_ids']) : [];
        $selectedIds = array_values(array_unique(array_merge($selectedIds, $reportsPmIds)));
        if (!empty($appointmentIds)) {
            $appointmentIdsInt = array_map('intval', (array) $appointmentIds);
            $selectedIds = array_values(array_intersect($selectedIds, $appointmentIdsInt));
        }

        $yesterdayReportsPmIssues = isset($reportsPm['issues']) && is_array($reportsPm['issues'])
            ? $reportsPm['issues'] : [];

        return response()->json([
            'success' => true,
            'yesterday_pet_ids' => array_values(array_unique($selectedIds)),
            'yesterday_reports_pm_issues' => $yesterdayReportsPmIssues,
        ]);
    }

    public function editBoardingProcessLog($id)
    {
        
        $process = Process::with(['appointment.pet', 'appointment.customer.profile'])
            ->findOrFail($id);
        
        $processes = Process::with(['appointment.pet', 'appointment.customer.profile'])
            ->where('date', $process->date)
            ->get();
        
        $appointmentIds = $processes->pluck('appointment_id')->toArray();
        
        $flows = $process->flows ? json_decode($process->flows, true) : [];
        
        return view('dashboard.boarding-process-log-edit', compact('process', 'processes', 'appointmentIds', 'flows'));
    }

    public function updateBoardingProcessLog(Request $request, $id)
    {
        $process = Process::findOrFail($id);
        
        $request->validate([
            'flows' => 'required|array',
        ]);

        $newFlows = $request->input('flows');
        $startTime = $request->input('start_time');
        $pickupTime = $request->input('pickup_time');

        // Get all processes for the same date (no longer filtering by staff_id)
        $processes = Process::where('date', $process->date)
            ->get();

        $updatedCount = 0;
        $errors = [];

        foreach ($processes as $proc) {
            try {
                // Merge existing flows with new flows (new flows take precedence)
                $existingFlows = $proc->flows ? json_decode($proc->flows, true) : [];
                if (!is_array($existingFlows)) {
                    $existingFlows = [];
                }
                $mergedFlows = array_merge($existingFlows, $newFlows);
                
                $proc->flows = json_encode($mergedFlows);
                if ($startTime) {
                    $proc->start_time = $startTime;
                }
                if ($pickupTime) {
                    $proc->pickup_time = $pickupTime;
                }
                $proc->save();
                $updatedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error updating process ID {$proc->id}: " . $e->getMessage();
            }
        }

        if ($updatedCount > 0) {
            $message = "Successfully updated process logs for {$updatedCount} pet(s).";
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " error(s) occurred.";
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
                'errors' => $errors
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update any process logs.',
                'errors' => $errors
            ], 400);
        }
    }

    public function deleteBoardingProcessLog($id)
    {
        $process = Process::findOrFail($id);
        
        $processes = Process::where('staff_id', $process->staff_id)
            ->where('date', $process->date)
            ->get();
        
        $deletedCount = 0;
        foreach ($processes as $proc) {
            $proc->delete();
            $deletedCount++;
        }

        return redirect()->route('boarding-process-log')
            ->with('success', "Successfully deleted process logs for {$deletedCount} pet(s).");
    }

    public function groomerCalendar($id)
    {
        $groomers = User::whereHas('roles', function ($query) {
            $query->whereNot('title', 'customer');
        })->with('profile')->get();

        $groomerId = trim((string) request()->query('groomer_id', ''));
        $date = trim((string) request()->query('date', ''));

        $groomerId = $groomerId === '' ? null : $groomerId;
        $date = $date === '' ? null : $date;

        return view('dashboard.groomer-calendar', compact('id', 'groomers', 'groomerId', 'date'));
    }

    public function groomerCalendarData(Request $request, $id)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $groomerId = trim((string) $request->query('groomer_id', ''));
        $date = trim((string) $request->query('date', ''));

        $groomerId = $groomerId === '' ? null : $groomerId;
        $date = $date === '' ? null : $date;

        $rangeStart = $start ? Carbon::parse($start) : Carbon::today()->startOfDay();
        $rangeEnd = $end ? Carbon::parse($end) : Carbon::today()->endOfDay();

        if (!empty($date)) {
            $selectedDate = Carbon::parse($date);
            $rangeStart = $selectedDate->copy()->startOfDay();
            $rangeEnd = $selectedDate->copy()->endOfDay();
        }

        $dateStart = $rangeStart->copy()->startOfDay();
        $dateEnd = $rangeEnd->copy()->subSecond()->endOfDay();

        $staffsQuery = User::whereHas('roles', function ($query) {
            $query->whereNot('title', 'customer');
        })->with('profile');

        if (!empty($groomerId)) {
            $staffsQuery->where('id', $groomerId);
        }

        $staffs = $staffsQuery->get();

        $appointmentsQuery = Appointment::where('service_id', $id)
            ->where('status', '!=', 'cancelled')
            ->whereDate('date', '>=', $dateStart->toDateString())
            ->whereDate('date', '<=', $dateEnd->toDateString())
            ->with(['pet', 'staff.profile', 'process', 'service']);

        if (!empty($groomerId)) {
            $appointmentsQuery->where(function ($query) use ($groomerId) {
                $query->where('staff_id', $groomerId)
                    ->orWhereHas('process', function ($processQuery) use ($groomerId) {
                        $processQuery->where('staff_id', $groomerId);
                    });
            });
        }

        $appointments = $appointmentsQuery
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $additionalServiceIds = $appointments->flatMap(function ($appointment) {
            $serviceIds = [];

            if (!empty($appointment->additional_service_ids)) {
                $serviceIds = array_merge($serviceIds, explode(',', $appointment->additional_service_ids));
            }

            if (!empty($appointment->metadata['secondary_service_ids'] ?? null)) {
                $serviceIds = array_merge($serviceIds, explode(',', $appointment->metadata['secondary_service_ids']));
            }

            return collect($serviceIds)
                ->map(function ($serviceId) {
                    return (int) trim($serviceId);
                })
                ->filter(function ($serviceId) {
                    return $serviceId > 0;
                });
        })->unique()->values();

        $additionalServiceNames = Service::whereIn('id', $additionalServiceIds)
            ->pluck('name', 'id');

        $resources = $staffs->map(function ($staff) {
            $firstName = optional($staff->profile)->first_name;
            $displayName = trim((string) $firstName);
            if ($displayName === '') {
                $displayName = $staff->name ?: $staff->email;
            }

            return [
                'id' => (string) $staff->id,
                'title' => $displayName,
                'extendedProps' => [
                    'img_url' => empty(optional($staff->profile)->avatar_img)
                        ? null
                        : asset('storage/profiles/' . optional($staff->profile)->avatar_img),
                ],
            ];
        })->values();

        $events = $appointments->map(function ($appointment) use ($additionalServiceNames) {
            if (!$appointment->date || !$appointment->start_time || !$appointment->end_time) {
                return null;
            }

            $resolvedStaffId = $appointment->staff_id ?: optional($appointment->process)->staff_id;
            if (!$resolvedStaffId) {
                return null;
            }

            $startAt = Carbon::parse($appointment->date . ' ' . $appointment->start_time);
            $endAt = Carbon::parse($appointment->date . ' ' . $appointment->end_time);

            if ($endAt->lessThanOrEqualTo($startAt)) {
                $endAt = $startAt->copy()->addMinutes(30);
            }

            $appointmentAdditionalServiceNames = [];

            if (!empty($appointment->additional_service_ids)) {
                $appointmentAdditionalServiceNames = array_merge(
                    $appointmentAdditionalServiceNames,
                    collect(explode(',', $appointment->additional_service_ids))
                        ->map(function ($serviceId) use ($additionalServiceNames) {
                            return $additionalServiceNames->get((int) trim($serviceId));
                        })
                        ->filter()
                        ->values()
                        ->all()
                );
            }

            $appointmentAdditionalServiceNames = array_values(array_unique($appointmentAdditionalServiceNames));

            return [
                'id' => (string) $appointment->id,
                'resourceId' => (string) $resolvedStaffId,
                'title' => optional($appointment->pet)->name ?: 'Pet Booking',
                'start' => $startAt->format('Y-m-d\TH:i:s'),
                'end' => $endAt->format('Y-m-d\TH:i:s'),
                'extendedProps' => [
                    'pet_name' => optional($appointment->pet)->name,
                    'img_url' => empty(optional($appointment->pet)->pet_img)
                        ? null
                        : asset('storage/pets/' . optional($appointment->pet)->pet_img),
                    'main_service' => optional($appointment->service)->name,
                    'additional_services' => $appointmentAdditionalServiceNames,
                ],
            ];
        })->filter()->values();

        return response()->json([
            'resources' => $resources,
            'events' => $events,
        ]);
    }

    public function exportBoardingDetailReportPDF(Request $request, $id)
    {
        $appointment = Appointment::with([
            'pet',
            'customer.profile',
            'service',
            'staff.profile',
            'checkout'
        ])->findOrFail($id);

        $checkin = Checkin::where('appointment_id', $appointment->id)->first();
        if ($checkin && $checkin->flows) {
            $checkin->flows = json_decode($checkin->flows, true);
        }

        $pet = $appointment->pet;
        $customer = $appointment->customer;
        $ownerProfile = optional($appointment->customer)->profile;
        $ownerName = trim((string) ($ownerProfile->first_name ?? '') . ' ' . ($ownerProfile->last_name ?? ''));
        if ($ownerName === '') {
            $ownerName = optional($customer)->name ?? (optional($customer)->email ?? 'Not set');
        }

        $petAge = null;
        if (!empty($pet->age)) {
            $petAge = (int) $pet->age;
        } elseif (!empty($pet->birthdate)) {
            try {
                $petAge = \Carbon\Carbon::parse($pet->birthdate)->age;
            } catch (\Exception $e) {
                $petAge = null;
            }
        }
        $isSenior = $petAge !== null ? $petAge >= 8 : false;

        $behaviorIds = collect(is_array($pet->pet_behavior_id ?? null) ? $pet->pet_behavior_id : [])
            ->filter()
            ->map(fn ($behaviorId) => (int) $behaviorId)
            ->unique()
            ->values();
        $behaviorLabels = $behaviorIds->isNotEmpty()
            ? PetBehavior::whereIn('id', $behaviorIds->all())->pluck('description')->filter()->values()->all()
            : [];

        $checkinFlows = is_array(optional($checkin)->flows) ? $checkin->flows : [];
        $medsFlows = is_array($checkinFlows['meds'] ?? null) ? $checkinFlows['meds'] : [];

        $isTruthy = function ($value) {
            return $value === true || $value === 1 || $value === '1' || $value === 'true';
        };

        $requestMedsAm = filter_var($request->query('meds_am', null), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $requestMedsPm = filter_var($request->query('meds_pm', null), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $hasRequestMedicationState = $requestMedsAm !== null || $requestMedsPm !== null;

        if ($hasRequestMedicationState) {
            $medicationRequired = ($requestMedsAm === true) || ($requestMedsPm === true);
        } else {
            $medicationRequired =
                $isTruthy($medsFlows['dispense_am'] ?? null) ||
                $isTruthy($medsFlows['dispense_pm'] ?? null) ||
                $isTruthy($checkinFlows['medications_am'] ?? null) ||
                $isTruthy($checkinFlows['medications_pm'] ?? null);
        }

        $stayDuration = 'Not set';
        $startDate = null;
        $endDate = null;
        try {
            $startDate = \Carbon\Carbon::parse($appointment->date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($appointment->end_date)->startOfDay();

            if ($startDate && $endDate) {
                if ($endDate->lt($startDate)) {
                    $endDate = $startDate->copy();
                }

                $totalDays = $startDate->diffInDays($endDate) + 1;
                $durationLabel = $totalDays . ' day' . ($totalDays > 1 ? 's' : '');
                $dateRangeLabel = $startDate->format('M j, Y');
                if (!$startDate->isSameDay($endDate)) {
                    $dateRangeLabel .= ' - ' . $endDate->format('M j, Y');
                }
                $stayDuration = $durationLabel . ' (' . $dateRangeLabel . ')';
            }
        } catch (\Exception $e) {
            $stayDuration = 'Not set';
        }

        $pdf = Pdf::loadView('archives.boarding-detail-report-pdf', [
            'appointment' => $appointment,
            'showPetStayInfo' => true,
            'ownerName' => $ownerName,
            'stayDuration' => $stayDuration,
            'isSenior' => $isSenior,
            'medicationRequired' => $medicationRequired,
            'behaviorLabels' => $behaviorLabels,
        ]);

        $fileName = 'Boarding_Report_' . $appointment->pet->name . '_' . date('Y-m-d', strtotime($appointment->date)) . '.pdf';

        return $pdf->download($fileName);
    }
}