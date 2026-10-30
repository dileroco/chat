<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SendMSGController extends Controller
{
    public function sendMessage(Request $request)
    {
        $senderId = $request->session()->get('user_id');

        if (!$senderId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not logged in',
            ], 401);
        }

        $data = $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        if ((int) $data['receiver_id'] === (int) $senderId) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot send a message to yourself',
            ], 422);
        }

        $content = trim($data['content']);

        if ($content === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Message content is required',
            ], 422);
        }

        $id = DB::table('messages')->insertGetId([
            'sender_id' => $senderId,
            'receiver_id' => $data['receiver_id'],
            'content' => $content,
            'status' => 'unread',
            'created_at' => now(),
        ]);

        $message = DB::table('messages')->where('id', $id)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully',
            'data' => $message,
        ], 201);
    }
}
