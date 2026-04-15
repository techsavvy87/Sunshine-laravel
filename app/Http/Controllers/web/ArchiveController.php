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
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $customerPet = $request->get('customer');
        $serviceId = $request->get('service');
        $staffId = $request->get('staff');
        $datetimes = $request->get('datetimes');

        if ($customerPet) {
            $finishedAppointments = Appointment::whereIn('status', ['finished', 'cancelled', 'no_show'])
                ->where(function ($query) use ($customerPet) {
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
            $finishedAppointments = Appointment::whereIn('status', ['finished', 'cancelled', 'no_show']);
        }

        if ($serviceId) {
            $finishedAppointments = $finishedAppointments->where('service_id', $serviceId);
        }

        if ($staffId) {
            $finishedAppointments = $finishedAppointments->where('staff_id', $staffId);
        }

        if ($datetimes) {
            [$start, $end] = explode(' - ', $datetimes);

            $startDateTime = \Carbon\Carbon::createFromFormat('m/d/y h:i A', trim($start))->format('Y-m-d H:i:s');
            $endDateTime = \Carbon\Carbon::createFromFormat('m/d/y h:i A', trim($end))->format('Y-m-d H:i:s');

            $finishedAppointments = $finishedAppointments->where(function($query) use ($startDateTime, $endDateTime) {
                $query->whereRaw("CONCAT(date, ' ', start_time) >= ?", [$startDateTime])
                    ->whereRaw("CONCAT(date, ' ', end_time) <= ?", [$endDateTime]);
            });
        }

        $finishedAppointments = $finishedAppointments
            ->with(['pet', 'customer.profile', 'service', 'staff.profile'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate($perPage);

        $services = Service::orderBy('name')->get();
        $staffs = User::whereHas('roles', function ($query) {
            $query->whereNot('title', 'customer');
        })->with('profile')->get();

        return view('archives.index', compact('finishedAppointments', 'services', 'staffs', 'customerPet', 'serviceId', 'staffId', 'datetimes'));
    }

    public function detail($id)
    {
        $appointment = Appointment::with([
            'pet',
            'customer.profile',
            'service',
            'staff.profile',
            'invoice.items',
            'transactions.invoice'
        ])->findOrFail($id);

        $checkin = Checkin::where('appointment_id', $appointment->id)->first();
        if ($checkin && $checkin->flows) {
            $checkin->flows = json_decode($checkin->flows, true);
        }

        $process = null;
        $processes = collect();
        
        if (isBoardingService($appointment->service)) {
            $processes = Process::with('staff.profile')
                ->where('appointment_id', $appointment->id)
                ->whereNull('detail_id')
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();
            foreach ($processes as $proc) {
                if ($proc->flows) {
                    $proc->flows = json_decode($proc->flows, true);
                }
            }
        } else {
            $process = Process::where('appointment_id', $appointment->id)->whereNull('detail_id')->first();
            if ($process && $process->flows) {
                $process->flows = json_decode($process->flows, true);
            }
        }

        $packageProcesses = [];
        if (isPackageService($appointment->service)) {
            $packageProcesses = Process::where('appointment_id', $appointment->id)
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();
            foreach ($packageProcesses as $proc) {
                if ($proc->flows) {
                    $proc->flows = json_decode($proc->flows, true);
                }
            }
        }

        $alaCarteProcesses = [];
        if (isAlaCarteService($appointment->service) && $appointment->metadata && isset($appointment->metadata['secondary_service_ids'])) {
            $secondaryServiceIds = explode(',', $appointment->metadata['secondary_service_ids']);
            $alaCarteProcesses = Process::where('appointment_id', $appointment->id)
                ->whereIn('detail_id', $secondaryServiceIds)
                ->with('staff.profile')
                ->get()
                ->keyBy('detail_id');
        }

        $checkout = Checkout::where('appointment_id', $appointment->id)->first();
        if ($checkout && $checkout->flows) {
            $checkout->flows = json_decode($checkout->flows, true);
        }

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

        $staffs = User::whereHas('roles', function ($query) {
            $query->whereNot('title', 'customer');
        })->with('profile')->get();

        return view('archives.detail', compact('appointment', 'checkin', 'process', 'processes', 'checkout', 'staffs', 'lastAppointmentRatings', 'alaCarteProcesses', 'packageProcesses'));
    }

    /**
     * Return the report section HTML for an appointment (for embedding in pet Previous note modal).
     */
    public function reportFragment($id)
    {
        $appointment = Appointment::with([
            'pet',
            'customer.profile',
            'service',
            'staff.profile',
            'invoice.items',
            'transactions.invoice'
        ])->findOrFail($id);

        $checkin = Checkin::where('appointment_id', $appointment->id)->first();
        if ($checkin && $checkin->flows) {
            $checkin->flows = json_decode($checkin->flows, true);
        }

        $process = null;
        $processes = collect();

        if (isBoardingService($appointment->service)) {
            $processes = Process::where('appointment_id', $appointment->id)
                ->whereNull('detail_id')
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();
            foreach ($processes as $proc) {
                if ($proc->flows) {
                    $proc->flows = json_decode($proc->flows, true);
                }
            }
        } else {
            $process = Process::where('appointment_id', $appointment->id)->whereNull('detail_id')->first();
            if ($process && $process->flows) {
                $process->flows = json_decode($process->flows, true);
            }
        }

        $packageProcesses = [];
        if (isPackageService($appointment->service)) {
            $packageProcesses = Process::where('appointment_id', $appointment->id)
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();
            foreach ($packageProcesses as $proc) {
                if ($proc->flows) {
                    $proc->flows = json_decode($proc->flows, true);
                }
            }
        }

        $alaCarteProcesses = [];
        if (isAlaCarteService($appointment->service) && $appointment->metadata && isset($appointment->metadata['secondary_service_ids'])) {
            $secondaryServiceIds = explode(',', $appointment->metadata['secondary_service_ids']);
            $alaCarteProcesses = Process::where('appointment_id', $appointment->id)
                ->whereIn('detail_id', $secondaryServiceIds)
                ->with('staff.profile')
                ->get()
                ->keyBy('detail_id');
        }

        $checkout = Checkout::where('appointment_id', $appointment->id)->first();
        if ($checkout && $checkout->flows) {
            $checkout->flows = json_decode($checkout->flows, true);
        }

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

        $html = view('archives.partials.report-content', compact('appointment', 'checkin', 'process', 'processes', 'checkout', 'lastAppointmentRatings', 'alaCarteProcesses', 'packageProcesses') + ['hideExportButtons' => true])->render();

        return response()->json(['html' => $html]);
    }

    public function getConciergeReport($id, Request $request)
    {
        $appointment = Appointment::findOrFail($id);
        
        if (!isBoardingService($appointment->service)) {
            return response()->json(['html' => '<p class="text-base-content/70">Concierge report is only available for boarding services.</p>']);
        }

        $date = $request->get('date');
        if (!$date) {
            return response()->json(['html' => '<p class="text-base-content/70">Please select a date.</p>']);
        }

        $process = Process::with('staff.profile')
            ->where('appointment_id', $appointment->id)
            ->whereNull('detail_id')
            ->where('date', $date)
            ->first();

        if (!$process || !$process->flows) {
            return response()->json(['html' => '<p class="text-base-content/70">No concierge report data available for the selected date.</p>']);
        }

        if (is_string($process->flows)) {
            $process->flows = json_decode($process->flows, true);
        }

        $staffNames = [];
        $flows = is_array($process->flows) ? $process->flows : [];
        $staffSignOffIds = [];
        foreach ($flows as $stepData) {
            if (isset($stepData['staff_sign_off']) && is_array($stepData['staff_sign_off'])) {
                foreach ($stepData['staff_sign_off'] as $uid) {
                    if ($uid !== null && $uid !== '') {
                        $staffSignOffIds[] = is_numeric($uid) ? (int) $uid : $uid;
                    }
                }
            }
        }
        $staffSignOffIds = array_unique(array_filter($staffSignOffIds));
        if (!empty($staffSignOffIds)) {
            $users = User::with('profile')->whereIn('id', $staffSignOffIds)->get();
            foreach ($users as $u) {
                $name = 'N/A';
                if ($u->profile) {
                    $name = trim(($u->profile->first_name ?? '') . ' ' . ($u->profile->last_name ?? ''));
                    if ($name === '') {
                        $name = $u->name ?? 'N/A';
                    }
                } else {
                    $name = $u->name ?? 'N/A';
                }
                $staffNames[(string) $u->id] = $name;
            }
        }

        $html = view('archives.partials.concierge-report-details', [
            'process' => $process,
            'staff_names' => $staffNames,
        ])->render();
        
        return response()->json(['html' => $html]);
    }

    public function exportGroomingReportPDF($id)
    {
        $appointment = Appointment::with([
            'pet',
            'customer.profile',
            'service',
            'staff.profile'
        ])->findOrFail($id);

        $checkin = Checkin::where('appointment_id', $appointment->id)->first();
        if ($checkin && $checkin->flows) {
            $checkin->flows = json_decode($checkin->flows, true);
        }

        $process = Process::where('appointment_id', $appointment->id)->first();
        if ($process && $process->flows) {
            $process->flows = json_decode($process->flows, true);
        }

        $checkout = Checkout::where('appointment_id', $appointment->id)->first();
        if ($checkout && $checkout->flows) {
            $checkout->flows = json_decode($checkout->flows, true);
        }

        $pdf = Pdf::loadView('archives.grooming-report-pdf', compact('appointment', 'checkin', 'process', 'checkout'));

        $fileName = 'Grooming_Report_' . $appointment->pet->name . '_' . date('Y-m-d', strtotime($appointment->date)) . '.pdf';

        return $pdf->download($fileName);
    }

    public function exportTrainingReportPDF($id)
    {
        $appointment = Appointment::with([
            'pet',
            'customer.profile',
            'service',
            'staff.profile'
        ])->findOrFail($id);

        $checkin = Checkin::where('appointment_id', $appointment->id)->first();
        if ($checkin && $checkin->flows) {
            $checkin->flows = json_decode($checkin->flows, true);
        }

        $checkout = Checkout::where('appointment_id', $appointment->id)->first();
        if ($checkout && $checkout->flows) {
            $checkout->flows = json_decode($checkout->flows, true);
        }

        $allCompletedAppointments = Appointment::where('appointments.pet_id', $appointment->pet_id)
            ->where('appointments.id', '!=', $appointment->id)
            ->join('checkouts', 'appointments.id', '=', 'checkouts.appointment_id')
            ->with('service.category')
            ->orderBy('checkouts.updated_at', 'desc')
            ->orderBy('checkouts.created_at', 'desc')
            ->select('appointments.*')
            ->get();

        $lastAppointment = null;
        $lastAppointmentRatings = [];
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

        $pdf = Pdf::loadView('archives.training-report-pdf', compact('appointment', 'checkin', 'checkout', 'lastAppointmentRatings'));

        $fileName = 'Training_Report_' . $appointment->pet->name . '_' . date('Y-m-d', strtotime($appointment->date)) . '.pdf';

        return $pdf->download($fileName);
    }

    public function exportDaycareReportPDF($id)
    {
        $appointment = Appointment::with([
            'pet',
            'customer.profile',
            'service',
            'staff.profile'
        ])->findOrFail($id);

        if (!isDaycareService($appointment->service)) {
            abort(404, 'This is not a daycare appointment');
        }

        $checkin = Checkin::where('appointment_id', $appointment->id)->first();
        if ($checkin && $checkin->flows) {
            $checkin->flows = json_decode($checkin->flows, true);
        }

        $process = Process::where('appointment_id', $appointment->id)->first();
        if ($process && $process->flows) {
            $process->flows = json_decode($process->flows, true);
        }

        $checkout = Checkout::where('appointment_id', $appointment->id)->first();
        if ($checkout && $checkout->flows) {
            $checkout->flows = json_decode($checkout->flows, true);
        }

        $pdf = Pdf::loadView('archives.daycare-report-pdf', compact('appointment', 'checkin', 'process', 'checkout'));

        $fileName = 'Daycare_Report_' . $appointment->pet->name . '_' . date('Y-m-d', strtotime($appointment->date)) . '.pdf';

        return $pdf->download($fileName);
    }

    public function exportGroupClassReportPDF($id)
    {
        $appointment = Appointment::with([
            'pet',
            'customer.profile',
            'service',
            'staff.profile'
        ])->findOrFail($id);

        if (!isGroupClassService($appointment->service)) {
            abort(404, 'This is not a group class appointment');
        }

        $checkin = Checkin::where('appointment_id', $appointment->id)->first();
        if ($checkin && $checkin->flows) {
            $checkin->flows = json_decode($checkin->flows, true);
        }

        $process = Process::where('appointment_id', $appointment->id)->first();
        if ($process && $process->flows) {
            $process->flows = json_decode($process->flows, true);
        }

        $checkout = Checkout::where('appointment_id', $appointment->id)->first();
        if ($checkout && $checkout->flows) {
            $checkout->flows = json_decode($checkout->flows, true);
        }

        $pdf = Pdf::loadView('archives.group-class-report-pdf', compact('appointment', 'checkin', 'process', 'checkout'));

        $fileName = 'Group_Class_Report_' . $appointment->pet->name . '_' . date('Y-m-d', strtotime($appointment->date)) . '.pdf';

        return $pdf->download($fileName);
    }

    public function exportAlaCarteReportPDF($id)
    {
        $appointment = Appointment::with([
            'pet',
            'customer.profile',
            'service',
            'staff.profile'
        ])->findOrFail($id);

        if (!isAlaCarteService($appointment->service)) {
            abort(404, 'This is not an A la Carte appointment');
        }

        $checkin = Checkin::where('appointment_id', $appointment->id)->first();
        if ($checkin && $checkin->flows) {
            $checkin->flows = json_decode($checkin->flows, true);
        }

        $process = Process::where('appointment_id', $appointment->id)->whereNull('detail_id')->first();
        if ($process && $process->flows) {
            $process->flows = json_decode($process->flows, true);
        }

        $alaCarteProcesses = [];
        if ($appointment->metadata && isset($appointment->metadata['secondary_service_ids'])) {
            $secondaryServiceIds = explode(',', $appointment->metadata['secondary_service_ids']);
            $alaCarteProcesses = Process::where('appointment_id', $appointment->id)
                ->whereIn('detail_id', $secondaryServiceIds)
                ->with('staff.profile')
                ->get()
                ->keyBy('detail_id');
        }

        $checkout = Checkout::where('appointment_id', $appointment->id)->first();
        if ($checkout && $checkout->flows) {
            $checkout->flows = json_decode($checkout->flows, true);
        }

        $pdf = Pdf::loadView('archives.ala-carte-report-pdf', compact('appointment', 'checkin', 'process', 'checkout', 'alaCarteProcesses'));

        $fileName = 'A_la_Carte_Report_' . $appointment->pet->name . '_' . date('Y-m-d', strtotime($appointment->date)) . '.pdf';

        return $pdf->download($fileName);
    }

    public function exportBoardingReportPDF($id)
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

        $process = null;
        $processes = collect();

        if (isBoardingService($appointment->service)) {
            $processes = Process::where('appointment_id', $appointment->id)
                ->orderBy('date')
                ->orderBy('start_time')
                ->get();
            foreach ($processes as $p) {
                if ($p->flows) {
                    $p->flows = json_decode($p->flows, true);
                }
            }
        } else {
            $process = Process::where('appointment_id', $appointment->id)->whereNull('detail_id')->first();
            if ($process && $process->flows) {
                $process->flows = json_decode($process->flows, true);
            }
        }

        $checkout = Checkout::where('appointment_id', $appointment->id)->first();
        if ($checkout && $checkout->flows) {
            $checkout->flows = json_decode($checkout->flows, true);
        }

        $pdf = Pdf::loadView('archives.boarding-report-pdf', compact('appointment', 'checkin', 'process', 'processes', 'checkout'));

        $fileName = 'Boarding_Report_' . $appointment->pet->name . '_' . date('Y-m-d', strtotime($appointment->date)) . '.pdf';

        return $pdf->download($fileName);
    }

    public function exportConciergeReportPDF(Request $request, $id)
    {
        $appointment = Appointment::with([
            'pet',
            'customer.profile',
            'service',
            'staff.profile'
        ])->findOrFail($id);

        $date = $request->input('date');
        if (!$date) {
            abort(400, 'Date parameter is required');
        }

        $process = Process::where('appointment_id', $appointment->id)
            ->where('date', $date)
            ->whereNull('detail_id')
            ->first();

        if (!$process) {
            abort(404, 'Concierge report not found for the selected date');
        }

        if ($process->flows) {
            $process->flows = json_decode($process->flows, true);
        }

        $pdf = Pdf::loadView('archives.concierge-report-pdf', compact('appointment', 'process', 'date'));

        $fileName = 'Concierge_Report_' . $appointment->pet->name . '_' . date('Y-m-d', strtotime($date)) . '.pdf';

        return $pdf->download($fileName);
    }

    public function exportPackageReportPDF($id)
    {
        $appointment = Appointment::with([
            'pet',
            'customer.profile',
            'service',
            'staff.profile'
        ])->findOrFail($id);

        if (!isPackageService($appointment->service)) {
            abort(404, 'This is not a package appointment');
        }

        $checkin = Checkin::where('appointment_id', $appointment->id)->first();
        if ($checkin && $checkin->flows) {
            $checkin->flows = json_decode($checkin->flows, true);
        }

        $process = null;
        $packageProcesses = collect();
        
        $packageProcesses = Process::where('appointment_id', $appointment->id)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
        foreach ($packageProcesses as $proc) {
            if ($proc->flows) {
                $proc->flows = json_decode($proc->flows, true);
            }
        }
        
        $checkout = Checkout::where('appointment_id', $appointment->id)->first();
        if ($checkout && $checkout->flows) {
            $checkout->flows = json_decode($checkout->flows, true);
        }

        $pdf = Pdf::loadView('archives.package-report-pdf', compact('appointment', 'checkin', 'process', 'packageProcesses', 'checkout'));

        $fileName = 'Package_Report_' . $appointment->pet->name . '_' . date('Y-m-d', strtotime($appointment->date)) . '.pdf';

        return $pdf->download($fileName);
    }
}