<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Log;

class MailHelper
{
    /**
     * Send an email asynchronously (true fire-and-forget).
     * Uses exec() to spawn a detached background curl process.
     * PHP returns immediately — no waiting at all.
     */
    public static function sendAsync(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $textBody = ''
    ): void {
        try {
            $appUrl  = rtrim((string) env('APP_URL', 'http://localhost:8000'), '/');
            $url     = $appUrl . '/api/v1/mail/send-async';

            // Escape all values for shell
            $payload = json_encode([
                'to_email'  => $toEmail,
                'to_name'   => $toName,
                'subject'   => $subject,
                'html_body' => $htmlBody,
                'text_body' => $textBody ?: strip_tags($htmlBody),
            ]);

            // Spawn curl as a fully detached background process
            // > /dev/null 2>&1 & means: discard output, discard errors, run in background
            $cmd = 'curl -s -X POST '
                . '-H "Content-Type: application/json" '
                . '-d ' . escapeshellarg($payload) . ' '
                . escapeshellarg($url)
                . ' > /dev/null 2>&1 &';

            exec($cmd);
        } catch (\Throwable $e) {
            Log::error('MailHelper::sendAsync failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send an email synchronously.
     */
    public static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $textBody = ''
    ): void {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host       = env('MAIL_HOST', 'smtp.gmail.com');
        $mailer->SMTPAuth   = true;
        $mailer->Username   = env('MAIL_USERNAME');
        $mailer->Password   = env('MAIL_PASSWORD');
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port       = (int) env('MAIL_PORT', 587);
        $mailer->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Helping Hands'));
        $mailer->addAddress($toEmail, $toName);
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body    = $htmlBody;
        $mailer->AltBody = $textBody ?: strip_tags($htmlBody);
        $mailer->send();
    }

    /**
     * Wrap content in the standard Helping Hands email template.
     */
    public static function template(string $bodyContent): string
    {
        return "
        <div style='font-family:sans-serif;max-width:520px;margin:0 auto;padding:0;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;'>
            <div style='background:linear-gradient(135deg,#7c3aed,#a855f7);padding:24px 28px;'>
                <h2 style='color:#fff;margin:0;font-size:20px;'>Helping Hands</h2>
                <p style='color:rgba(255,255,255,0.8);margin:4px 0 0;font-size:13px;'>Child Development &amp; Education Center</p>
            </div>
            <div style='padding:28px;background:#fff;'>
                {$bodyContent}
            </div>
            <div style='background:#f9fafb;padding:16px 28px;border-top:1px solid #e5e7eb;'>
                <p style='color:#9ca3af;font-size:11px;margin:0;'>This is an automated notification from Helping Hands. Please do not reply.</p>
            </div>
        </div>";
    }
}
