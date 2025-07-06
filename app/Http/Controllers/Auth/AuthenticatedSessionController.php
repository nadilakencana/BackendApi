<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        
        $user = Auth::user();
       
        $token = $user->createToken('api-token')->plainTextToken;

      
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);

       
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        // Auth::guard('web')->logout();

        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
