<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PetBehavior;
use App\Models\BehaviorIcon;

class PetBehaviorController extends Controller
{
    public function listBehaviors(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $search = $request->get('search');

        $icons = BehaviorIcon::all();

        $behaviors = PetBehavior::with('icon')
            ->when($search, function ($query) use ($search) {
                $query->where('description', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('pet-behaviors.index', compact('behaviors', 'icons', 'search'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'icon_id' => 'required|exists:behavior_icons,id',
            'description' => 'required|string',
        ]);

        PetBehavior::create([
            'icon_id' => $request->icon_id,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pet behavior created successfully!',
            'result' => PetBehavior::with('icon')->get(),
        ], 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            'behavior_icon_id' => 'required|exists:pet_behaviors,id',
            'icon_id' => 'required|exists:behavior_icons,id',
            'description' => 'required|string',
        ]);

        $behavior = PetBehavior::findOrFail($request->behavior_icon_id);
        $behavior->icon_id = $request->icon_id;
        $behavior->description = $request->description;
        $behavior->save();

        return response()->json([
            'status' => true,
            'message' => 'Pet behavior updated successfully!',
            'result' => PetBehavior::with('icon')->get(),
        ], 200);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:pet_behaviors,id',
        ]);

        $behavior = PetBehavior::findOrFail($request->id);
        $behavior->delete();

        return response()->json([
            'status' => true,
            'message' => 'Pet behavior deleted successfully!',
            'result' => PetBehavior::with('icon')->get(),
        ], 200);
    }
}
