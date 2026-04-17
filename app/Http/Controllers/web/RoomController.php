<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Room;
use App\Models\Kennel;
use App\Models\Appointment;

class RoomController extends Controller
{
    public function listRooms(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $search = $request->get('search');

        $query = Room::query()->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhere('kennel_ids', 'like', '%' . $search . '%');
            });
        }

        $rooms = $query->paginate($perPage)->withQueryString();
        $kennels = Kennel::orderBy('name')->get();
        $kennelLookup = $kennels->keyBy('id');

        $activeBoardingAppointments = Appointment::with(['pet', 'catRoom'])
            ->whereIn('status', ['checked_in', 'in_progress'])
            ->whereHas('service.category', function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%boarding%']);
            })
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $activeBoardingAppointmentsByKennel = $activeBoardingAppointments
            ->whereNotNull('kennel_id')
            ->groupBy('kennel_id')
            ->map(function ($appointments) {
                return $appointments->flatMap(function ($appointment) {
                    return $appointment->family_pets;
                })->filter()->unique('id')->values();
            });

        $activeBoardingAppointmentsByRoom = $activeBoardingAppointments
            ->whereNotNull('cat_room_id')
            ->groupBy('cat_room_id')
            ->map(function ($appointments) {
                return $appointments->flatMap(function ($appointment) {
                    return $appointment->family_pets;
                })->filter()->unique('id')->values();
            });

        $rooms->getCollection()->transform(function ($room) use ($kennelLookup, $activeBoardingAppointmentsByKennel, $activeBoardingAppointmentsByRoom) {
            $room->assigned_kennels = collect($room->kennel_id_array)
                ->map(fn ($id) => $kennelLookup->get((int) $id))
                ->filter()
                ->values();

            $room->assigned_kennel_names = $room->assigned_kennels->pluck('name')->implode(', ');
            $room->current_room_pets = $activeBoardingAppointmentsByRoom->get((int) $room->id, collect());
            $room->kennel_pet_assignments = $room->assigned_kennels->map(function ($kennel) use ($activeBoardingAppointmentsByKennel) {
                $pets = $activeBoardingAppointmentsByKennel->get((int) $kennel->id, collect());

                return (object) [
                    'kennel' => $kennel,
                    'pets' => $pets,
                ];
            })->values();

            return $room;
        });

        return view('rooms.index', compact('rooms', 'kennels', 'search'));
    }

    public function addRoom()
    {
        // Get all assigned kennel_ids from rooms
        $assignedIds = Room::whereNotNull('kennel_ids')
            ->pluck('kennel_ids')
            ->flatMap(function ($ids) {
                return explode(',', $ids);
            })
            ->filter()
            ->unique()
            ->toArray();

        // Get only unassigned kennels
        $kennels = Kennel::whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get();

        return view('rooms.create', compact('kennels'));
    }

    public function editRoom($id)
    {
        $room = Room::findOrFail($id);

        // Get kennel assignments from other rooms only so current room selections remain visible.
        $assignedIds = Room::where('id', '!=', $room->id)
            ->whereNotNull('kennel_ids')
            ->pluck('kennel_ids')
            ->flatMap(function ($ids) {
                return explode(',', $ids);
            })
            ->filter()
            ->unique()
            ->toArray();

        $kennels = Kennel::whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get();

        return view('rooms.update', compact('room', 'kennels'));
    }

    public function processFileUpload(Request $request)
    {
        try {
            $request->validate([
                'img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $file = $request->file('img');
            $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('temp', $fileName, 'local');

            return response()->json([
                'temp_file' => $fileName,
                'original_name' => $file->getClientOriginalName(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'File upload failed: ' . $e->getMessage()
            ], 422);
        }
    }

    public function revertFileUpload(Request $request)
    {
        try {
            $tempFile = $request->getContent();

            if ($tempFile && Storage::disk('local')->exists('temp/' . $tempFile)) {
                Storage::disk('local')->delete('temp/' . $tempFile);
            }

            return response()->json(['message' => 'File reverted successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'File deletion failed: ' . $e->getMessage()
            ], 422);
        }
    }

    public function createRoom(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:dog,cat,other',
            'status' => 'required|in:Available,Blocked,Maintenance',
            'kennel_ids' => 'nullable|array',
            'kennel_ids.*' => 'integer|exists:kennels,id',
            'temp_file' => 'nullable|string',
        ]);

        $normalizedName = trim((string) $request->name);

        if (Room::whereRaw('LOWER(name) = ?', [strtolower($normalizedName)])->exists()) {
            return redirect()->back()->withInput()->with([
                'status' => 'fail',
                'message' => 'Room name already exists.'
            ]);
        }

        $room = new Room();
        $room->name = $normalizedName;
        $room->description = $request->description;
        $room->type = $request->type;
        $room->status = $request->status;
        $room->kennel_ids = $this->normalizeKennelIds($request->input('kennel_ids', []));

        if ($request->filled('temp_file')) {
            $tempFile = $request->temp_file;
            $tempPath = 'temp/' . $tempFile;

            if (Storage::disk('local')->exists($tempPath)) {
                $fileContents = Storage::disk('local')->get($tempPath);

                if ($fileContents !== null) {
                    $permanentPath = 'rooms/' . $tempFile;
                    Storage::disk('public')->put($permanentPath, $fileContents);
                    Storage::disk('local')->delete($tempPath);
                }
            }

            $room->img = $tempFile;
        }

        $room->save();

        return redirect()->route('rooms')->with([
            'status' => 'success',
            'message' => 'Room added successfully!'
        ]);
    }

    public function updateRoom(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:rooms,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:dog,cat,other',
            'status' => 'required|in:Available,Blocked,Maintenance',
            'kennel_ids' => 'nullable|array',
            'kennel_ids.*' => 'integer|exists:kennels,id',
            'img_action' => 'required|in:keep,change,delete',
            'temp_file' => 'nullable|string',
            'current_img' => 'nullable|string',
        ]);

        $normalizedName = trim((string) $request->name);

        if (Room::whereRaw('LOWER(name) = ?', [strtolower($normalizedName)])
            ->where('id', '!=', $request->id)
            ->exists()) {
            return redirect()->back()->withInput()->with([
                'status' => 'fail',
                'message' => 'Room name already exists.'
            ]);
        }

        $room = Room::findOrFail($request->id);
        $room->name = $normalizedName;
        $room->description = $request->description;
        $room->type = $request->type;
        $room->status = $request->status;
        $room->kennel_ids = $this->normalizeKennelIds($request->input('kennel_ids', []));

        switch ($request->img_action) {
            case 'change':
                if ($room->img && Storage::disk('public')->exists('rooms/' . $room->img)) {
                    Storage::disk('public')->delete('rooms/' . $room->img);
                }

                if ($request->filled('temp_file')) {
                    $tempFile = $request->temp_file;
                    $tempPath = 'temp/' . $tempFile;

                    if (Storage::disk('local')->exists($tempPath)) {
                        $fileContents = Storage::disk('local')->get($tempPath);

                        if ($fileContents !== null) {
                            $permanentPath = 'rooms/' . $tempFile;
                            Storage::disk('public')->put($permanentPath, $fileContents);
                            Storage::disk('local')->delete($tempPath);
                            $room->img = $tempFile;
                        }
                    }
                }
                break;

            case 'delete':
                if ($room->img && Storage::disk('public')->exists('rooms/' . $room->img)) {
                    Storage::disk('public')->delete('rooms/' . $room->img);
                }
                $room->img = null;
                break;

            case 'keep':
            default:
                break;
        }

        $room->save();

        return redirect()->route('rooms')->with([
            'status' => 'success',
            'message' => 'Room updated successfully!'
        ]);
    }

    public function deleteRoom(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:rooms,id',
        ]);

        $room = Room::findOrFail($request->id);

        if ($room->img && Storage::disk('public')->exists('rooms/' . $room->img)) {
            Storage::disk('public')->delete('rooms/' . $room->img);
        }

        $room->delete();

        return redirect()->route('rooms')->with([
            'status' => 'success',
            'message' => 'Room deleted successfully!'
        ]);
    }

    private function normalizeKennelIds($kennelIds): ?string
    {
        $ids = collect(is_array($kennelIds) ? $kennelIds : explode(',', (string) $kennelIds))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return null;
        }

        $existingIds = Kennel::whereIn('id', $ids)->pluck('id')->all();

        return empty($existingIds) ? null : implode(',', $existingIds);
    }
}
