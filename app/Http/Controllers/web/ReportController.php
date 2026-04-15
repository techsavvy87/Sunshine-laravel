<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\IncidentReport;
use App\Models\User;
use App\Models\PetProfile;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\ServiceCategory;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function listIncidentReports(Request $request, $serviceId)
    {
        $perPage = $request->get('per_page', 20);
        $pet = $request->get('pet');
        $staffId = $request->get('staff');
        $datetimes = $request->get('datetimes');

        $incidentReportsQuery = IncidentReport::where('service_id', $serviceId);

        // Search by pet name: find matching pet ids, then filter incident reports whose
        // comma-separated `pet_ids` contains any of those ids (MySQL FIND_IN_SET).
        if (!empty($pet)) {
            $petIds = PetProfile::where('name', 'like', "%{$pet}%")->pluck('id')->toArray();

            if (count($petIds) > 0) {
                $incidentReportsQuery->where(function ($q) use ($petIds) {
                    foreach ($petIds as $id) {
                        $q->orWhereRaw('FIND_IN_SET(?, pet_ids)', [$id]);
                    }
                });
            } else {
                // no pets match the name -> return empty result set
                $incidentReportsQuery->whereRaw('0 = 1');
            }
        }

        // filter by staff, staff is staff_id
        if (!empty($staffId) && $staffId != -1) {
            $incidentReportsQuery->where(function ($q) use ($staffId) {
                $q->orWhereRaw('FIND_IN_SET(?, staff_ids)', [$staffId]);
            });
        }

        // filter by date range
        if (!empty($datetimes)) {
            [$start, $end] = explode(' - ', $datetimes);
            // Parse to Carbon (assuming format: m/d/y h:i A)
            $startDateTime = Carbon::createFromFormat('m/d/y h:i A', trim($start))->format('Y-m-d H:i:s');
            $endDateTime = Carbon::createFromFormat('m/d/y h:i A', trim($end))->format('Y-m-d H:i:s');

            // Filter appointments where start_time is within the range
            $appointments = $appointments->where(function($query) use ($startDateTime, $endDateTime) {
                $query->whereRaw("CONCAT(date, ' ', start_time) >= ?", [$startDateTime])
                    ->whereRaw("CONCAT(date, ' ', end_time) <= ?", [$endDateTime]);
            });
        }

        $incidentReports = $incidentReportsQuery->orderBy('created_at', 'desc')->paginate($perPage);
        foreach ($incidentReports as $report) {
            $report->pets = PetProfile::whereIn('id', array_filter(array_map('trim', explode(',', $report->pet_ids))))->get();
            $report->staffs = User::whereIn('id', array_filter(array_map('trim', explode(',', $report->staff_ids))))->get();
        }

        $staffs = User::whereHas('roles', function ($query) {
                    $query->whereNot('title', 'customer');
                })->get();

        return view('reports.list-incident', compact('incidentReports', 'staffs', 'pet', 'staffId', 'datetimes', 'perPage', 'serviceId'));
    }

    public function addIncidentReport(Request $request, $serviceId)
    {
        return view('reports.create-incident', compact('serviceId'));
    }

    public function createIncidentReport(Request $request)
    {
        $request->validate([
            'pets' => 'required|array|min:1',
            'staffs' => 'required|array|min:1',
            'injury_type' => 'required|string',
            'injury_location' => 'required|string',
            'needs_treatment' => 'required|string',
            'is_emergency' => 'required|string',
            'contact_owner' => 'required|string',
            'treatment_type' => 'required|string',
            'conclusion' => 'required|string',
            'service_id' => 'required|exists:services,id',
        ]);

        // save pictures
        $picturePaths = [];
        if ($request->hasFile('pictures')) {
            foreach ($request->file('pictures') as $picture) {
                $path = $picture->store('public/reports');
                $paths = explode("/", $path);
                $picturePaths[] = end($paths);
            }
        }

        $serviceId = $request->service_id;

        // appointment ids for involved pets on the current date with the daycare service
        $appointmentIds = $this->getAppointmentIds($request->pets, $serviceId);
        if (count($appointmentIds) == 0) {
            return redirect()->back()->with([
                'status' => 'fail',
                'message' => 'No valid appointments found for the selected pets with the specified service.'
            ]);
        }
        // update the status of appointments
        Appointment::whereIn('id', $appointmentIds)->update(['status' => 'issue']);

        $incidentReport = new IncidentReport;
        $incidentReport->pet_ids = implode(',', $request->pets);
        $incidentReport->staff_ids = implode(',', $request->staffs);
        $incidentReport->appointment_ids = implode(',', $appointmentIds);
        $incidentReport->incident_description = $request->description;
        $incidentReport->pictures = !empty($picturePaths) ? implode(',', $picturePaths) : null;
        $incidentReport->injury_type = $request->injury_type;
        $incidentReport->injury_location = $request->injury_location;
        $incidentReport->needs_treatment = $request->needs_treatment;
        $incidentReport->is_emergency = $request->is_emergency;
        $incidentReport->contact_owner = $request->contact_owner;
        $incidentReport->owner_conversation_notes = $request->owner_conversation_notes;
        $incidentReport->treatment_type = $request->treatment_type;
        $incidentReport->vet_name = $request->vet_name;
        $incidentReport->vet_bill = $request->vet_bill;
        $incidentReport->vet_payment = $request->vet_payment;
        $incidentReport->vet_results = $request->vet_results;
        $incidentReport->conclusion = $request->conclusion;
        $incidentReport->service_id = $serviceId;

        $incidentReport->save();

        return redirect()->route('list-incident-reports', ['serviceId' => $serviceId])->with([
            'status' => 'success',
            'message' => 'Incident report created successfully!'
        ]);
    }

    public function editIncidentReport($id)
    {
        $incidentReport = IncidentReport::find($id);

        // prepare selected pets
        $selectedPetIds = array_filter(array_map('trim', explode(',', $incidentReport->pet_ids ?? '')));
        $selectedPets = $selectedPetIds ? PetProfile::whereIn('id', $selectedPetIds)->get() : collect();

        // prepare selected staffs
        $selectedStaffIds = array_filter(array_map('trim', explode(',', $incidentReport->staff_ids ?? '')));
        $selectedStaffs = $selectedStaffIds ? User::whereIn('id', $selectedStaffIds)->get() : collect();

        return view('reports.update-incident', compact('incidentReport', 'selectedPets', 'selectedStaffs'));
    }

    public function updateIncidentReport(Request $request)
    {
        $request->validate([
            'incident_report_id' => 'required|exists:incident_reports,id',
            'pets' => 'required|array|min:1',
            'staffs' => 'required|array|min:1',
            'injury_type' => 'required|string',
            'injury_location' => 'required|string',
            'needs_treatment' => 'required|string',
            'is_emergency' => 'required|string',
            'contact_owner' => 'required|string',
            'treatment_type' => 'required|string',
            'conclusion' => 'required|string',
        ]);

        $incidentReport = IncidentReport::find($request->incident_report_id);

        // collect current pictures submitted
        $currentPictures = [];
        if (!empty($request->current_pictures)) {
            $currentPictures = array_filter(array_map('trim', explode(',', $request->current_pictures)));
        }
        // Delete pictures that are NOT in the submitted list
        $existingPictures = [];
        if (!empty($incidentReport->pictures)) {
            $existingPictures = array_filter(array_map('trim', explode(',', $incidentReport->pictures)));
        }

        // pictures stored in DB but not present in the submitted current pictures
        $deletePictures = array_values(array_diff($existingPictures, $currentPictures));

        // optional: actually remove files from storage
        foreach ($deletePictures as $filename) {
            $filePath = 'public/reports/' . $filename;
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
        }

        // add new pictures
        if ($request->hasFile('pictures')) {
            foreach ($request->file('pictures') as $picture) {
                $path = $picture->store('public/reports');
                $paths = explode("/", $path);
                $currentPictures[] = end($paths);
            }
        }

        // update the status of previous appointments back to in_progress
        Appointment::whereIn('id', explode(',', $incidentReport->appointment_ids))->update(['status' => 'completed']);
        // appointment ids for involved pets on the current date with the daycare service
        $appointmentIds = $this->getAppointmentIds($request->pets, $incidentReport->service_id);
        if (count($appointmentIds) == 0) {
            return redirect()->back()->with([
                'status' => 'fail',
                'message' => 'No valid appointments found for the selected pets with the specified service.'
            ]);
        }
        // update the status of appointments
        Appointment::whereIn('id', $appointmentIds)->update(['status' => 'issue']);

        $incidentReport->pet_ids = implode(',', $request->pets);
        $incidentReport->staff_ids = implode(',', $request->staffs);
        $incidentReport->appointment_ids = implode(',', $appointmentIds);
        $incidentReport->incident_description = $request->description;
        $incidentReport->pictures = !empty($currentPictures) ? implode(',', $currentPictures) : null;
        $incidentReport->injury_type = $request->injury_type;
        $incidentReport->injury_location = $request->injury_location;
        $incidentReport->needs_treatment = $request->needs_treatment;
        $incidentReport->is_emergency = $request->is_emergency;
        $incidentReport->contact_owner = $request->contact_owner;
        $incidentReport->owner_conversation_notes = $request->owner_conversation_notes;
        $incidentReport->treatment_type = $request->treatment_type;
        $incidentReport->vet_name = $request->vet_name;
        $incidentReport->vet_bill = $request->vet_bill;
        $incidentReport->vet_payment = $request->vet_payment;
        $incidentReport->vet_results = $request->vet_results;
        $incidentReport->conclusion = $request->conclusion;
        $incidentReport->save();

        return redirect()->route('list-incident-reports', ['serviceId' => $incidentReport->service_id])->with([
            'status' => 'success',
            'message' => 'Incident report updated successfully!'
        ]);
    }

    private function getAppointmentIds($petIds, $serviceId)
    {
        // appointment ids for involved pets on the current date with the daycare service
        $appointmentIds = Appointment::whereIn('pet_id', $petIds)
            ->where('service_id', $serviceId)
            ->whereIn('status', ['checked_in', 'in_progress', 'completed'])
            ->pluck('id')
            ->toArray();

        return $appointmentIds;
    }

    public function deleteIncidentReport(Request $request)
    {
        $request->validate([
            'incident_report_id' => 'required|exists:incident_reports,id',
        ]);

        $incidentReport = IncidentReport::find($request->incident_report_id);
        $serviceId = $incidentReport->service_id;

        // appointment ids for involved pets on the current date with the daycare service
        $petIds = array_filter(array_map('trim', explode(',', $incidentReport->pet_ids ?? '')));
        $appointmentIds = $incidentReport->appointment_ids ? array_filter(array_map('trim', explode(',', $incidentReport->appointment_ids))) : [];
        // update the status of appointments
        Appointment::whereIn('id', $appointmentIds)->update(['status' => 'completed']);

        // optional: delete associated pictures from storage
        if (!empty($incidentReport->pictures)) {
            $pictures = array_filter(array_map('trim', explode(',', $incidentReport->pictures)));
            foreach ($pictures as $filename) {
                $filePath = 'public/reports/' . $filename;
                if (Storage::exists($filePath)) {
                    Storage::delete($filePath);
                }
            }
        }

        $incidentReport->delete();

        return redirect()->route('list-incident-reports', ['serviceId' => $serviceId])->with([
            'status' => 'success',
            'message' => 'Incident report deleted successfully!'
        ]);
    }
}
