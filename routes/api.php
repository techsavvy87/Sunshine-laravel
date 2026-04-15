<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ProfileController;
use App\Http\Controllers\api\ServiceController;
use App\Http\Controllers\api\AppointmentController;
use App\Http\Controllers\api\QuestionnaireController;
use App\Http\Controllers\api\ProgressController;
use App\Http\Controllers\api\PaymentController;
use App\Http\Controllers\api\PackageController;
use App\Http\Controllers\api\ContactController;
use App\Http\Controllers\api\NotificationController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/verify', [AuthController::class, 'verifyRegister']);
Route::post('/forgot/password', [AuthController::class, 'forgotPassword']);
Route::post('/reset/password', [AuthController::class, 'resetPassword']);
Route::post('/contact', [ContactController::class, 'submit']);

Route::middleware(['auth:sanctum'])->group(function () {
  Route::get('/logout', [AuthController::class, 'logout']);
  Route::post('/password/update', [AuthController::class, 'updatePassword']);

  // customer profile
  Route::get('/profile/show', [ProfileController::class, 'show']);
  Route::post('/profile/update', [ProfileController::class, 'update']);

  // customer's additional owner
  Route::get('/profile/owners/list', [ProfileController::class, 'listAdditionalOwners']);
  Route::post('/profile/owners/update', [ProfileController::class, 'updateAdditionalOwners']);

  // customer's pets
  Route::get('/profile/pets/list', [ProfileController::class, 'listPets']);
  Route::post('/profile/pets/add', [ProfileController::class, 'addPet']);
  Route::post('/profile/pets/update', [ProfileController::class, 'updatePet']);
  Route::post('/profile/pets/delete', [ProfileController::class, 'deletePet']);
  Route::get('/profile/pets/breeds', [ProfileController::class, 'listBreeds']);
  Route::get('/profile/pets/colors', [ProfileController::class, 'listColors']);
  Route::get('/profile/pets/coatTypes', [ProfileController::class, 'listCoatTypes']);

  // customer's pet vaccinations
  Route::post('/profile/pets/vaccinations/add', [ProfileController::class, 'addPetVaccinations']);

  // customer's pet certificates
  Route::post('/profile/pets/certificates/add', [ProfileController::class, 'addPetCertificate']);

  // services
  Route::get('/service/detail/{id}', [ServiceController::class, 'detail']);
  Route::get('/service/package/{id}', [ServiceController::class, 'package']);
  Route::post('/service/timeslots', [ServiceController::class, 'getTimeSlots']);
  Route::get('/service/calculate-distance/service_id={serviceId}', [ServiceController::class, 'calculateDistance']);

  // packages
  Route::get('/package/list', [PackageController::class, 'list']);
  Route::get('/package/detail/{id}', [PackageController::class, 'detail']);
  Route::get('/packages/customer', [PackageController::class, 'listCustomerPackages']);

  // appointments
  Route::post('/appointment/create', [AppointmentController::class, 'create']);
  Route::get('/appointment/list/{serviceId}', [AppointmentController::class, 'list']);
  Route::get('/appointment/cancel/{id}', [AppointmentController::class, 'cancel']);
  Route::get('/appointment/process/{id}', [AppointmentController::class, 'process']);
  // Route::get('/appointment/checkout/{id}', [AppointmentController::class, 'checkout']);

  // questionnaire
  Route::post('/questionnaire/detail', [QuestionnaireController::class, 'detail']);
  Route::post('/questionnaire/save', [QuestionnaireController::class, 'save']);

  // payment
  Route::get('/invoice/{id}', [PaymentController::class, 'invoice']);
  Route::get('/invoices', [PaymentController::class, 'invoices']);
  Route::get('/checkout/detail/{apptId}', [PaymentController::class, 'checkoutDetail']);
  Route::post('/checkout/class', [PaymentController::class, 'checkoutClass']);
  Route::post('/checkout/package', [PaymentController::class, 'checkoutPackage']);
  Route::post('/payment/stripe', [PaymentController::class, 'setStripe']);
  Route::post('/payment/complete', [PaymentController::class, 'completePayment']);

  // notifications
  Route::post('/notifications/all', [NotificationController::class, 'allNotifications']);
  Route::post('/notifications/poll', [NotificationController::class, 'pollNotifications']);
  Route::post('/notifications/read', [NotificationController::class, 'markAsRead']);
  Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
  Route::post('/notifications/toggle-status', [NotificationController::class, 'toggleStatus']);
  Route::post('/notifications/delete', [NotificationController::class, 'delete']);
});