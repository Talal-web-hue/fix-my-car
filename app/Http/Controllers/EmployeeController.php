<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function store(Request $request)
    {
     $user = $request->user();
    //  الآدمن له الصلاحية في إضافة الموظف فقط
     if($user->role !== 'admin')
        {
       return response()->json([
        'success'=>'false',
        'message'=>'ليس لديك الصلاحية لإضافةالموظفين'
       ] , 403);
        }
    
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id|unique:employees,user_id', // التأكد أن المستخدم ليس موظفاً مسبقاً
        'department_id'=>'required|integer|exists:departments,id',
        'birth' => 'required|date',
        ]); 

        // إنشاء سجل الموظف
        $employee = Employee::create([
         'user_id'=>$validated['user_id'],
         'department_id'=>$validated['department_id'],
         'birth'=>$validated['birth'],
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'تم إضافة الموظف بنجاح',
            'data'=>$employee->load('user','department')
        ] , 201);
    }



//  تابع لعرض الموظفين مع معلوماتهم الشخصية وقسمهم الذي ينتمون إليه
    public function index(Request $request)
    {
        $user = $request->user();
        //  الآدمن له الصلاحية في عرض الموظفين فقط
         if($user->role !== 'admin')
            {
           return response()->json([
            'success'=>'false',
            'message'=>'ليس لديك الصلاحية للوصول إلى قائمة الموظفين'
           ] , 403);
            }
    
        $employees = Employee::with('user','department')->get();
        return response()->json([
            'success'=>true,
            'message'=>'قائمة الموظفين',
            'data'=>$employees
        ] , 200);
    }
}
