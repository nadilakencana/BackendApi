<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\HistoryChat;
use App\Models\SessionChats;

class HistoryChatController extends Controller
{
     public function sendMessage(Request $request)
    {
        $request->validate([
            'id_session' => 'nullable|string|max:100', 
            'message' => 'required|string',
        ]);

        $userMessage = $request->input('message');
        $sessionId = $request->input('id_session');

        $session = null;
        if ($sessionId) {
            $session = SessionChats::find($sessionId);
        }

        if (!$session) {
            $sessionId = (string) Str::uuid(); 
            $session = SessionChats::create([
                'id' => $sessionId,
                'started_at' => now(),
                'title' => 'New Chat Session ' . now()->format('Y-m-d H:i') 
            ]);
        }

        
        HistoryChat::create([
            'id_session' => $sessionId,
            'sender_type' => 'user',
            'message' => $userMessage,
        ]);

      
        $apiKey = env('GEMINI_API_KEY');
        $model = 'gemini-pro'; 
        $client = new Client();

        $history = $session->messages()->orderBy('created_at', 'asc')->get();
        $contents = [];
        foreach ($history as $msg) {
            $contents[] = ['parts' => [['text' => $msg->message]]];
        }

        $contents[] = ['parts' => [['text' => $userMessage]]]; 

        try {
            $response = $client->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'json' => [
                    'contents' => $contents 
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            $aiResponseText = 'Maaf, saya tidak dapat memahami permintaan Anda.'; // Default
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $aiResponseText = $data['candidates'][0]['content']['parts'][0]['text'];
            } else if (isset($data['promptFeedback']['blockReason'])) {
                 // Tangani jika ada blocking reason dari Gemini
                $aiResponseText = "Maaf, permintaan ini diblokir karena: " . $data['promptFeedback']['blockReason'];
                Log::warning('Gemini API blocked message', ['reason' => $data['promptFeedback']['blockReason'], 'user_message' => $userMessage]);
            } else {
                Log::error('Gemini API did not return expected response structure.', ['response' => $data]);
                $aiResponseText = 'Terjadi kesalahan saat memproses respons AI.';
            }

           
            HistoryChat::create([
                'id_session' => $sessionId,
                'sender_type' => 'ai',
                'message' => $aiResponseText,
            ]);

            return response()->json([
                'id_session' => $sessionId,
                'reply' => $aiResponseText
            ]);

        } catch (\Exception $e) {
            Log::error('Error communicating with Gemini API: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to communicate with AI: ' . $e->getMessage(),
                'id_session' => $sessionId 
            ], 500);
        }
    }

    public function getSessions(Request $request)
    {
       
        $sessions = SessionChats::orderBy('updated_at', 'desc')->get(['id', 'title', 'started_at']);
        return response()->json($sessions);
    }

    public function getHistory(Request $request, $sessionId)
    {
        $history = HistoryChat::where('id_session', $sessionId)
                              ->orderBy('created_at', 'asc')
                              ->get(['sender_type', 'message', 'created_at']);
        return response()->json($history);
    }
}
