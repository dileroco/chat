<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class fetchUsersController extends Controller
{
    public function fetchUsers(Request $request)
    {
        $senderId = $request->session()->get('user_id');

        if (!$senderId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not logged in',
                'data' => ['loggedin' => false],
            ], 401);
        }

        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim($data['search'] ?? '');
        $searchLike = "%{$search}%";

        $users = DB::select('
            SELECT
                u.id AS user_id,
                u.full_name,
                u.username,
                IFNULL(u.profile_picture, "default.png") AS profile_picture,
                MAX(m.created_at) AS last_message_time
            FROM users u
            LEFT JOIN messages m
                ON ((m.receiver_id = u.id AND m.sender_id = ?)
                    OR (m.receiver_id = ? AND m.sender_id = u.id))
            WHERE u.id != ?
                AND (u.full_name LIKE ? OR u.username LIKE ?)
            GROUP BY u.id, u.full_name, u.username, u.profile_picture
            ORDER BY last_message_time DESC, u.id ASC
        ', [$senderId, $senderId, $senderId, $searchLike, $searchLike]);

        $users = collect($users)->map(function ($user) use ($senderId) {
            $user->unread_count = DB::table('messages')
                ->where('receiver_id', $senderId)
                ->where('sender_id', $user->user_id)
                ->where('status', 'unread')
                ->count();

            return $user;
        })->values();

        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully',
            'data' => $users,
        ]);
    }
}
