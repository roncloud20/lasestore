<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // create order 
    public function createOrder (Request $request) {
        $validator = Validator::make($request->all(),[
            'product_id' => 'required|exists:products,product_id|interger',
            'quantity' => 'required|integer',
            'unit_price' => 'required|decimal',
            'order_ref' => 'required|string',
            'order_status' => 'required|in:pending,shipped,out_for_delivery,delivered,rejected,returned'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Unable to complete order',
            ],422);
        }

        try {

        } catch(\Exception $errors) {
            return response()->json([
                'message' => $errors->getMessage(), 
            ],500);
        }
    }
}
