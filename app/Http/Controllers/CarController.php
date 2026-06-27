<?php

namespace App\Http\Controllers;
use App\Http\Requests\Car\storeCarRequest;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
class CarController extends Controller
{

    public function storeCar(Request $request)
    {
        //
        $validated = $request->validate([
            'model'=>'required|string|max:100',
            'plateNumber'=>'required|string|max:15',
            'vin'=>'nullable|string|min:6',
            'color'=>'required|string|max:50',
            'car_manufacture'=>'required|string|max:100',
            'car_type'=>'required|string'
        ]);

            $user = Auth::user();

            // نتحقق مما إذا كانت السيارة مسجلة مسبقة لنفس المستخدم أم لا بناءا على رقم اللوحة
            $existingCar = Car::where('user_id' , $user->id)->
            where('plateNumber' , $validated['plateNumber'])->first();
            if($existingCar)
            {
               return response()->json(
                [
                    'message'=>'لقد قمت بتسجيل سيارة بنفس اللوحة مسبقا'
                    , 'car'=>$existingCar
                    ] , 409);
            }

            $car = $user->cars()->create($validated);

           return response()->json(
            [
                'message'=>'تم إنشاء السيارة بنجاح' ,
                $car
            ] , 201);
        }

    public function getUserCars(): JsonResponse
    {
        $user = Auth::user();

        // جلب جميع السيارات التي تتبع للمستخدم الحالي
        $cars = $user->cars; // Assuming the User model has a `cars` relationship defined

        return response()->json([
            'message' => 'تم جلب جميع سيارات المستخدم بنجاح',
            'عدد سياراتك هو: ' . $cars->count(),
            'cars' => $cars
        ], 200);
    }

     public function deleteUserCars($idCar)
    {
        $user = Auth::user();
        $car = $user->cars()->where('id' , $idCar)->first();

        if(!$car)
        {
            return response()->json(
                [
                    'message'=>'لم يتم العثور على السيارة المطلوبة'
                ] , 404);
        }

        $car->delete();

        return response()->json(
            [
                'message'=>'تم حذف السيارة بنجاح'
            ] , 200);
    }

    public function updateUserCar(Request $request, $idCar): JsonResponse
    {
        $validated = $request->validate([
            'model' => 'sometimes|string|max:100',
            'plateNumber' => 'sometimes|string|max:15',
            'vin' => 'nullable|string|size:17',
            'color' => 'sometimes|string|max:50',
            'car_manufacture' => 'sometimes|string|max:100',
            'car_type' => 'sometimes|enum'
        ]);

        $user = Auth::user();
        $car = $user->cars()->where('id', $idCar)->first();

        if (!$car) {
            return response()->json(
                [
                    'message' => 'السيارة المطلوبة غير موجودة أو لا تنتمي للمستخدم الحالي'
                ],
                403
            );
        }

        $car->update($validated);

        return response()->json(
            [
                'message' => 'تم تحديث معلومات السيارة بنجاح',
                'car' => $car
            ],
            200
        );
    }

    public function getCarByPlate($plateNumber): JsonResponse
    {
        $user = Auth::user();
        $car = $user->cars()->where('plateNumber', $plateNumber)->first();

        if (!$car) {
            return response()->json(
                [
                    'message' => 'السيارة المطلوبة غير موجودة أو لا تنتمي للمستخدم الحالي'
                ],
                404
            );
        }

        return response()->json(
            [
                'message' => 'تم جلب معلومات السيارة بنجاح',
                'car' => $car
            ],
            200
        );
    }

    public function deleteCarByPlate($plateNumber): JsonResponse
    {
        $user = Auth::user();  // للحصول على المستخدم الحالي
        $car = $user->cars()->where('plateNumber', $plateNumber)->first();

        if (!$car) {
            return response()->json(
                [
                    'message' => 'السيارة المطلوبة غير موجودة أو لا تنتمي للمستخدم الحالي'
                ],
                404
            );
        }

        $car->delete();

        return response()->json(
            [
                'message' => 'تم حذف السيارة بنجاح'
            ],
            200
        );
    }

}