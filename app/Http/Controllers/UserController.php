<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class UserController extends Controller
{
   public function register(Request $request)
    {
     $request->validate(
      [
        'first_name' => 'required|string|max:25',
        'last_name' => 'required|string|max:25',
        'email' => 'required|string|email|max:255|unique:users',
        'phone' => 'required|string|max:15',
        'password' => 'required|string|min:6|confirmed',
       ]);
     $user = User::create(
        [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
                'message'=>'تم إنشاء حسابك بنجاح ' ,
                $user
        ] , 201);
    }

    public function login(Request $request)
    {
    $request->validate(
        [
            'email'=>'required|email|string|max:255',
            'password'=>'required|string|min:6'
        ]);
        if(!Auth::attempt($request->only(['email' , 'password'])))
        {
           return response()->json([
            'message'=>'invalid password or email'
           ] , 404);
        }
         $user =User::where('email' , $request->email)->firstOrFail();
         $token = $user->createToken('auth_token')->plainTextToken;
         return response()->json([
            'message' => 'تم تسجيل دخولك بنجاح',
            'user' => $user,
            'access_token' => $token
        ], 201);
    }


    public function logout(Request $request)
    {
        $user = Auth::user();
        if(!$user)
        {
        return response()->json(
            [
                'message'=>'المستخدم غير موجود '
            ] , 404);
        }
        $user->currentAccessToken()->delete();
        return response()->json(
            [
                'message'=>'logout successfully'
            ] , 200 );
    }


    //  تابع لعرض معلومات المستخدم
    public function getUser()
    {
        $user = Auth::user();
        if(!$user)
        {
          return response()->json(
            [
                'message'=>'your accound not found'
            ] , 404);
        }
        $user->get();
        return response()->json(
            [
                'message'=>'معلوماتك الشخصية هي'
                , $user
                ] , 200);
    }


    //  التعديل على معلومات المستخدم

    public function updateUser(Request $request)
    {
        $user = Auth::user();
        if(!$user)
        {
           return response()->json([ 'message'=>'تأكد من حسابك هل هو موجود أم لا'
           ] , 404);
        }
        $user->update($request->all());
        return response()->json([
            'message'=>'تم تعديل معلوماتك الشخصية بنجاح'
            , $user
        ] , 201);
    }



    public function deleteUser()
        {
        $user = Auth::user();

        if (!$user)
         {
            return response()->json(
                [
                 'message' => 'المستخدم غير موجود'
                ],404);
        }

        $user->delete();

        return response()->json(
            [
                'message' => 'تم حذف المستخدم بنجاح'
            ],
            200
        );
    }

}