<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // create order 
    public function createOrder (Request $request) {
        $validator = Validator::make($request->all(),[
            'product_id' => 'required|exists:products,product_id',
            'quantity' => 'required',
            'unit_price' => 'required|numeric',
            'order_ref' => 'required|string',
            'address_id' => 'required|exists:addresses,id',
            'cost_price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Unable to complete order',
            ],422);
        }

        try {
            $customer_id = auth()->user()->id;
            $order = new Order;
            $order->product_id = $request->input('product_id');
            $order->quantity = $request->input('quantity');
            $order->unit_price = $request->input('unit_price');
            $order->cost_price = $request->input('cost_price');
            $order->customer_id = $customer_id;
            $order->order_ref = $request->input('order_ref');
            $order->address_id = $request->input('address_id');
            $order->save();

            return response()->json([
                'order' => $order,
                'message' => 'Order created successfully',
            ],201);

        } catch(\Exception $errors) {
            return response()->json([
                'message' => $errors->getMessage(), 
            ],500);
        }
    }
}
