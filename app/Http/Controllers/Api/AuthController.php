<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{


    public function registerUser(Request $request){


                try {
                    $validatedData = $request->validate([
                        'email' => 'required|email|unique:users,email',
                        'password' => 'required|string|min:6',
                    ]);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'status' => false,
                        'errors' => $e->errors(),
                    ], 422);
                }

                $new_user = new User;
                $new_user->name = 'User_' . uniqid();
                $new_user->email = $request->email;
                $new_user->password = bcrypt($request->password);
                $new_user->save();

                return response()->json([
                    'status' => true,
                    'token' => $new_user->createToken('api-token')->plainTextToken,
                    'message'=>'User Register Successfully!',
                ],200);

    }


}
