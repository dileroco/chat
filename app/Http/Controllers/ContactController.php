<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function contacts(Request $request)
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

        $searchLike = '%'.trim($data['search'] ?? '').'%';

        $contacts = DB::select('
            SELECT
                u.id AS user_id,
                u.full_name,
                u.username,
                IFNULL(u.profile_picture, "default.png") AS profile_picture,
                (
                    SELECT COUNT(*)
                    FROM messages unread_messages
                    WHERE unread_messages.sender_id = u.id
                        AND unread_messages.receiver_id = ?
                        AND unread_messages.status = "unread"
                ) AS unread_count
            FROM users u
            WHERE u.id IN (
                SELECT messages.sender_id
                FROM messages
                WHERE messages.receiver_id = ?
                UNION
                SELECT messages.receiver_id
                FROM messages
                WHERE messages.sender_id = ?
            )
            AND (u.full_name LIKE ? OR u.username LIKE ?)
            ORDER BY (
                SELECT MAX(messages.created_at)
                FROM messages
                WHERE (messages.sender_id = u.id AND messages.receiver_id = ?)
                    OR (messages.receiver_id = u.id AND messages.sender_id = ?)
            ) DESC
        ', [
            $senderId,
            $senderId,
            $senderId,
            $searchLike,
            $searchLike,
            $senderId,
            $senderId,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Contacts retrieved successfully',
            'data' => $contacts,
        ]);
    }
}
