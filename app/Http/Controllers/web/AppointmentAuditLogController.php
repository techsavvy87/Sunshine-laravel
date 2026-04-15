<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\AppointmentAuditLog;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;
        $description = $request->get('description', '');
        $petOwner = $request->get('pet_owner', '');
        $serviceId = $request->get('service');
        $staffId = $request->get('staff');
        $datetimes = $request->get('datetimes');

        $query = AppointmentAuditLog::query()->orderByDesc('created_at');

        if ($description !== null && $description !== '') {
            $query->where('description', 'like', '%' . trim($description) . '%');
        }

        if ($petOwner !== null && $petOwner !== '') {
            $search = '%' . trim($petOwner) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('pet_name', 'like', $search)
                    ->orWhere('owner_name', 'like', $search);
            });
        }

        if ($serviceId) {
            $service = Service::with('category')->find($serviceId);
            if ($service) {
                $typeMatch = $service->category ? $service->category->name : $service->name;
                $query->where('type', $typeMatch);
            }
        }

        if ($staffId) {
            $staff = User::with('profile')->find($staffId);
            if ($staff) {
                $staffName = $staff->profile
                    ? trim(($staff->profile->first_name ?? '') . ' ' . ($staff->profile->last_name ?? ''))
                    : ($staff->name ?? $staff->email);
                if (!empty($staffName)) {
                    $query->where('employee', 'like', '%' . $staffName . '%');
                }
            }
        }

        if ($datetimes) {
            [$start, $end] = explode(' - ', $datetimes);

            $startDateTime = Carbon::createFromFormat('m/d/y h:i A', trim($start))->startOfMinute();
            $endDateTime = Carbon::createFromFormat('m/d/y h:i A', trim($end))->endOfMinute();

            $query->whereBetween('created_at', [$startDateTime, $endDateTime]);
        }

        $logs = $query->paginate($perPage)->withQueryString();

        $services = Service::where('status', 'active')->where('level', 'primary')->get();
        $staffs = User::whereHas('roles', function ($q) {
            $q->whereNot('title', 'customer');
        })->get();

        return view('audit-log.index', compact(
            'logs',
            'perPage',
            'description',
            'petOwner',
            'serviceId',
            'staffId',
            'datetimes',
            'services',
            'staffs'
        ));
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:appointment_audit_logs,id',
        ]);

        AppointmentAuditLog::findOrFail($request->id)->delete();

        return redirect()->back()->with([
            'message' => 'Audit record deleted successfully.',
            'status' => 'success',
        ]);
    }
}
