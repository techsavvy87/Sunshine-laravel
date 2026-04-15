<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\InventoryAttribute;

class InventoryController extends Controller
{
    public function listCategories(Request $request)
    {
        $inventoryCategories = InventoryCategory::with('parent')->get();
        $parentCategories = $inventoryCategories->whereNull('parent_id');

        return view('inventories.category', compact('inventoryCategories', 'parentCategories'));
    }

    public function createCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:inventory_categories,id',
        ]);

        $inventoryCategory = new InventoryCategory;
        $inventoryCategory->name = $request->name;
        $inventoryCategory->parent_id = $request->parent_id;
        $inventoryCategory->save();

        return response()->json([
            'message' => 'Category created successfully.',
            'result' => InventoryCategory::with('parent')->get(),
        ]);
    }

    public function updateCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:inventory_categories,id',
        ]);

        $inventoryCategory = InventoryCategory::findOrFail($request->category_id);
        $inventoryCategory->name = $request->name;
        $inventoryCategory->parent_id = $request->parent_id;
        $inventoryCategory->save();

        return response()->json([
            'message' => 'Category updated successfully.',
            'result' => InventoryCategory::with('parent')->get(),
        ]);
    }

    public function deleteCategory(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:inventory_categories,id'
        ]);

        $category = InventoryCategory::findOrFail($request->id);
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory category deleted successfully!',
            'result' => InventoryCategory::with('parent')->get()
        ]);
    }

    public function getParentCategories()
    {
        $parentCategories = InventoryCategory::whereNull('parent_id')->get();
        return response()->json($parentCategories);
    }

    public function listItems(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $search = $request->get('search');
        if (!empty($search)) {
            $inventoryItems = InventoryItem::where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })->with('category')->paginate($perPage);
        } else {
            $inventoryItems = InventoryItem::with('category')->paginate($perPage);
        }

        return view('inventories.index', compact('search', 'inventoryItems'));
    }

    public function addItem(Request $request)
    {
        $categories = InventoryCategory::all();
        return view('inventories.create', compact('categories'));
    }

    public function createItem(Request $request)
    {
        $request->validate([
            'vendor' => 'required|string',
            'brand' => 'required|string',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|unique:inventory_items,sku',
            'cost' => 'required|numeric|min:0',
            'wholesale_cost' => 'required|numeric|min:0',
            'category' => 'required|exists:inventory_categories,id',
            'par' => 'integer|min:0',
        ]);

        // save inventory item
        $inventoryItem = new InventoryItem;
        $inventoryItem->vendor = $request->vendor;
        $inventoryItem->brand = $request->brand;
        $inventoryItem->description = $request->description;
        $inventoryItem->sku = $request->sku;
        $inventoryItem->cost = $request->cost;
        $inventoryItem->wholesale_cost = $request->wholesale_cost;
        $inventoryItem->category_id = $request->category;
        $inventoryItem->par = $request->par;
        $inventoryItem->is_hidden = $request->boolean('is_hidden') ?? false;
        $inventoryItem->is_service = $request->boolean('is_service') ?? false;
        $inventoryItem->save();

        // save inventory attributes
        $attributes = json_decode($request->attrs);
        foreach($attributes as $attribute)
        {
            if (!empty($attribute->name) || !empty($attribute->value))
            {
                $inventoryAttribute = new InventoryAttribute;
                $inventoryAttribute->item_id = $inventoryItem->id;
                $inventoryAttribute->attribute_name = $attribute->name;
                $inventoryAttribute->attribute_value = $attribute->value;
                $inventoryAttribute->save();
            }
        }

        return redirect()->route('inventory-items')->with([
            'status' => 'success',
            'message' => 'Inventory item created successfully.'
        ]);
    }

    public function editItem($id)
    {
        $inventoryItem = InventoryItem::findOrFail($id);
        $categories = InventoryCategory::all();
        return view('inventories.update', compact('inventoryItem', 'categories'));
    }

    public function updateItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'vendor' => 'required|string',
            'brand' => 'required|string',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|unique:inventory_items,sku,' . $request->item_id,
            'cost' => 'required|numeric|min:0',
            'wholesale_cost' => 'required|numeric|min:0',
            'category' => 'required|exists:inventory_categories,id',
            'par' => 'integer|min:0',
        ]);

        $inventoryItem = InventoryItem::findOrFail($request->item_id);
        $inventoryItem->vendor = $request->vendor;
        $inventoryItem->brand = $request->brand;
        $inventoryItem->description = $request->description;
        $inventoryItem->sku = $request->sku;
        $inventoryItem->cost = $request->cost;
        $inventoryItem->wholesale_cost = $request->wholesale_cost;
        $inventoryItem->category_id = $request->category;
        $inventoryItem->par = $request->par;
        $inventoryItem->is_hidden = $request->boolean('is_hidden') ?? false;
        $inventoryItem->is_service = $request->boolean('is_service') ?? false;
        $inventoryItem->save();

        // save inventory attributes
        $attributes = json_decode($request->attrs);

        // Collect IDs of submitted attributes
        $submittedIds = [];
        foreach ($attributes as $attribute) {
            if (!empty($attribute->id)) {
                $submittedIds[] = $attribute->id;
            }
        }

        // Delete attributes that are NOT in the submitted list
        InventoryAttribute::where('item_id', $inventoryItem->id)
            ->whereNotIn('id', $submittedIds)
            ->delete();

        // Update or create attributes
        foreach ($attributes as $attribute) {
            if (!empty($attribute->id)) {
                // Update existing attribute
                $inventoryAttribute = InventoryAttribute::find($attribute->id);
                if ($inventoryAttribute) {
                    $inventoryAttribute->attribute_name = $attribute->name;
                    $inventoryAttribute->attribute_value = $attribute->value;
                    $inventoryAttribute->save();
                }
            } else {
                // Create new attribute
                if (!empty($attribute->name) || !empty($attribute->value)) {
                    $inventoryAttribute = new InventoryAttribute;
                    $inventoryAttribute->item_id = $inventoryItem->id;
                    $inventoryAttribute->attribute_name = $attribute->name;
                    $inventoryAttribute->attribute_value = $attribute->value;
                    $inventoryAttribute->save();
                }
            }
        }

        return redirect()->route('inventory-items')->with([
            'status' => 'success',
            'message' => 'Inventory item updated successfully.'
        ]);
    }

    public function deleteItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:inventory_items,id'
        ]);

        $inventoryItem = InventoryItem::findOrFail($request->item_id);
        $inventoryItem->delete();

        return redirect()->route('inventory-items')->with([
            'status' => 'success',
            'message' => 'Inventory item deleted successfully.'
        ]);
    }

    public function detailItem($id)
    {
        $inventoryItem = InventoryItem::findOrFail($id);
        // get the list of inventory transactions
        $transactions = InventoryTransaction::where('item_id', $id)->get();
        return view('inventories.detail', compact('inventoryItem', 'transactions'));
    }

    public function createTransaction(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
            'change_type' => 'in:increase,decrease',
            'reason' => 'nullable|string',
        ]);

        $transaction = new InventoryTransaction;
        $transaction->item_id = $request->item_id;
        $transaction->change_type = $request->change_type;
        $transaction->quantity_change = $request->quantity;
        $transaction->reason = $request->reason;
        $transaction->user_id = auth()->id();
        $transaction->save();

        // update the quantity of the inventory item
        $inventoryItem = InventoryItem::findOrFail($request->item_id);
        if ($request->change_type === 'increase') {
            $inventoryItem->quantity += $request->quantity;
        } else {
            $inventoryItem->quantity -= $request->quantity;
        }
        $inventoryItem->save();

        return redirect()->back()->with([
            'status' => 'success',
            'message' => 'Inventory transaction created successfully.'
        ]);
    }

    public function getInventoryItems(Request $request)
    {
        $search = $request->get('q', '');

        $inventoryItems = InventoryItem::where('brand', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->limit(6)
            ->get();

        return response()->json($inventoryItems);
    }
}
