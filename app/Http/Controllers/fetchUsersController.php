<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class fetchUsersController extends Controller
{
    public function fetchUsers(Request $request)
    {
        // Preflight OPTIONS
        if ($request->isMethod('options')) {
            return response()->json([], 200);
        }

        // جلب user_id من session
        $sender_id = session('user_id');
        if (!$sender_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not logged in',
                'data' => ['loggedin' => false]
            ], 401);
        }

        $search = $request->query('search', '');
        $searchLike = "%{$search}%";

        // جلب المستخدمين مع آخر رسالة لكل واحد
        $users = DB::select("
            SELECT 
                u.id AS user_id, 
                u.full_name, 
                u.username, 
                IFNULL(u.profile_picture, 'default.png') AS profile_picture,
                MAX(m.created_at) AS last_message_time
            FROM users u
            LEFT JOIN messages m 
                ON ( (m.receiver_id = u.id AND m.sender_id = ?) 
                  OR (m.receiver_id = ? AND m.sender_id = u.id) )
            WHERE u.id != ? 
              AND (u.full_name LIKE ? OR u.username LIKE ?)
            GROUP BY u.id, u.full_name, u.username, u.profile_picture
            ORDER BY last_message_time DESC, u.id ASC
        ", [$sender_id, $sender_id, $sender_id, $searchLike, $searchLike]);

        // إضافة unread_messages لكل مستخدم
        $result = collect($users)->map(function ($user) use ($sender_id) {
            $unread = DB::table('messages')
                ->where('receiver_id', $sender_id)
                ->where('sender_id', $user->user_id)
                ->where('status', 'unread')
                ->count();
            $user->unread_messages = $unread;
            return $user;
        });

        if ($result->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No contacts found',
                'data' => []
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Contacts retrieved successfully',
            'data' => $result
        ]);
    }
}