<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
        ]);

        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !$this->passwordMatches($user, $credentials['password'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect username or password',
            ], 401);
        }

        if (Hash::needsRehash($user->password)) {
            $user->forceFill(['password' => Hash::make($credentials['password'])])->save();
        }

        $this->startUserSession($request, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => $this->userData($user),
        ]);
    }

    public function signup(Request $request)
    {
        $request->merge([
            'full_name' => trim((string) $request->input('full_name')),
            'email' => strtolower(trim((string) $request->input('email'))),
            'username' => strtolower(trim((string) $request->input('username'))),
        ]);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
        ]);

        $this->startUserSession($request, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Signup successful',
            'data' => $this->userData($user),
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 'success',
            'message' => 'User successfully logged out',
        ]);
    }

    private function passwordMatches(User $user, string $password): bool
    {
        return Hash::check($password, (string) $user->password);
    }

    private function startUserSession(Request $request, User $user): void
    {
        $request->session()->regenerate();
        $request->session()->put([
            'loggedin' => true,
            'user_id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'username' => $user->username,
            'profile_picture' => $user->profile_picture,
        ]);

    }

    private function userData(User $user): array
    {
        return [
            'loggedin' => true,
            'user_id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'username' => $user->username,
            'profile_picture' => $user->profile_picture,
            'created_at' => $user->created_at,
        ];
    }
}
