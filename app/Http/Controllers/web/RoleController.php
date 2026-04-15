<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
    public function list(Request $request)
    {
        $search = $request->get('search');
        if (!empty($search)) {
            $roles = Role::where('title', 'like', '%' . $search . '%')->orderBy('title')->get();
        } else {
            $roles = Role::where('title', '!=', 'customer')->orderBy('title')->get();
        }
        $permissions = Permission::orderBy('title')->get();

        return view('roles.index', compact('search', 'roles', 'permissions'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        // validate if title already exists
        if (Role::where('title', $request->title)->exists()) {
            return redirect()->route('roles')->with([
                'status' => 'fail',
                'message' => 'Role already exists.'
            ]);
        }

        $role = new Role;
        $role->title = $request->title;
        $role->description = $request->description ?? '';
        $role->save();

        return redirect()->route('roles')->with([
            'status' => 'success',
            'message' => 'Role created successfully!'
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:roles,id',
            'title' => 'required|string',
        ]);

        // validate if role already exists
        if (Role::where('title', $request->title)->exists()) {
            return redirect()->route('roles')->with([
                'status' => 'fail',
                'message' => 'Role already exists.'
            ]);
        }

        $role = Role::find($request->id);
        $role->title = $request->title;
        $role->description = $request->description ?? '';
        $role->save();

        return redirect()->route('roles')->with([
            'status' => 'success',
            'message' => 'Role updated successfully!'
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:roles,id',
        ]);

        $role = Role::find($request->id)->delete();

        return redirect()->route('roles')->with([
            'status' => 'success',
            'message' => 'Role deleted successfully!'
        ]);
    }

    public function createRolePermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission' => 'required|exists:permissions,id',
        ]);

        $role = Role::find($request->role_id);
        $role->permissions()->attach($request->permission, [
            'can_create' => $request->boolean('can_create') ?? false,
            'can_read' => $request->boolean('can_read') ?? false,
            'can_update' => $request->boolean('can_update') ?? false,
            'can_delete' => $request->boolean('can_delete') ?? false,
        ]);

        return redirect()->route('roles')->with([
            'status' => 'success',
            'message' => 'Permissions added to role successfully!'
        ]);
    }

    public function updateRolePermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission' => 'required|exists:permissions,id',
        ]);

        $role = Role::find($request->role_id);
        $role->permissions()->updateExistingPivot($request->permission, [
            'can_create' => $request->boolean('can_create') ?? false,
            'can_read' => $request->boolean('can_read') ?? false,
            'can_update' => $request->boolean('can_update') ?? false,
            'can_delete' => $request->boolean('can_delete') ?? false,
        ]);

        return redirect()->route('roles')->with([
            'status' => 'success',
            'message' => 'Permissions updated for role successfully!'
        ]);
    }

    public function deleteRolePermission(Request $request)
    {
        $request->validate([
            'remove_role_id' => 'required|exists:roles,id',
            'remove_permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::find($request->remove_role_id);
        $role->permissions()->detach($request->remove_permission_id);

        return redirect()->route('roles')->with([
            'status' => 'success',
            'message' => 'Permission removed from role successfully!'
        ]);
    }
}
