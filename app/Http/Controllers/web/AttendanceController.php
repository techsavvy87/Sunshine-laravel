<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeAttendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function listAttendance(Request $request)
    {
        $dateStr = $request->get('date', now()->format('Y-m-d'));
        $date = Carbon::parse($dateStr);

        $staffs = User::whereHas('roles', function ($query) {
            $query->whereNot('title', 'customer');
        })->with('profile')->orderBy('name')->get();

        $records = [];
        foreach ($staffs as $staff) {
            $att = EmployeeAttendance::firstOrCreate(
                ['user_id' => $staff->id, 'date' => $date->format('Y-m-d')],
                ['present' => true, 'injury_sickness' => false]
            );
            $att->setRelation('user', $staff);
            $records[] = $att;
        }

        return view('attendance.index', [
            'records' => $records,
            'staffs' => $staffs,
            'date' => $date,
        ]);
    }

    public function updateAttendance(Request $request)
    {
        $request->validate([
            'rows' => 'required|array',
            'rows.*.id' => 'required|exists:employee_attendance,id',
            'rows.*.present' => 'nullable|boolean',
            'rows.*.injury_sickness' => 'nullable|boolean',
            'rows.*.notes' => 'nullable|string|max:65535',
        ]);

        foreach ($request->rows as $row) {
            $att = EmployeeAttendance::find($row['id']);
            if ($att) {
                $att->present = isset($row['present']) ? (bool) $row['present'] : true;
                $att->injury_sickness = isset($row['injury_sickness']) ? (bool) $row['injury_sickness'] : false;
                $att->notes = $row['notes'] ?? null;
                $att->save();
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Attendance saved.']);
        }
        return redirect()->back()->with('message', 'Attendance saved.');
    }
}
