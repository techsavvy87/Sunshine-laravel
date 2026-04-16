<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Kennel;

class KennelController extends Controller
{
    public function listKennels(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $search = $request->get('search');
        $type = $request->get('type');
        $status = $request->get('status');

        $query = Kennel::query()->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $kennels = $query->paginate($perPage)->withQueryString();

        return view('kennels.index', compact('kennels', 'search', 'type', 'status'));
    }

    public function addKennel()
    {
        return view('kennels.create');
    }

    public function editKennel($id)
    {
        $kennel = Kennel::findOrFail($id);

        return view('kennels.update', compact('kennel'));
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

    public function createKennel(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:dog,cat',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:In Service,Out of Service,Cleaning',
            'temp_file' => 'nullable|string',
        ]);

        $kennel = new Kennel();
        $kennel->name = $request->name;
        $kennel->description = $request->description;
        $kennel->type = $request->type;
        $kennel->capacity = $request->capacity;
        $kennel->status = $request->status;

        if ($request->filled('temp_file')) {
            $tempFile = $request->temp_file;
            $tempPath = 'temp/' . $tempFile;

            if (Storage::disk('local')->exists($tempPath)) {
                $fileContents = Storage::disk('local')->get($tempPath);

                if ($fileContents !== null) {
                    $permanentPath = 'kennels/' . $tempFile;
                    Storage::disk('public')->put($permanentPath, $fileContents);
                    Storage::disk('local')->delete($tempPath);
                }
            }

            $kennel->img = $tempFile;
        }

        $kennel->save();

        return redirect()->route('kennels')->with([
            'status' => 'success',
            'message' => 'Kennel added successfully!'
        ]);
    }

    public function updateKennel(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:kennels,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:dog,cat',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:In Service,Out of Service,Cleaning',
            'img_action' => 'required|in:keep,change,delete',
            'temp_file' => 'nullable|string',
            'current_img' => 'nullable|string',
        ]);

        $kennel = Kennel::findOrFail($request->id);
        $kennel->name = $request->name;
        $kennel->description = $request->description;
        $kennel->type = $request->type;
        $kennel->capacity = $request->capacity;
        $kennel->status = $request->status;

        switch ($request->img_action) {
            case 'change':
                if ($kennel->img && Storage::disk('public')->exists('kennels/' . $kennel->img)) {
                    Storage::disk('public')->delete('kennels/' . $kennel->img);
                }

                if ($request->filled('temp_file')) {
                    $tempFile = $request->temp_file;
                    $tempPath = 'temp/' . $tempFile;

                    if (Storage::disk('local')->exists($tempPath)) {
                        $fileContents = Storage::disk('local')->get($tempPath);

                        if ($fileContents !== null) {
                            $permanentPath = 'kennels/' . $tempFile;
                            Storage::disk('public')->put($permanentPath, $fileContents);
                            Storage::disk('local')->delete($tempPath);
                            $kennel->img = $tempFile;
                        }
                    }
                }
                break;

            case 'delete':
                if ($kennel->img && Storage::disk('public')->exists('kennels/' . $kennel->img)) {
                    Storage::disk('public')->delete('kennels/' . $kennel->img);
                }
                $kennel->img = null;
                break;

            case 'keep':
            default:
                break;
        }

        $kennel->save();

        return redirect()->route('kennels')->with([
            'status' => 'success',
            'message' => 'Kennel updated successfully!'
        ]);
    }

    public function deleteKennel(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:kennels,id',
        ]);

        $kennel = Kennel::findOrFail($request->id);

        if ($kennel->img && Storage::disk('public')->exists('kennels/' . $kennel->img)) {
            Storage::disk('public')->delete('kennels/' . $kennel->img);
        }

        $kennel->delete();

        return redirect()->route('kennels')->with([
            'status' => 'success',
            'message' => 'Kennel deleted successfully!'
        ]);
    }
}
