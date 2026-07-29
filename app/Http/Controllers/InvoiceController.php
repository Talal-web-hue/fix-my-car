<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\FixRequest;
use Illuminate\Http\Request;
use App\Models\Piece;
use Illuminate\Support\Facades\DB;
class InvoiceController extends Controller
{




// تابع إنشاء الفاتورة , هي  من صلاحيات الموظف و الأدمن  

public function store(Request $request)
{

   $user = $request->user();
  if($user->role!=='admin' && $user->role!=='employee') {
        return response()->json([
            'success' => false,
            'message' => 'ليس لديك الصلاحيات الكافية لإنشاء الفاتورة'
        ], 403);
    }
    {

    }
   $validated = $request->validate([
        'fix_request_id' => 'required|exists:fix_requests,id',
        'pieces' => 'required|array|min:1',
        'pieces.*.piece_id' => 'required|integer|exists:pieces,id',
        'pieces.*.quantity' => 'required|integer|min:1',
        'labor_cost' => 'nullable|numeric|min:0', // أضفنا هذا أيضاً لأنه مستخدم في الحساب
    ]);
      // التحقق من عدم وجود  فاتورة سابقة  لنفس الطلب 
     $existingInvoice = Invoice::where('fix_request_id', $validated['fix_request_id'])->first();
      if ($existingInvoice) {
            return response()->json('يوجد فاتورة بالفعل لهذا الطلب، يرجى تعديلها بدلاً من إنشاء جديدة.', 409);
        }
         //جلب بيانات طلب التصليح للتأكد منه
        $fixRequest = FixRequest::find($validated['fix_request_id']);
        if (!$fixRequest) {
            return response()->json('طلب التصليح غير موجود', 404);
        }
       try {
            return DB::transaction(function () use ($validated, $user, $fixRequest) {
                
                $totalPartsCost = 0;
                $piecesToAttach = [];

                // حساب تكلفة القطع وإعدادها للربط
                foreach ($validated['pieces'] as $item) {
                    $piece = Piece::find($item['piece_id']);
                    
                    // السعر وقت البيع (مهم جداً للحفاظ على التاريخ المالي)
                    $priceAtSale = $piece->price; 
                    $subtotal = $priceAtSale * $item['quantity'];
                    
                    $totalPartsCost += $subtotal;

       // تجهيز البيانات لجدول الكسر (Pivot)
                    $piecesToAttach[$piece->id] = [
                        'quantity' => $item['quantity'],
                        'price_at_time_of_sale' => $priceAtSale,
                    ];
                }

                // حساب الإجمالي النهائي
                $laborCost = $validated['labor_cost'] ?? 0;
                $finalTotal = $totalPartsCost + $laborCost;
    // إنشاء الفاتورة
                $invoice = Invoice::create([
                    'fix_request_id' => $fixRequest->id,
                    'employee_id' => $user->id, // الموظف الذي أنشأ الفاتورة
                    'total_amount' => $finalTotal,
                    'status' => 'قيد الانتظار',
                    // ملاحظة: إذا أضفت حقول parts_total و labor_cost لاحقاً، ضعها هنا
                ]);
                       $invoice->pieces()->attach($piecesToAttach);

                // تحديث حالة طلب التصليح إلى "منتهي" أو "جاهز للفاتورة" حسب سير عملك
                // $fixRequest->update(['status' => 'completed']); 

                // تحميل العلاقات للرد
                $invoice->load(['pieces', 'fixRequest.car']);

                return response()->json([
                    'invoice' => $invoice,
                    'breakdown' => [
                        'parts_total' => $totalPartsCost,
                        'labor_cost' => $laborCost,
                        'grand_total' => $finalTotal
                    ]
                ], 201);
            });
      
        } catch (\Exception $e) {
            return response()->json('حدث خطأ أثناء إنشاء الفاتورة: ' . $e->getMessage(), 500);
        }
}


}