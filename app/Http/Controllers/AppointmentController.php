<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{

    public function store(Request $request)
{
    $user = $request->user(); //  المستخدم الحالي من المصادقة
    
    $validated = $request->validate([
        'car_id' => 'required|exists:cars,id',
        'appointment_date' => 'required|date|after:now',
        'status' => 'sometimes|in:قيد الانتظار,منتهي,مرفوض,مقبول', //  قيم إنجليزية
        'notes' => 'nullable|string|max:1000',
    ]);
    
    //  التحقق من أن السيارة تعود للمستخدم الحالي
    $car = Car::where('id', $validated['car_id'])
              ->where('user_id', $user->id)
              ->first();
    
    if (!$car) {
        return response()->json([
            'success' => false,
            'message' => 'السيارة غير موجودة أو لا تعود لك.'
        ], 403);
    }
    
    //  التحقق من عدم وجود موعد آخر في نفس الوقت
    $existingAppointment = Appointment::where('car_id', $validated['car_id'])
        ->where('appointment_date', $validated['appointment_date'])
        ->where('status', '!=', 'مرفوض') //  تجاهل المواعيد الملغاة
        ->first();
    
    if ($existingAppointment) {
        return response()->json([
            'success' => false,
            'message' => 'يوجد موعد آخر لنفس السيارة في نفس الوقت.'
        ], 409);
    }
    
    //  إضافة user_id تلقائياً من المصادقة
    $appointmentData = array_merge($validated, [
        'user_id' => $user->id,  
        'status' => $validated['status'] ?? 'قيد الانتظار',
    ]);
    
    $appointment = Appointment::create($appointmentData);
    $appointment->load(['car', 'user']);
    
    return response()->json([
        'success' => true,
        'message' => 'تم حجز الموعد بنجاح.',
        'appointment' => $appointment
    ], 201);
}



// تابع يقوم بعرض جميع المواعيد مع بيانات السيارة

public function index(Request $request)
{
  $user = $request->user();
  $query = Appointment::with('car')->where('user_id' , $user->id);

  // فلترة حسب رقم السيارة
  if($request->filled('car_id'))
    {
   $query->where('status' , $request->status);
    }

  if ($request->filled('from_date')) {
        $query->where('appointment_date', '>=', $request->from_date);
    }
    
// فلترة حسب حالة الموعد
if($request->filled('status'))
    {
   $query->where('status' , $request->status);
    }

$appointments = $query->orderBy('appointment_date' , 'desc')->paginate(10);  // أي كل مواعيد في صفحة
return response()->json([
    'success'=>true,
    'message'=>'إليك قائمة المواعيد',
    'appointments'=>$appointments
] , 200);
}



//  تابع إلغاء الموعد 

public function destroy(Request $request , $id)
{
  $user = $request->user();
    $appointment = Appointment::where('id', $id)    // البحث عن الموعد
        ->where('user_id', $user->id)
        ->first();
       // التحقق من وجود الموعد
    if (!$appointment) {
        return response()->json([
            'success' => false,
            'message' => 'الموعد غير موجود أو لا يعود لك.'
        ], 404);
    }
    
    // التحقق من أن الموعد لم يُنفذ بعد
    if ($appointment->status === 'منتهي'  ||  $appointment->status ==='مرفوض') {
        return response()->json([
            'success' => false,
            'message' => 'لا يمكن إلغاء موعد منتهي أو مرفوض'
        ], 403);
    }

    $appointment->update(['status' => 'مرفوض']);
    return response()->json([
        'success'=>true,
        'message'=>'تم إلغاء الموعد بنجاح',
        'appointment'=>$appointment
    ] , 200);

}



public function update(Request $request, $id)
{
    $user = $request->user();
    
    // البحث عن الموعد
    $appointment = Appointment::where('id', $id)
        ->where('user_id', $user->id)
        ->first();
    
    // التحقق من وجود الموعد
    if (!$appointment) {
        return response()->json([
            'success' => false,
            'message' => 'الموعد غير موجود أو لا يعود لك.'
        ], 404);
    }
 // التحقق من أن الموعد لم يُنفذ بعد , لإنه في حال تم تنفيذ الموعد لا نستطيع التعديل عليه
    if ($appointment->status === 'منتهي') {
        return response()->json([
            'success' => false,
            'message' => 'لا يمكن تعديل موعد منتهي.'
        ], 403);
    }
  // التحقق من أن الموعد لم يُلغَ مسبقاً
    if ($appointment->status === 'مرفوض') {
        return response()->json([
            'success' => false,
            'message' => 'لا يمكن تعديل موعد ملغي.'
        ], 403);
    }
    
        // Validation للتعديل
    $validated = $request->validate([
        'car_id' => 'sometimes|exists:cars,id',
        'appointment_date' => 'sometimes|date|after:now',
        'status' => 'sometimes|in:قيد الانتظار,مقبول,مرفوض,منتهي',
        'notes' => 'nullable|string',
    ]);

    $appointment->update($validated);
    $appointment->load(['car']);
    return response()->json([
        'success' => true,
        'message' => 'تم تحديث الموعد بنجاح',
        'appointment' => $appointment
    ], 200);
}





// تابع عرض تفاصيل الموعد مع بيانات السيارة

public  function show(Request $request , $id)
{
    $user = $request->user();
    $appointment = Appointment::with('car')
        ->where('id', $id)
        ->where('user_id', $user->id)
        ->first();

    if (!$appointment) {
        return response()->json([
            'success' => false,
            'message' => 'الموعد غير موجود أو لا يعود لك.'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'تفاصيل الموعد',
        'appointment' => $appointment
    ], 200);

}



//  تابع إحصائيات  المواعيد
public function statistics(Request $request)
{
    $user = $request->user();
    
    // إحصائيات عامة للمستخدم
    $totalAppointments = Appointment::where('user_id', $user->id)->count();
    
    $byStatus = Appointment::where('user_id', $user->id)
        ->selectRaw('status, count(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status');
    
    // المواعيد القادمة (خلال 30 يوم)
    $upcomingCount = Appointment::where('user_id', $user->id)
        ->where('appointment_date', '>=', now())
        ->where('appointment_date', '<=', now()->addDays(30))
        ->where('status', '!=', 'مرفوض')
        ->count();
    
    // أقرب موعد قادم
    $nextAppointment = Appointment::where('user_id', $user->id)
        ->where('appointment_date', '>=', now())
        ->where('status', '!=', 'مرفوض')
        ->orderBy('appointment_date')
        ->with('car')
        ->first();
    
    $byCar = Appointment::where('appointments.user_id', $user->id)  
        ->join('cars', 'appointments.car_id', '=', 'cars.id')
        ->selectRaw('cars.plateNumber, cars.car_manufacture, cars.model, count(appointments.id) as appointments_count')
        ->groupBy('cars.id', 'cars.plateNumber', 'cars.car_manufacture', 'cars.model')
        ->get();
    
    // إحصائيات شهرية (آخر 6 أشهر)
    $monthlyStats = Appointment::where('user_id', $user->id)
        ->where('created_at', '>=', now()->subMonths(6))
        ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, count(*) as count')
        ->groupBy('year', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->get();
    
    return response()->json([
        'success' => true,
        'message' => 'تم جلب الإحصائيات بنجاح.',
        'statistics' => [
            'total_appointments' => $totalAppointments,
            'by_status' => $byStatus,
            'upcoming_in_30_days' => $upcomingCount,
            'next_appointment' => $nextAppointment,
            'by_car' => $byCar,
            'monthly_stats' => $monthlyStats,
        ]
    ]);
}



}