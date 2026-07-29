<?php

namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Department;

use Illuminate\Http\Client\ResponseSequence;
use Illuminate\Http\Request;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;

class DepartmentController extends Controller
{
    // إنشاء قسم
    public function store(Request $request)
    {
      if ($request->user()->role !== 'admin') {
        return response()->json([
            'success' => false,
            'message' => 'ليس لديك صلاحية لإضافة أقسام'
        ], 403);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:departments,name',
        'description' => 'nullable|string|max:1000',
        'type' => 'required|in:كهرباء,دهان,هيكل', // مطابقة للـ Enum في قاعدة البيانات
    ]);

    $department = Department::create($validated);

    return response()->json([
        'success' => true,
        'message' => 'تم إنشاء القسم بنجاح',
        'data' => $department
    ], 201);
    }






    //  عرض جميع الأقسام

   public function index()
{
    $departments = Department::all();
    
    return response()->json([
        'success' => true,
        'message' => 'تم جلب قائمة الأقسام بنجاح',
        'data' => $departments
    ]);
}




/// تابع تحديث القسم 

public function update(Request $request  , Department $department)
{
 $user = $request->user();
 if($user->role !== 'admin')
    {
       return response()->json([
        'success'=>false ,
        'message'=>'ليس لديك صلاحيات'
       ] , 403);
    }

    $validated = $request->validate([
     'name' => 'sometimes|string|max:255|unique:departments,name' ,
     'description' => 'nullable|string|max:1000'  , 
     'type' => 'sometimes|in:كهرباء,دهان,هيكل',

    ]);
    $department->update($validated);
    return response()->json([
        'success'=>true,
        'message'=>'تم تحديث القسم بنجاح',
        'data'=>$department
    ],201);
}





// تابع لحذف القسم
public function destroy(Request $request  ,  Department $department)
{
    // التحقق من الصلاحيات
    if ($request->user()->role !== 'admin') {
        return response()->json([
            'success' => false,
            'message' => 'ليس لديك صلاحية لحذف الأقسام'
        ], 403);
    }

    //  تحقق أمني: منع حذف القسم إذا كان هناك موظفين مرتبطين به
    if ($department->employees()->count() > 0) {
        return response()->json([
            'success' => false,
            'message' => 'لا يمكن حذف هذا القسم لوجود موظفين مرتبطين به. قم بنقل الموظفين أولاً.'
        ], 403);
    }

    $department->delete();

    return response()->json([
        'success' => true,
        'message' => 'تم حذف القسم بنجاح'
    ]);
}




// تابع عرض تفاصيل القسم مع قائمة الموظفين التابعين له
public function showWithEmployee(Request $request, $id) 
{
    $user = $request->user();

    if ($user->role !== 'admin') {
        return response()->json([
            'success' => false,
            'message' => 'ليس لديك الصلاحيات الكافية لعرض تفاصيل الموظفين'
        ], 403);
    }

    // البحث عن القسم
    $department = Department::find($id);
    
    if (!$department) {
        return response()->json([
            'success' => false,
            'message' => 'القسم الذي تريد الاستفسار عنه قد يكون محذوف مسبقاً'
        ], 404);
    }

    $department->load('employees.user'); 
    return response()->json([
        'success' => true,
        'message' => 'تم جلب تفاصيل القسم مع الموظفين التابعين له',
        'data' => [
            'department' => $department,
            'employees_count' => $department->employees->count(),
            'employees_list' => $department->employees->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->user->first_name . ' ' . $emp->user->last_name,
                    'role' => $emp->user->role
                ];
            })
        ]
    ]);
}




//     عرض إحصائيات قسم معين
// التابع مخصص لعرض ملخص إحصائي سريع لقسم معين
public function statistics(Request $request, $id)
{
    $user = $request->user(); //  جلب بيانات المستخدم الحالي
    if ($user->role !== 'admin' && $user->role !== 'employee') {
        return response()->json([
            'success' => false,
            'message' => 'ليس لديك الصلاحيات الكافية لعرض إحصائيات الأقسام'
        ], 403);
    }

    $department = Department::find($id);

    if (!$department) {
        return response()->json([
            'success' => false,
            'message' => 'القسم غير موجود'
        ], 404);
    }

    // 3. عدد الموظفين في القسم
    $employeesCount = $department->employees()->count();

    // 4. معرفة من هو المدير الحالي (إن وجد)
    $managerName = null;
    if ($department->manager_id) {
        $manager = Employee::with('user')->find($department->manager_id);
        if ($manager) {
            $managerName = $manager->user->first_name . ' ' . $manager->user->last_name;
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'تم جلب إحصائيات القسم بنجاح',
        'data' => [
            'department_name' => $department->name,
            'department_type' => $department->type,
            'total_employees' => $employeesCount,
            'manager_name' => $managerName ?? 'لم يتم تعيين مدير بعد',
        ]
    ]);
}
}
