<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Payment;

class PaymentController extends Controller
{
    //
    public function processPayment(Request $request) {
        // Payment processing logic will go here
        $validator = Validator::make($request->all(), [
            'order_ref' => 'required|string',
            'total' => 'required|numeric',
            'payment_method' => 'required|string',
            'address_id' => 'required|exists:addresses,id',
        ]);

        if($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Invalid payment data',
            ], 422);
        }

        try {
            $customer_id = auth()->user()->id;
            // Here you would typically integrate with a payment gateway
            // For demonstration, we'll assume the payment is always successful

            $payment = new Payment;
            $payment->order_ref = $request->input('order_ref');
            $payment->total = $request->input('total');
            $payment->payment_status = 'completed';
            $payment->payment_ref = uniqid('pay_');
            $payment->customer_id = $customer_id;
            $payment->payment_method = $request->input('payment_method');
            $payment->address_id = $request->input('address_id');
            $payment->save();

            return response()->json([
                'payment' => $payment,
                'message' => 'Payment processed successfully',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
