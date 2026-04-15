<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceIssue;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    public function listMaintenance(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $date = $request->get('date');
        $search = $request->get('search');

        $query = MaintenanceIssue::query()->orderBy('date', 'desc')->orderBy('id', 'desc');

        if ($date) {
            $query->whereDate('date', Carbon::parse($date));
        }
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $issues = $query->paginate($perPage);

        return view('maintenance.index', compact('issues', 'date', 'search'));
    }

    public function createMaintenance(Request $request)
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'description' => 'required|string|max:65535',
            'date' => 'required|date',
        ]);
        MaintenanceIssue::create([
            'type' => $request->type,
            'description' => $request->description,
            'date' => $request->date,
        ]);
        return redirect()->route('maintenance')->with('message', 'Maintenance issue added.');
    }

    public function updateMaintenance(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:maintenance_issues,id',
            'type' => 'required|string|max:255',
            'description' => 'required|string|max:65535',
            'date' => 'required|date',
        ]);
        $issue = MaintenanceIssue::findOrFail($request->id);
        $issue->update($request->only('type', 'description', 'date'));
        return redirect()->route('maintenance')->with('message', 'Maintenance issue updated.');
    }

    public function deleteMaintenance(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:maintenance_issues,id',
        ]);
        MaintenanceIssue::findOrFail($request->id)->delete();
        return redirect()->route('maintenance')->with('message', 'Maintenance issue deleted.');
    }
}
