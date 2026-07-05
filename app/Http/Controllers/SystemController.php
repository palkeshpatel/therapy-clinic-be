<?php

namespace App\Http\Controllers;

use App\Helpers\MailHelper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Internal endpoint to send an email.
     * Called asynchronously from MailHelper::sendAsync via cURL (fire-and-forget).
     * No authentication required — only reachable from localhost.
     */
    public function sendMailAsync(Request $request)
    {
        // Security: only allow local loopback calls
        $ip = $request->server('REMOTE_ADDR') ?? '127.0.0.1';
        if (!in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        try {
            MailHelper::send(
                (string) $request->input('to_email', ''),
                (string) $request->input('to_name', ''),
                (string) $request->input('subject', ''),
                (string) $request->input('html_body', ''),
                (string) $request->input('text_body', '')
            );
            return response()->json(['status' => 'sent']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Async mail (internal) failed', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['status' => 'failed', 'error' => $e->getMessage()], 500);
        }
    }
}
