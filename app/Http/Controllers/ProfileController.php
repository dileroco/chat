<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{

    public function fetch(Request $request)
    {

        if (!Session::get('loggedin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not logged in',
                'data' => ['loggedin' => false]
            ]);
        }

        $userId = Session::get('user_id');

        $user = DB::table('users')->where('id', $userId)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ]);
        }

        if ($request->isMethod('post')) {

            $full_name = trim($request->input('full_name'));
            $password = $request->input('password');
            $profile_picture = $user->profile_picture;

            if ($request->hasFile('profile_picture')) {

                $file = $request->file('profile_picture');

                $allowed = ['jpg','jpeg','png','gif'];

                if (!in_array($file->extension(), $allowed)) {
                    return response()->json([
                        'status'=>'error',
                        'message'=>'Invalid file type'
                    ]);
                }

                $newFileName = md5(time().$file->getClientOriginalName()).'.'.$file->extension();

                $file->move(public_path('react/images/profile-picture/'), $newFileName);

                if ($profile_picture && $profile_picture !== 'default.png') {
                    $old = public_path('react/images/profile-picture/'.$profile_picture);
                    if (file_exists($old)) {
                        unlink($old);
                    }
                }

                $profile_picture = $newFileName;
            }

            $updateData = [
                'full_name'=>$full_name,
                'profile_picture'=>$profile_picture
            ];

            if (!empty($password)) {
                $updateData['password'] = Hash::make($password);
            }

            DB::table('users')->where('id',$userId)->update($updateData);

            Session::put('full_name',$full_name);
            Session::put('profile_picture',$profile_picture);

            return response()->json([
                'status'=>'success',
                'message'=>'Profile updated successfully',
                'data'=>$updateData
            ]);
        }

        return response()->json([
            'status'=>'success',
            'message'=>'User data fetched successfully',
            'data'=>$user
        ]);
    }

}