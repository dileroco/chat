<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SendMSGController extends Controller
{
    public function sendMessage(Request $request)
    {
        // CORS
 
        if ($request->isMethod('options')) {
            return response()->json([], 200);
        }

        $response = [
            'status' => '',
            'message' => '',
            'data' => []
        ];

        // تحقق من session
        if (!session('loggedin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not logged in'
            ]);
        }

        try {

            $content = trim($request->input('content'));
            $receiver_id = $request->input('receiver_id');

            if (!$content || !$receiver_id) {
                throw new \Exception('Content and receiver_id are required');
            }

            $sender_id = session('user_id');

            $id = DB::table('messages')->insertGetId([
                'sender_id' => $sender_id,
                'receiver_id' => $receiver_id,
                'content' => $content,
                'created_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => [
                    'id' => $id,
                    'content' => $content,
                    'created_at' => now()
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);

        }
    }
}