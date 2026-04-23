<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use App\Models\User;
use App\Models\AdditionalOwner;
use App\Models\Profile;
use App\Models\Role;

class CustomerController extends Controller
{
    public function listCustomers(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $search = $request->get('search');
        if (!empty($search)) {
            $customers = User::where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('phone_number_1', 'like', "%{$search}%")
                            ->orWhere('phone_number_2', 'like', "%{$search}%")
                            ->orWhere('gender', 'like', "%{$search}%");
                    });
            })->whereHas('roles', function ($query) {
                $query->where('title', 'customer');
            })->orderBy('created_at', 'desc')->paginate($perPage);
        } else {
            $customers = User::whereHas('roles', function ($query) {
                            $query->where('title', 'customer');
                        })->orderBy('created_at', 'desc')->paginate($perPage);
        }
        return view('customers.index', compact('search', 'customers'));
    }

    public function addCustomer()
    {
        return view('customers.create');
    }

    public function processFileUpload(Request $request)
    {
        try {
            $request->validate([
                'avatar_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            ]);

            // Handle file upload logic here
            $file = $request->file('avatar_img');

            // generate a unique file name
            $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();

            // Store in temporary directory
            $path = $file->storeAs('temp', $fileName, 'local');

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

    public function createCustomer(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone_number_1' => 'required|string',
            'temp_file' => 'nullable|string',
        ]);

        // Create account
        $user = new User();
        $user->name = $request->username;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);

        $isEmailVerified = $request->boolean('email_verified') ?? false;
        if ($isEmailVerified) {
            $user->email_verified_at = Carbon::now();
        }
        $user->status = $request->boolean('status') ?? false;
        $user->block_reservations = $request->boolean('block_reservations') ?? false;
        $user->block_messages = $request->boolean('block_messages') ?? false;
        $user->save();

        // Create profile
        $profile = new Profile();
        $profile->user_id = $user->id;
        $profile->first_name = $request->first_name;
        $profile->last_name = $request->last_name;
        $profile->phone_number_1 = $request->phone_number_1;
        $profile->phone_number_2 = $request->phone_number_2;
        $profile->gender = $request->gender;
        $profile->address = $request->street_address;
        $profile->city = $request->city;
        $profile->state = $request->state;
        $profile->zip_code = $request->zip_code;
        $profile->emergency_contact_info = $request->emergency_contact_info;
        $profile->home_number = $request->home_number;
        $profile->work_number = $request->work_number;

        if ($request->filled('temp_file')) {
            $tempFile = $request->temp_file;
            $tempPath = 'temp/' . $tempFile;

            if (Storage::disk('local')->exists($tempPath)) {
                // Get file contents and ensure it's not null
                $fileContents = Storage::disk('local')->get($tempPath);

                if ($fileContents !== null) {
                    // Move the file to a permanent location
                    $permanentPath = 'profiles/' . $tempFile;
                    Storage::disk('public')->put($permanentPath, $fileContents);
                    Storage::disk('local')->delete($tempPath); // Delete the temporary file
                }
            }
            $profile->avatar_img = $tempFile; // Store the file name in the profile
        }

        $profile->save();

        // Assign customer role
        $customerRole = Role::where('title', 'customer')->first();
        if ($customerRole) {
            $user->roles()->attach($customerRole);
        }

        // Create additional owners
        $owners = json_decode($request->owners);
        foreach($owners as $owner)
        {
            if (!empty($owner->name) && !empty($owner->phone))
            {
                $additionalOwner = new AdditionalOwner;
                $additionalOwner->user_id = $user->id;
                $additionalOwner->full_name = $owner->name;
                $additionalOwner->phone_number = $owner->phone;
                $additionalOwner->save();
            }
        }

        // Optionally, redirect or return a response
        return redirect()->route('customers')->with([
            'status' => 'success',
            'message' => 'Customer created successfully!'
        ]);
    }

    public function editCustomer($id)
    {
        // Fetch the customer and their profile
        $customer = User::with(['appointmentCancellations.service', 'appointmentCancellations.cancelledBy'])->find($id);
        $perPage = (int) request('per_page', 5);
        $perPage = in_array($perPage, [5, 10, 20, 50], true) ? $perPage : 5;
        $invoices = $customer->invoices()
            ->whereIn('status', ['sent', 'paid'])
            ->orderByDesc('issued_at')
            ->paginate($perPage)
            ->withQueryString();
        return view('customers.update', compact('customer', 'invoices'));
    }

    public function customerInvoices($id)
    {
        $customer = User::findOrFail($id);
        $perPage = (int) request('per_page', 5);
        $perPage = in_array($perPage, [5, 10, 20, 50], true) ? $perPage : 5;
        $invoices = $customer->invoices()
            ->whereIn('status', ['sent', 'paid'])
            ->orderByDesc('issued_at')
            ->paginate($perPage)
            ->withQueryString();
        return view('customers.partials.invoice-list', compact('invoices'));
    }

    public function updateCustomer(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'username' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $request->user_id,
            'password' => 'nullable|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone_number_1' => 'required|string',
            'avatar_action' => 'required|in:keep,change,delete',
            'temp_file' => 'nullable|string',
            'current_avatar' => 'nullable|string',
        ]);

        $user = User::findOrFail($request->user_id);

        // Update user account information
        $user->name = $request->username;
        $user->email = $request->email;
        // Update password only if provided
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $isEmailVerified = $request->boolean('email_verified') ?? false;
        if ($isEmailVerified) {
            $user->email_verified_at = Carbon::now();
        } else {
            $user->email_verified_at = null;
        }
        $user->status = $request->boolean('status') ?? false;
        $user->block_reservations = $request->boolean('block_reservations') ?? false;
        $user->block_messages = $request->boolean('block_messages') ?? false;

        $user->save();

        // Update profile information
        $profile = $user->profile;
        if (!$profile) {
            $profile = new Profile();
            $profile->user_id = $user->id;
        }

        $profile->first_name = $request->first_name;
        $profile->last_name = $request->last_name;
        $profile->phone_number_1 = $request->phone_number_1;
        $profile->phone_number_2 = $request->phone_number_2;
        $profile->gender = $request->gender;
        $profile->address = $request->street_address;
        $profile->city = $request->city;
        $profile->state = $request->state;
        $profile->zip_code = $request->zip_code;
        $profile->emergency_contact_info = $request->emergency_contact_info;
        $profile->home_number = $request->home_number;
        $profile->work_number = $request->work_number;

        // Handle avatar based on action
        switch ($request->avatar_action) {
            case 'keep':
                // Do nothing - keep the current avatar
                break;

            case 'change':
                // Delete old avatar if exists
                if ($profile->avatar_img) {
                    $oldAvatarPath = 'profiles/' . $profile->avatar_img;
                    if (Storage::disk('public')->exists($oldAvatarPath)) {
                        Storage::disk('public')->delete($oldAvatarPath);
                    }
                }

                // Move new avatar from temp to permanent location
                if ($request->temp_file) {
                    $tempFile = $request->temp_file;
                    $tempPath = 'temp/' . $tempFile;

                    if (Storage::disk('local')->exists($tempPath)) {
                        $permanentPath = 'profiles/' . $tempFile;
                        Storage::disk('public')->put($permanentPath, Storage::disk('local')->get($tempPath));
                        Storage::disk('local')->delete($tempPath);
                        $profile->avatar_img = $tempFile;
                    }
                }
                break;

            case 'delete':
                // Delete current avatar
                if ($profile->avatar_img) {
                    $avatarPath = 'profiles/' . $profile->avatar_img;
                    if (Storage::disk('public')->exists($avatarPath)) {
                        Storage::disk('public')->delete($avatarPath);
                    }
                    $profile->avatar_img = null;
                }
                break;
        }

        $profile->save();

        // save additional owners
        $owners = json_decode($request->owners);
        // Collect IDs of submitted owners
        $submittedIds = [];
        foreach ($owners as $owner) {
            if (!empty($owner->id)) {
                $submittedIds[] = $owner->id;
            }
        }

        // Delete owners that are NOT in the submitted list
        AdditionalOwner::where('user_id', $user->id)
            ->whereNotIn('id', $submittedIds)
            ->delete();

        // Update or create owners
        foreach ($owners as $owner) {
            if (!empty($owner->id)) {
                // Update existing owner
                $additionalOwner = AdditionalOwner::find($owner->id);
                if ($additionalOwner) {
                    $additionalOwner->full_name = $owner->name;
                    $additionalOwner->phone_number = $owner->phone;
                    $additionalOwner->save();
                }
            } else {
                // Create new owner
                if (!empty($owner->name) || !empty($owner->phone)) {
                    $additionalOwner = new AdditionalOwner;
                    $additionalOwner->user_id = $user->id;
                    $additionalOwner->full_name = $owner->name;
                    $additionalOwner->phone_number = $owner->phone;
                    $additionalOwner->save();
                }
            }
        }

        return redirect()->route('customers')->with([
            'status' => 'success',
            'message' => 'Customer updated successfully!'
        ]);
    }

    public function deleteCustomer(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);

        // Delete user's avatar if exists
        if ($user->profile && $user->profile->avatar_img) {
            $avatarPath = 'profiles/' . $user->profile->avatar_img;
            if (Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }
        }

        // Delete the user's pets' images if exists
        foreach ($user->pets as $pet) {
            if ($pet->pet_img) {
                $petAvatarPath = 'pets/' . $pet->pet_img;
                if (Storage::disk('public')->exists($petAvatarPath)) {
                    Storage::disk('public')->delete($petAvatarPath);
                }
            }
        }

        // Delete the user (this will cascade delete profile and role relationships and additional users and pets)
        $user->delete();

        return redirect()->route('customers')->with([
            'status' => 'success',
            'message' => 'Customer deleted successfully!'
        ]);
    }
}
