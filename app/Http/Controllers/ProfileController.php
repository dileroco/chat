<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function fetch(Request $request)
    {
        $userId = $request->session()->get('user_id');

        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not logged in',
                'data' => ['loggedin' => false],
            ], 401);
        }

        $user = DB::table('users')
            ->select(['id', 'full_name', 'email', 'username', 'profile_picture', 'created_at'])
            ->where('id', $userId)
            ->first();

        if (!$user) {
            $request->session()->invalidate();

            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        if ($request->isMethod('post')) {
            $request->merge([
                'full_name' => trim((string) $request->input('full_name')),
            ]);

            $data = $request->validate([
                'full_name' => ['required', 'string', 'max:255'],
                'password' => ['nullable', 'string', 'min:8'],
                'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
            ]);

            $profilePicture = $user->profile_picture;

            if ($request->hasFile('profile_picture')) {
                $directory = public_path('react/images/profile-picture');
                File::ensureDirectoryExists($directory);

                $file = $request->file('profile_picture');
                $newFileName = Str::uuid().'.'.$file->extension();
                $file->move($directory, $newFileName);

                if ($profilePicture && $profilePicture !== 'default.png') {
                    File::delete($directory.DIRECTORY_SEPARATOR.$profilePicture);
                }

                $profilePicture = $newFileName;
            }

            $updateData = [
                'full_name' => trim($data['full_name']),
                'profile_picture' => $profilePicture,
            ];

            if (!empty($data['password'] ?? null)) {
                $updateData['password'] = Hash::make($data['password']);
            }

            DB::table('users')->where('id', $userId)->update($updateData);

            $request->session()->put([
                'full_name' => $updateData['full_name'],
                'profile_picture' => $profilePicture,
            ]);

            $user = DB::table('users')
                ->select(['id', 'full_name', 'email', 'username', 'profile_picture', 'created_at'])
                ->where('id', $userId)
                ->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => $user,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User data fetched successfully',
            'data' => $user,
        ]);
    }
}
