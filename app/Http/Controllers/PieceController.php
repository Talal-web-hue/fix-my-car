<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use Illuminate\Http\Client\ResponseSequence;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;

class PieceController extends Controller
{
    // تابع إضافة  القطع ,  هي  من   صلاحيات الأدمن   و الموظف
    public function store(Request $request)
    {
       $user  = $request->user();
         if (!$user->role === 'admin' && !$user->role === 'employee')
             {
        return response()->json([
            'success'=>false,
            'message'=>'ليس لديك  الصلاحيات  الكافية  لإضافة القطع'
        ],403);
    }
    $valdidation =  $request->validate(
        [
            'name'=>'required|string|',
            'price'=>'required|numeric|min:0',
            'description'=>'nullable|string'
        ]);

    $piece = Piece::create($valdidation);
    return response()->json([
        'success'=>true,
        'message'=>'تم إنشاء القطع بنجاح',
        'data'=>$piece
    ] , 201);
   }




   // تابع لعرض  تفاصيل قطعة ما 
   public function show($id)
   {
    $piece = Piece::find($id);
    if(!$piece)
    {
        return response()->json([
            'success'=>false,
            'message'=>'القطعة غير موجودة'
        ],404);
    }
    return response()->json([
        'success'=>true,
        'message'=>'تم جلب تفاصيل القطعة بنجاح',
        'data'=>$piece
    ], 200);
   }




   // تابع لتعديل  بيانات القطعة ,  هي  من   صلاحيات الأدمن   و الموظف
    public function update(Request $request , $id)
    {
        $user  = $request->user();
        if (!$user->role === 'admin' && !$user->role === 'employee')
            {
       return response()->json([
           'success'=>false,
           'message'=>'ليس لديك  الصلاحيات  الكافية  لتعديل بيانات القطع'
       ],403);
    }
     $piece = Piece::find($id);
        if(!$piece)
        {
            return response()->json([
                'success'=>false,
                'message'=>'القطعة غير موجودة'
            ],404);
        }
    // التحقق من صحة البيانات المدخلة
    $validation = $request->validate([
        'name'=>'sometimes|required|string',
        'price'=>'sometimes|required|numeric|min:0',
        'description'=>'nullable|string'
    ]);
    // تحديث بيانات القطعة
    $piece->update($validation);
    return response()->json([
        'success'=>true,
        'message'=>'تم تعديل بيانات القطعة بنجاح',
        'data'=>$piece
    ], 200);    
}



// تابع لحذف قطعة ما ,  هي  من   صلاحيات الأدمن  فقط
public function delete(Request $request , $id)
{
    $user  = $request->user();
    if (!$user->role === 'admin') 
    
        {
            return response()->json([
                'success'=>false,
                'message'=>'ليس لديك  الصلاحيات  الكافية  لحذف القطع'
            ],403); 
        }
    $piece  =  Piece::find($id);
    if(!$piece)
    {
        return response()->json([
            'success'=>false,
            'message'=>'القطعة غير موجودة إو أنك قمت بحذفها مسبقا'
        ],404);
    }
     // التحقق من أن القطعة غير مستخدمة  في  الفاتورة
    if($piece->invoices()->exists())
        {
            return response()->json([
                'success'=>false,
                'message'=>'لا يمكنك حذف القطعة لإنها مستخدمة في فاتورة'
            ] , 403);    
        }
    $piece->delete();
    return response()->json([
        'success'=>true,
        'message'=>'تم حذف القطعة بنجاح'
    ] , 200);
}



//  تابع للبحث عن قطع  حسب عمود اسم القطعة 



// تابع إحصائيات شاملة

public function statistics(Request $request)
{
    $user = $request->user();
    
    //  التحقق من الصلاحيات
    if (!in_array($user->role, ['admin', 'employee'])) {
        return response()->json([
            'success' => false,
            'message' => 'ليس لديك الصلاحيات الكافية لعرض الإحصائيات'
        ], 403);
    }

   $totalPieces = Piece::count();
   $totalValue  = Piece::sum('price');
   $averagePrice = Piece::avg('price') ?? 0;
   $maxPrice = Piece::max('price') ?? 0;
   $minPrice = Piece::min('price') ?? 0;

     //  أغلى 5 قطع
    $mostExpensive = Piece::orderBy('price', 'desc')
        ->limit(5)
        ->get(['id', 'name', 'price']);
    
    // أرخص 5 قطع
    $cheapest = Piece::orderBy('price', 'asc')
        ->limit(5)
        ->get(['id', 'name', 'price']);
    
    // أحدث القطع  المضافة
    $latestAdded = Piece::orderBy('created_at' ,  'desc')->limit(5)
    ->get(['id','name','price','created_at']);

       //  توزيع الأسعار 
    $priceDistribution = [
        'under_1000' => Piece::where('price', '<', 1000)->count(),
        '1000_to_5000' => Piece::whereBetween('price', [1000, 5000])->count(),
        '5000_to_10000' => Piece::whereBetween('price', [5000, 10000])->count(),
        '10000_to_50000' => Piece::whereBetween('price', [10000, 50000])->count(),
        'over_50000' => Piece::where('price', '>', 50000)->count(),
    ];

     //  القطع الأكثر استخداماً في الفواتير
    $mostUsedPieces = Piece::withCount('invoices')
        ->orderBy('invoices_count', 'desc')
        ->limit(5)
        ->get(['id', 'name', 'price', 'invoices_count']);
    
    //  القطع التي لم تُستخدم أبداً
    $unusedPiecesCount = Piece::doesntHave('invoices')->count();
    
      // إجمالي الإيرادات من كل القطع
    $totalRevenue = \Illuminate\Container\Attributes\DB::table('invoice_pieces')
        ->selectRaw('SUM(quantity * price_at_time_of_sale) as total')
        ->value('total') ?? 0;
    

          return response()->json([
        'success' => true,
        'message' => 'تم جلب الإحصائيات بنجاح',
        'data' => [
            'overview' => [
                'total_pieces' => $totalPieces,
                'total_value' => round($totalValue, 2),
                'average_price' => round($averagePrice, 2),
                'max_price' => round($maxPrice, 2),
                'min_price' => round($minPrice, 2),
                'total_revenue' => round($totalRevenue, 2),
            ],
            'most_expensive' => $mostExpensive,
            'cheapest' => $cheapest,
            'latest_added' => $latestAdded,
            'price_distribution' => $priceDistribution,
            'most_used_pieces' => $mostUsedPieces,
            'unused_pieces_count' => $unusedPiecesCount,
        ]
    ]);
    
}






// تابع index لعرض جميع القطع مع بحث و فلاتر 
//  من صلاحيات المستخدم و الأدمن و الموظف
public function index(Request $request)
  {
  $user = $request->user();
  if(!$user)
    {
      return response()->json([
        'success'=>false,
        'message'=>'يجب تسجيل الدخول أولاً'
      ] , 401);
    }
    $query = Piece::query();
  //  البحث بالاسم أو الوصف
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
  return response()->json(
    [
        'success'=>true,
        'message'=>'تم جلب  المنتجات',
        'data'=>$query
            ]  ,  200);

}







// تابع نسخ قطعة في حال كان لدينا إضافة قطع متشابهة كثيراً

public function duplicate(Request $request , $id)
{
    $user = $request->user();
    
    //  التحقق من الصلاحيات
    if (!in_array($user->role, ['admin', 'employee'])) {
        return response()->json([
            'success' => false,
            'message' => 'ليس لديك الصلاحيات الكافية لنسخ القطع'
        ], 403);
    }

     $originalPiece = Piece::find($id); // هنا قمنا بالبحث عن القطعة التي نريد نسخها
    
    if (!$originalPiece) {
        return response()->json([
            'success' => false,
            'message' => 'القطعة الأصلية غير موجودة'
        ], 404);
    }

    $validatation = $request->validate([
        'name'=>'required|string',
        'price' => 'nullable|numeric|min:0',
        'description' => 'nullable|string|max:1000'
    ]);
        //  بناء بيانات القطعة الجديدة
    $newPieceData = [
        'name' => $validated['name'] ?? $originalPiece->name,
        'price' => $validated['price'] ?? $originalPiece->price,
        'description' => $validated['description'] ?? $originalPiece->description,
    ];
  $newPiece = Piece::create($newPieceData);
      return response()->json([
        'success' => true,
        'message' => 'تم نسخ القطعة بنجاح',
        'data' => [
            'original_piece' => [
                'id' => $originalPiece->id,
                'name' => $originalPiece->name,
                'price' => $originalPiece->price,
            ],
            'new_piece' => $newPiece,
            'customizations_applied' => array_keys($validatation),
        ]
    ], 201);
}
    
}


// تابع الحذف المتعدد
 