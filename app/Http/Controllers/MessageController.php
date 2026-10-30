<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function fetchMessages(Request $request)
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
            'id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $receiverId = (int) $data['id'];

        DB::table('messages')
            ->where('sender_id', $receiverId)
            ->where('receiver_id', $senderId)
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        $messages = DB::table('messages')
            ->where(function ($query) use ($senderId, $receiverId) {
                $query->where('sender_id', $senderId)
                    ->where('receiver_id', $receiverId);
            })
            ->orWhere(function ($query) use ($senderId, $receiverId) {
                $query->where('sender_id', $receiverId)
                    ->where('receiver_id', $senderId);
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Messages fetched successfully',
            'data' => $messages,
        ]);
    }
}
