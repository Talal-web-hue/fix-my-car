<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\FixRequest;
use Illuminate\Http\Request;
use App\Models\Piece;
class InvoiceController extends Controller
{


public function store(Request $request)
{
    $request->validate([
        'fix_request_id' => 'required|exists:fix_requests,id',
        'pieces' => 'required|array|min:1',
        'pieces.*.piece_id' => 'required|exists:pieces,id',
        'pieces.*.quantity' => 'required|integer|min:1',
        'service_fee' => 'nullable|numeric|min:0'
    ]);

    // التحقق: هل هذا الطلب يخص العميل أو يمكن للموظف الوصول إليه؟
    // (هنا نفترض أن الموظف فقط من يُنشئ الفاتورة)
    // يمكنك إضافة منطق حسب دور المستخدم لاحقاً

    // $serviceFee = $request->service_fee ?? 0;
    $piecesTotal = 0;
    $pivotData = [];

    foreach ($request->pieces as $item) {
        $piece = Piece::find($item['piece_id']);
        $price = $piece->price;
        $qty = $item['quantity'];
        $piecesTotal += $price * $qty;

        $pivotData[$piece->id] = [
            'price_at_time_of_sale' => $price,
            'quantity' => $qty
        ];
    }

    $totalAmount = $piecesTotal + $serviceFee;

    $invoice = Invoice::create([
        'fix_request_id' => $request->fix_request_id,
        // 'employee_id' => auth()->id(), // أو أي منطق لتحديد الموظف
        'total_amount' => $totalAmount,
        'status' => 'قيد الانتظار'
    ]);

    $invoice->pieces()->attach($pivotData);

    return response()->json([
        'message' => 'تم إنشاء الفاتورة بنجاح.',
        'invoice' => $invoice->load('pieces')
    ], 201);
}
}