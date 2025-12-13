<?php

namespace App\Http\Controllers;

use App\Models\FixRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FixRequestController extends Controller
{
    public function store(Request $request)
    {
         $request->validate([
            'car_id' => 'required|exists:cars,id',
            'description' => 'required|string|max:500',
            'location' => 'required|string|max:500',
            'status' => 'nullable|string|in:قيد الانتظار,تم التعيين,قيد التنفيذ,منتهي'
        ]);

        $user = Auth::user();
        $car = $user->cars()->where('id', 'car_id')
        ->where('user_id' , $user->id)->get();

        if (!$car) {
            return response()->json(
                [
                    'message' => 'السيارة المطلوبة غير موجودة أو لا تنتمي للمستخدم الحالي'
                ],
                403
            );
        }

        $fixRequest = FixRequest::create([
            'car_id' => $request->car_id,
            'description' => $request->description,
            'status' => $request->status ,
            'location' => $request->location
        ]);

        return response()->json(
            [
                'message' => 'تم إنشاء طلب التصليح بنجاح',
                'fixRequest' => $fixRequest
            ],
            201
        );
    }

    //  لعرض جميع طلبات العميل
    public function index()
    {
        $user = Auth::user();

        // جلب جميع طلبات التصليح التي تخص سيارة المستخدم الحالي
        $fixRequests = FixRequest::whereHas('car', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        return response()->json([
            'message' => 'تم جلب جميع طلبات التصليح بنجاح',
            'fixRequests' => $fixRequests
        ], 200);
    }

    public function show($id)
    {
        $user = Auth::user();

        // جلب طلب التصليح المحدد مع معلومات السيارة
        $fixRequest = FixRequest::with('car')->whereHas('car', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id', $id)->first();

        if (!$fixRequest) {
            return response()->json(
                [
                    'message' => 'طلب التصليح غير موجود أو لا ينتمي للمستخدم الحالي'
                ],
                404
            );
        }

        return response()->json([
            'message' => 'تم جلب تفاصيل طلب التصليح بنجاح',
            'fixRequest' => $fixRequest
        ], 200);
    }

    public function delete($id)
    {
        $user = Auth::user();

        // جلب طلب التصليح المحدد والتحقق من أنه ينتمي للمستخدم الحالي
        $fixRequest = FixRequest::whereHas('car', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id', $id)->first();

        if(!$fixRequest)
        {
            return response()->json(
                [
                    'message' => 'طلب التصليح غير موجود أو لا ينتمي للمستخدم الحالي'
                ],
                404
            );
        }

        $fixRequest->delete();

        return response()->json([
            'message' => 'تم حذف طلب التصليح بنجاح'
        ], 200);
    }
}