<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\FixRequestController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PieceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



//   User Api
Route::post('register' , [UserController::class, 'register']);
Route::post('login' , [UserController::class , 'login']);
Route::post('logout' , [UserController::class , 'logout'])->middleware('auth:sanctum');
Route::get('getUser' , [Usercontroller::class , 'getUser'])->middleware('auth:sanctum');
Route::delete('deleteUser' , [Usercontroller::class , 'deleteUser'])->middleware('auth:sanctum'); //  خاص  بالسمتخدم المسجل فقط
Route::put('updateUser' , [Usercontroller::class , 'updateUser'])->middleware('auth:sanctum'); // للتعديل على معلوماتي الشخصية


// Car Api

Route::post('storeCar' , [CarController::class , 'storeCar'])->middleware('auth:sanctum');
Route::get('getCar' , [CarController::class , 'getCar'])->middleware('auth:sanctum');
Route::get('getUserCars' , [CarController::class , 'getUserCars'])->middleware('auth:sanctum');
Route::put('updateUserCars/{idCar}' , [CarController::class , 'updateUserCars'])->middleware('auth:sanctum');
Route::get('getCarByPlate/{plateNumber}', [CarController::class, 'getCarByPlate'])->middleware('auth:sanctum');
Route::delete('deleteCarByPlate/{plateNumber}', [CarController::class, 'deleteCarByPlate'])->middleware('auth:sanctum');
Route::get('getCar/{id}' , [CarController::class , 'get'])->middleware('auth:sanctum');



// Fix_Request Api

Route::post('storeFixRequest' , [FixRequestController::class , 'store'])->middleware('auth:sanctum');
Route::get('getAllFixRequests', [FixRequestController::class, 'index'])->middleware('auth:sanctum');
Route::get('showFixRequest/{id}', [FixRequestController::class, 'show'])->middleware('auth:sanctum'); // لجلب تفاصيل طلب التصليح مع السيارة التي ينتمي لها طلب التصليح
Route::delete('deleteFixRequest/{id}', [FixRequestController::class, 'delete'])->middleware('auth:sanctum');


// Appointment Api

Route::post('storeAppointment', [AppointmentController::class, 'store'])->middleware('auth:sanctum');
Route::get('indexAllAppointments', [AppointmentController::class, 'index'])->middleware('auth:sanctum');
Route::delete('destroy/{id}', [AppointmentController::class, 'destroy'])->middleware('auth:sanctum');
Route::put('update/{id}', [AppointmentController::class, 'update'])->middleware('auth:sanctum');
Route::get('show/{id}', [AppointmentController::class, 'show'])->middleware('auth:sanctum');
Route::get('statistics', [AppointmentController::class, 'statistics'])->middleware('auth:sanctum');




//  Pieces Api

Route::post('store' , [PieceController::class ,'store'])->middleware('auth:sanctum');
Route::get('show/{id}' , [PieceController::class ,'show'])->middleware('auth:sanctum');
Route::put('update/{id}' , [PieceController::class ,'update'])->middleware('auth:sanctum');
Route::delete('delete/{id}' , [PieceController::class ,'delete'])->middleware('auth:sanctum');
Route::post('duplicate/{id}' , [PieceController::class , 'duplicate'])->middleware('auth:sanctum');
Route::get('statistics'  ,  [PieceController::class , 'statistics'])->middleware('auth:sanctum');
Route::get('index'  , [PieceController::class ,  'index'])->middleware('auth:sanctum');



// Invoice Api
Route::post('store' , [InvoiceController::class, 'store'])->middleware('auth:sanctum');




// Department Api
Route::post('storeDepartment' , [DepartmentController::class , 'store'])->middleware('auth:sanctum');
Route::get('indexDepartments' , [DepartmentController::class , 'index'])->middleware('auth:sanctum');
Route::put('updateDepartment/{department}' , [DepartmentController::class , 'update'])->middleware('auth:sanctum');
Route::delete('deleteDepartment/{department}' , [DepartmentController::class , 'destroy'])->middleware('auth:sanctum');
Route::get('showWithEmployee/{id}' , [DepartmentController::class , 'showWithEmployee'])->middleware('auth:sanctum');
Route::get('statistics/{id}' , [DepartmentController::class , 'statistics'])->middleware('auth:sanctum');