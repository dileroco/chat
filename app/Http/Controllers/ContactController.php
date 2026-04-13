<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function contacts(Request $request)
    {
        // دعم CORS مثل المشروع القديم

        if ($request->isMethod('options')) {
            return response()->json([], 200);
        }

        // استرجاع user_id من session فقط
        $sender_id = session('user_id');

        if (!$sender_id) {
            return response()->json([], 200);
        }

        // بحث
        $search = $request->query('search', '');
        $searchParam = "%$search%";

        // استعلام مثل الكود القديم
        $contacts = DB::select("
            SELECT 
                u.id,
                u.full_name,
                u.username,
                IFNULL(u.profile_picture, 'default.png') AS profile_picture,
                (
                    SELECT COUNT(*) 
                    FROM messages m
                    WHERE m.sender_id = u.id
                    AND m.receiver_id = ?
                    AND m.status = 'unread'
                ) AS unread_count
            FROM users u
            WHERE u.id IN (
                SELECT m.sender_id FROM messages m WHERE m.receiver_id = ?
                UNION
                SELECT m.receiver_id FROM messages m WHERE m.sender_id = ?
            )
            AND (u.full_name LIKE ? OR u.username LIKE ?)
            ORDER BY (
                SELECT MAX(created_at)
                FROM messages
                WHERE (sender_id = u.id AND receiver_id = ?)
                   OR (receiver_id = u.id AND sender_id = ?)
            ) DESC
        ", [
            $sender_id,
            $sender_id,
            $sender_id,
            $searchParam,
            $searchParam,
            $sender_id,
            $sender_id
        ]);

        return response()->json([
            'status' => 'success',
            
            'data' => $contacts
            
        ]);
    }
}
