<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // دعم CORS مثل الكود القديم

        if ($request->isMethod('options')) {
            return response()->json([], 200);
        }

        // تحقق من المدخلات
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $username = strtolower(trim($request->username));
        $password = trim($request->password);

        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No user found with that username'
            ], 404);
        }

        // تحقق كلمة المرور
        
        if ($user->password === hash_hmac('sha256', $password, 'iqbolshoh')) {
            // تحويل HMAC إلى bcrypt عند أول login
            $user->password_bcrypt = Hash::make($password);
            $user->password = Hash::make($password);
            $user->save();
        } 
        else if($user->password && Hash::check($password, $user->password)) {
            // login ناجح
        } 
        
        else {
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect username or password'
            ], 401);
        }

        // إنشاء Session
        $request->session()->regenerate();
        session(['loggedin' => true]);
        session(['user_id' => $user->id]);
        session(['full_name' => $user->full_name]);
        session(['email' => $user->email]);
        session(['username' => $user->username]);
        session(['profile_picture' => $user->profile_picture]);

        // إنشاء Cookies (مثل الكود القديم)
        cookie()->queue('username', $username, 60*24*30); // 30 يوم
        cookie()->queue('session_token', session()->getId(), 60*24*30);
       
        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'loggedin' => true,
                'user_id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'username' => $user->username,
                'profile_picture' => $user->profile_picture,
                'created_at' => $user->created_at
            ]
        ], 200);
    }

    public function signup(Request $request){
        if ($request->isMethod("options")){
            return(response()->json([],200));
        }
        $request->validate([
            "full_name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "username" => "required|string|max:50|unique:users,username",
            "password" => "required|string|min:8|confirmed"
        ]);

        User::create([
            "full_name" => $request->full_name,
            "email" => $request->email,
            "username" => $request->username,
            "password" => Hash::make($request->password)
        

        ]);
        $user = User::where('username', $request->username)->first();
        cookie()->queue('username', $user->username, 60*24*30); // 30 يوم
        cookie()->queue('session_token', session()->getId(), 60*24*30);
       
        $request->session()->regenerate();
        session(["loggedin" => true]);
        session(['user_id' => $user->id]);
        session(['full_name' => $user->full_name]);
        session(['email' => $user->email]);
        session(['username' => $user->username]);
        session(['profile_picture' => $user->profile_picture]);

        return response()->json([
            "status" =>  "success",
            "message" => "singup successfully "
        ]);
 

    }


    public function logout(Request $request){

        // destroy session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // delete cookies
        Cookie::queue(Cookie::forget('username'));
        Cookie::queue(Cookie::forget('session_token'));

        return response()->json([
            'status' => 'success',
            'message' => 'User successfully logged out'
        ]);
    }
    
}
