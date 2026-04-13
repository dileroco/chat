<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function fetchMessages(Request $request)
    {
        // دعم CORS

        if ($request->isMethod('options')) {
            return response()->json([], 200);
        }

        // التحقق من session
        $sender_id = session('user_id');
        if (!$sender_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not logged in',
                'data' => ['loggedin' => false]
            ]);
        }

        $receiver_id = $request->input('id');
        if (!$receiver_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Receiver ID is required'
            ]);
        }

        // تحديث الرسائل الغير مقروءة
        DB::table('messages')
            ->where('sender_id', $receiver_id)
            ->where('receiver_id', $sender_id)
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        // استرجاع الرسائل بين المستخدمين
        $messages = DB::table('messages')
            ->where(function($q) use ($sender_id, $receiver_id) {
                $q->where('sender_id', $sender_id)
                  ->where('receiver_id', $receiver_id);
            })
            ->orWhere(function($q) use ($sender_id, $receiver_id) {
                $q->where('sender_id', $receiver_id)
                  ->where('receiver_id', $sender_id);
            })
            ->orderBy('created_at')
            ->get();

        if ($messages->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No messages found'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Messages fetched successfully',
            'data' => $messages
        ]);
    }
}