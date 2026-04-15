<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function list(Request $request)
    {
        $search = $request->get('search');
        if (!empty($search)) {
            $permissions = Permission::where('title', 'like', '%' . $search . '%')->orderBy('title')->get();
        } else {
            $permissions = Permission::orderBy('title')->get();
        }
        return view('permissions.index', compact('permissions', 'search'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        // validate if permission already exists
        if (Permission::where('title', $request->title)->exists()) {
            return redirect()->route('permissions')->with([
                'status' => 'fail',
                'message' => 'Permission already exists.'
            ]);
        }

        $permission = new Permission;
        $permission->title = $request->title;
        $permission->save();

        return redirect()->route('permissions')->with([
            'status' => 'success',
            'message' => 'Permission created successfully!'
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:permissions,id',
            'title' => 'required|string',
        ]);

        // validate if permission already exists
        if (Permission::where('title', $request->title)->exists()) {
            return redirect()->route('permissions')->with([
                'status' => 'fail',
                'message' => 'Permission already exists.'
            ]);
        }

        $permission = Permission::find($request->id);
        $permission->title = $request->title;
        $permission->save();

        return redirect()->route('permissions')->with([
            'status' => 'success',
            'message' => 'Permission updated successfully!'
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:permissions,id',
        ]);

        $permission = Permission::find($request->id)->delete();

        return redirect()->route('permissions')->with([
            'status' => 'success',
            'message' => 'Permission deleted successfully!'
        ]);
    }
}
