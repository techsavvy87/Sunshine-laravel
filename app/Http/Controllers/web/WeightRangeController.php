<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WeightRange;

class WeightRangeController extends Controller
{
    public function list()
    {
        $weightRanges = WeightRange::all();
        return view('weightranges.index', compact('weightRanges'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'min_weight' => 'required|numeric',
            'max_weight' => 'required|numeric'
        ]);

        $min = floatval($request->min_weight);
        $max = floatval($request->max_weight);

        if ($min >= $max) {
            return response()->json([
                'status' => false,
                'message' => 'Min Weight must be less than Max Weight'
            ], 422);
        }

        // check overlap: any existing range that intersects [min, max]
        $overlapExists = WeightRange::where(function ($q) use ($min, $max) {
            // existing min inside new range
            $q->orWhere(function ($q1) use ($min, $max) {
                $q1->where('min_weight', '>', $min)->where('min_weight', '<', $max);
            })->orWhere(function ($q2) use ($min, $max) {
                $q2->where('max_weight', '>', $min)->where('max_weight', '<', $max);
            })->orWhere(function ($q3) use ($min, $max) {
                $q3->where('min_weight', '<', $min)->where('max_weight', '>', $max);
            })->orWhere(function ($q4) use ($min, $max) {
                $q4->where('min_weight', '>', $min)->where('max_weight', '<', $max);
            });
        })->exists();

        if ($overlapExists) {
            return response()->json([
                'status' => false,
                'message' => 'Weight range overlaps with an existing range'
            ], 422);
        }

        $weightRange = new WeightRange;
        $weightRange->name = $request->name;
        $weightRange->min_weight = $min;
        $weightRange->max_weight = $max;
        $weightRange->save();

        return response()->json([
            'status' => true,
            'message' => 'Weight range created successfully!',
            'result' => WeightRange::all()
        ], 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            'weight_range_id' => 'required|exists:weight_ranges,id',
            'name' => 'required|string|max:255',
            'min_weight' => 'required|numeric',
            'max_weight' => 'required|numeric'
        ]);

        $id = $request->weight_range_id;
        $min = floatval($request->min_weight);
        $max = floatval($request->max_weight);

        if ($min >= $max) {
            return response()->json([
                'status' => false,
                'message' => 'Min Weight must be less than Max Weight'
            ], 422);
        }

        // check overlap excluding the current record
        $overlapExists = WeightRange::where('id', '!=', $id)
            ->where(function ($q) use ($min, $max) {
                // existing min inside new range
                $q->orWhere(function ($q1) use ($min, $max) {
                    $q1->where('min_weight', '>', $min)->where('min_weight', '<', $max);
                })->orWhere(function ($q2) use ($min, $max) {
                    $q2->where('max_weight', '>', $min)->where('max_weight', '<', $max);
                })->orWhere(function ($q3) use ($min, $max) {
                    $q3->where('min_weight', '<', $min)->where('max_weight', '>', $max);
                })->orWhere(function ($q4) use ($min, $max) {
                    $q4->where('min_weight', '>', $min)->where('max_weight', '<', $max);
                });
            })->exists();

        if ($overlapExists) {
            return response()->json([
                'status' => false,
                'message' => 'Weight range overlaps with an existing range'
            ], 422);
        }

        $weightRange = WeightRange::findOrFail($id);
        $weightRange->name = $request->name;
        $weightRange->min_weight = $min;
        $weightRange->max_weight = $max;
        $weightRange->save();

        return response()->json([
            'status' => true,
            'message' => 'Weight range updated successfully!',
            'result' => WeightRange::all()
        ], 200);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:weight_ranges,id'
        ]);

        $weightRange = WeightRange::findOrFail($request->id);
        $weightRange->delete();

        return response()->json([
            'status' => true,
            'message' => 'Weight range deleted successfully!',
            'result' => WeightRange::all()
        ], 200);
    }
}

