<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WalletTransactionHistroy;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{

    public function recharge(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
            'status' => 'failure',
            'message' => 'Validation error.',
            'errors' => $validator->errors()
            ], 422);
        }else{

        $user = $request->user();
        $amount = $request->amount;

        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

        try {
            $order = $api->order->create([
                'receipt' => uniqid(),
                'amount' => $amount * 100,
                'currency' => 'INR'
            ]);

            return response()->json([
                'status' => 'success',
                'order_id' => $order->id,
                'amount' => $amount,
                'razorpay_key' => env('RAZORPAY_KEY'),
                'message' => 'Order created. Proceed with payment.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Order creation failed.',
                'error' => $e->getMessage()
            ], 500);
        }
      }
    }

    public function verify(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
        ]);


        $validator = \Validator::make($request->all(), [
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
            'status' => 'failure',
            'message' => 'Validation error.',
            'errors' => $validator->errors()
            ], 422);
        }else{

            $user = $request->user();
            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

            try {
                $attributes = [
                    'razorpay_order_id' => $request->razorpay_order_id,
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature' => $request->razorpay_signature
                ];

                $api->utility->verifyPaymentSignature($attributes);

                $payment = $api->payment->fetch($request->razorpay_payment_id);

                DB::transaction(function () use ($payment, $user) {

                    $user->wallet_balance += $payment->amount / 100;
                    $user->save();

                    WalletTransactionHistroy::create([
                        'user_id' => $user->id,
                        'type' => 'credit',
                        'payment_id' => $payment->id,
                        'amount' => $payment->amount / 100,
                        'status' => $payment->status,
                        'response' => json_encode($payment->toArray()),
                        'user_balance' => $user->wallet_balance,
                    ]);


                });

                return response()->json([
                    'status' => 'success',
                    'wallet_balance' => $user->wallet_balance,
                    'transaction_id' => $payment->id,
                    'message' => 'Wallet recharged successfully.'
                ]);
            } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Signature verification failed.',
                    'error' => $e->getMessage()
                ], 400);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Verification failed.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }

    public function walletRecharge(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
            'status' => 'failure',
            'message' => 'Validation error.',
            'errors' => $validator->errors()
            ], 422);
        }else{

            $user = $request->user();
            $amount = $request->amount;

            $user->wallet_balance += $amount;
            $user->save();
            $payment_id=uniqid('txn_');
            WalletTransactionHistroy::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'payment_id' => $payment_id,
                'amount' => $amount,
                'status' => 'success',
                'response' => json_encode(['payment_id' => $payment_id,'amount' => $amount,'status' => 'success']),
                'user_balance' => $user->wallet_balance,
            ]);


        return response()->json([
            'status' => 'success',
            'wallet_balance' => $user->wallet_balance,
            'transaction_id' => $payment_id,
            'message' => 'Wallet recharged successfully.'
        ]);



        }


    }

}
