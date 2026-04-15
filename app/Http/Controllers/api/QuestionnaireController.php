<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Questionnaire;
use App\Models\PetProfile;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Notification;

class QuestionnaireController extends Controller
{
    public function detail(Request $request)
    {
        $request->validate([
            'pet_id' => 'required|exists:pet_profiles,id',
        ]);

        $questionnaires = Questionnaire::where('pet_id', $request->pet_id)->get();
        $pet = PetProfile::find($request->pet_id);
        $serviceCategories = ServiceCategory::all();

        return response()->json([
            'status' => true,
            'message' => 'Questionnaire retrieved successfully.',
            'result' => ['questionnaires' => $questionnaires, 'pet' => $pet, 'service_categories' => $serviceCategories]
        ], 200);
    }

    public function save(Request $request)
    {
        $request->validate([
            'questionnaire_id' => 'nullable',
            'pet_id' => 'required|exists:pet_profiles,id',
            'service_category_id' => 'required|exists:service_categories,id',
            'questions_answers' => 'required',
        ]);

        $questionnaireId = $request->questionnaire_id;
        if (isset($questionnaireId)) {
            $questionnaire = Questionnaire::find($questionnaireId);
        } else {
            $questionnaire = new Questionnaire;
            $questionnaire->pet_id = $request->pet_id;
            $questionnaire->user_id = Auth::id();
            $questionnaire->service_category_id = $request->service_category_id;
        }
        $questionnaire->questions_answers = $request->questions_answers;
        $questionnaire->status = 'pending';
        $questionnaire->save();

        // Notify admin with 'send/receive messages' permission about new questionnaire submitted
        $users = User::select('users.*')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles_permissions', 'role_user.role_id', '=', 'roles_permissions.role_id')
            ->where('roles_permissions.permission_id', 15)
            ->where('roles_permissions.can_read', 1)
            ->distinct()
            ->get();
        foreach ($users as $user) {
            $notification = new Notification;
            $notification->user_id = $user->id;
            $notification->sender_id = Auth::id();
            $notification->title = "New Pet Questionnaire Submitted";
            $notification->message = "A customer has submitted new pet questionnaire.";
            $notification->type = "pet_questionnaire";
            $notification->metadata = json_encode(['questionnaire_id' => $questionnaire->id, 'pet_id' => $questionnaire->pet_id]);
            $notification->is_read = false;
            $notification->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Questionnaire saved successfully.',
            'result' => $questionnaire
        ], 200);
    }

}
